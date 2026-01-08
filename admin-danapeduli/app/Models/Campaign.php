<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Campaign extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'target_amount' => 'integer',
        'total_paid' => 'integer',
        'auto_close_on_target' => 'boolean',
        'open_at' => 'datetime',
        'close_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Campaign $campaign) {
            if (blank($campaign->slug)) {
                $campaign->slug = static::generateUniqueSlug($campaign->title);
            }
        });

        static::updating(function (Campaign $campaign) {
            if ($campaign->isDirty('title')) {
                $campaign->slug = static::generateUniqueSlug($campaign->title);
            }
        });
    }

    protected static function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    /* ========================
     | Relationships
     |======================== */
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function updates()
    {
        return $this->hasMany(CampaignUpdate::class);
    }

    /* ========================
     | Auto Close Logic
     |======================== */
    
    /**
     * Tutup campaign secara otomatis (set status CLOSED dan isi closed_at)
     */
    public function closeCampaign(): bool
    {
        if ($this->status === 'CLOSED' || $this->status === 'ARCHIVED') {
            return false; // Sudah ditutup
        }

        $this->update([
            'status' => 'CLOSED',
            'closed_at' => now(),
        ]);

        return true;
    }
}
