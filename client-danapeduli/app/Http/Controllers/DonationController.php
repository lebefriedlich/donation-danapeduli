<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Donation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;

class DonationController extends Controller
{

    public function createDonation(Request $request, $id)
    {
        $request->merge(['campaign_id' => $id]);
        $data = $request->validate([
            'campaign_id' => ['required', 'integer', 'exists:campaigns,id'],
            'amount' => ['required', 'integer', 'min:1000'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'is_anonymous' => ['boolean'],
            'message' => ['nullable', 'string', 'max:255'],
        ]);

        // Membuat transaksi untuk donasi
        $donation = DB::transaction(function () use ($data) {
            return Donation::create([
                'campaign_id' => $data['campaign_id'],
                'order_id' => (string) Str::uuid(),
                'amount' => (int) $data['amount'],
                'donor_name' => $data['name'] ?? 'Anonymous',
                'donor_email' => $data['email'],
                'is_anonymous' => (bool) $data['is_anonymous'],
                'message' => $data['message'] ?? null,
                'payment_status' => 'PENDING',
                'snap_token' => null,
            ]);
        });

        // Integrasi dengan Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $donation->order_id,
                'gross_amount' => $donation->amount,
            ],
            'customer_details' => [
                'first_name' => $donation->donor_name,
                'email' => $donation->donor_email,
            ],
        ];

        // Mendapatkan Snap Token
        $snapToken = \Midtrans\Snap::getSnapToken($params);
        $donation->snap_token = $snapToken;
        $donation->save();

        // Mengarahkan ke halaman pembayaran
        return response()->json([
            'snap_token' => $snapToken
        ]);
    }

    public function paymentStatus(Request $request)
    {
        // 1. Validasi signature Midtrans
        $serverKey = config('midtrans.server_key');
        $hashed = hash(
            'sha512',
            $request->order_id .
                $request->status_code .
                $request->gross_amount .
                $serverKey
        );

        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        DB::transaction(function () use ($request) {

            // 2. Lock donation (anti double webhook)
            $donation = Donation::where('order_id', $request->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldStatus = $donation->payment_status;

            // 3. Mapping Midtrans → ENUM DB
            $newStatus = match ($request->transaction_status) {
                'capture', 'settlement'   => 'PAID',
                'pending'                 => 'PENDING',
                'expire'                  => 'EXPIRED',
                'refund', 'partial_refund' => 'REFUNDED',
                default                   => 'FAILED',
            };

            // 4. Kalau status sama → STOP (idempotent)
            if ($oldStatus === $newStatus) {
                return;
            }

            // 5. Update donation
            $donation->payment_status = $newStatus;

            if ($newStatus === 'PAID') {
                $donation->paid_at = Carbon::now('Asia/Jakarta');
            }

            $donation->save();

            // 6. Jika BARU berubah ke PAID → update campaign
            if ($oldStatus !== 'PAID' && $newStatus === 'PAID') {

                // 🔼 Tambah total_paid
                Campaign::where('id', $donation->campaign_id)
                    ->increment('total_paid', $donation->amount);

                // 🔒 Auto close jika target tercapai
                Campaign::where('id', $donation->campaign_id)
                    ->where('auto_close_on_target', true)
                    ->whereColumn('total_paid', '>=', 'target_amount')
                    ->where('status', 'ACTIVE')
                    ->update([
                        'status'    => 'CLOSED',
                        'closed_at' => now(),
                    ]);
            }
        });

        return response()->json(['message' => 'Payment processed'], 200);
    }
}
