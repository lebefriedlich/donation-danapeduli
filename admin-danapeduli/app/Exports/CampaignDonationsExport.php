<?php

namespace App\Exports;

use App\Models\Donation;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CampaignDonationsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected int $campaignId;

    public function __construct(int $campaignId)
    {
        $this->campaignId = $campaignId;
    }

    public function query(): Builder
    {
        return Donation::query()
            ->where('campaign_id', $this->campaignId)
            ->orderBy('created_at');
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Nama Donatur',
            'Anonim',
            'Nominal',
            'Status',
            'Paid At',
            'Created At',
        ];
    }

    public function map($donation): array
    {
        return [
            $donation->order_id,
            $donation->is_anonymous ? 'Anonim' : ($donation->donor_name ?? '-'),
            $donation->is_anonymous ? 'Ya' : 'Tidak',
            $donation->amount,
            $donation->payment_status,
            optional($donation->paid_at)->format('d/m/Y H:i'),
            $donation->created_at->format('d/m/Y H:i'),
        ];
    }
}
