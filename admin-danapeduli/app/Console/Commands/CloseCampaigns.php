<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use Illuminate\Console\Command;

class CloseCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis menutup campaign yang melewati close_at atau mencapai target';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $closedCount = 0;

        // Auto-close berdasarkan tanggal close_at
        Campaign::where('status', 'ACTIVE')
            ->whereNotNull('close_at')
            ->where('close_at', '<=', now())
            ->each(function (Campaign $campaign) use (&$closedCount) {
                if ($campaign->closeCampaign()) {
                    $this->info("Campaign #{$campaign->id} '{$campaign->title}' ditutup (melewati close_at)");
                    $closedCount++;
                }
            });

        if ($closedCount === 0) {
            $this->info('Tidak ada campaign yang perlu ditutup.');
        } else {
            $this->info("Total {$closedCount} campaign berhasil ditutup.");
        }

        return Command::SUCCESS;
    }
}
