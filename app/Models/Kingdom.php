<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kingdom extends Model
{
    use HasFactory;

    protected $fillable = [
        'kd_number',
        'cooldown_hours',
        'last_notified_at',
        'replied_at',
        'replied_by',
        'ready_since',
        'reminder_count',
        'food',
        'wood',
        'stone',
        'gold'
    ];

    protected $casts = [
        'cooldown_hours' => 'float',
        'last_notified_at' => 'datetime',
        'replied_at' => 'datetime',
        'ready_since' => 'datetime',
        'reminder_count' => 'integer',
    ];
}