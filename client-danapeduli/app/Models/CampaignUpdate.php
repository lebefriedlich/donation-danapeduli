<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignUpdate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /* ========================
     | Relationships
     |======================== */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
}
