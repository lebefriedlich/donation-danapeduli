<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Midtrans\Config;

class PublicCampaignController extends Controller
{
    public function index(Request $request)
    {
        // Ambil tipe yang dipilih dari request, default 'ALL'
        $type = $request->string('type')->toString() ?: 'ALL';

        // Validasi tipe yang diterima, jika tidak valid, set default ke 'ALL'
        $type = in_array($type, ['DONATION', 'CROWDFUND', 'ALL'], true) ? $type : 'ALL';

        // Ambil semua campaign dengan status 'ACTIVE' atau 'CLOSED'
        $campaigns = Campaign::public()
            ->whereIn('status', ['ACTIVE', 'CLOSED'])
            ->when($type !== 'ALL', function ($query) use ($type) {
                return $query->where('type', $type); // Filter berdasarkan tipe jika tipe bukan 'ALL'
            })
            ->latest()
            ->get()
            ->map(fn($c) => $this->presentCampaign($c));

        return Inertia::render('Campaigns/Index', [
            'type' => $type,
            'campaigns' => $campaigns,
        ]);
    }

    public function show(string $slug)
    {
        $campaign = Campaign::with('donations')
            ->public()
            ->where('slug', $slug)
            ->firstOrFail();

        $updates = $campaign->updates()
            ->published()
            ->latest()
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'title' => $u->title,
                'content' => $u->content,
                'attachment' => $u->attachment ? asset('storage/' . $u->attachment) : null,
                'published_at' => optional($u->published_at)->toISOString(),
                'is_financial_update' => (bool) ($u->is_financial_update ?? false),
                'disbursed_amount' => $u->disbursed_amount,
            ]);

        return Inertia::render('Campaigns/Show', [
            'campaign' => $this->presentCampaign($campaign),
            'updates' => $updates ?? [], // Pastikan updates selalu array (kosong jika tidak ada)
        ]);
    }

    public function showDonateForm($slug)
    {
        // Cari campaign berdasarkan slug dan pastikan statusnya 'ACTIVE' atau 'CLOSED'
        $campaign = Campaign::public()
            ->where('slug', $slug)
            ->whereIn('status', ['ACTIVE', 'CLOSED'])
            ->firstOrFail();

        return Inertia::render('Campaigns/DonateForm', [
            'campaign' => $this->presentCampaign($campaign),
        ]);
    }

    private function presentCampaign($c): array
    {
        return [
            'id' => $c->id,
            'slug' => $c->slug,
            'title' => $c->title,
            'description' => $c->description,
            'type' => $c->type,
            'goal_type' => $c->goal_type,
            'target_amount' => (int) $c->target_amount,
            'total_paid' => (int) $c->total_paid,
            'status' => $c->status,
            'cover_image' => $c->cover_image ? asset('storage/' . $c->cover_image) : null,
        ];
    }
}
