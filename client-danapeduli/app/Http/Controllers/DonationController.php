<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Donation;
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

        $serverKey = config('midtrans.server_key');
        $hashed = hash('sha512', $request->input('order_id') . $request->input('status_code') . $request->input('gross_amount') . $serverKey);
        if ($hashed !== $request->input('signature_key')) {
            // Cari transaksi berdasarkan order_id
            $donation = Donation::where('order_id', $request->input('order_id'))->firstOrFail();
            $status = $request->input('transaction_status');

            // Update status pembayaran berdasarkan status yang diterima dari Midtrans
            switch ($status) {
                case 'capture':
                case 'settlement':
                    $donation->payment_status = 'SUCCESS';
                    break;

                case 'pending':
                    $donation->payment_status = 'PENDING';
                    break;

                case 'deny':
                case 'failure':
                    $donation->payment_status = 'FAILED';
                    break;

                case 'cancel':
                    $donation->payment_status = 'CANCELED';
                    break;

                case 'refund':
                    $donation->payment_status = 'REFUNDED';
                    break;

                case 'partial_refund':
                    $donation->payment_status = 'PARTIAL_REFUND';
                    break;

                default:
                    $donation->payment_status = 'UNKNOWN';
                    break;
            }

            $donation->save();
        }
    }
}
