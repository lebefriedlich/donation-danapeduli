<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'integer',
        'is_anonymous' => 'boolean',
        'paid_at' => 'datetime',
    ];

    /* ========================
     | Relationships
     |======================== */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
