<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kingdom extends Model
{
    use HasFactory;

    // Tambahkan baris ini biar Laravel ngijinin data masuk
    protected $fillable = ['kd_number', 'food', 'wood', 'stone', 'gold'];
}