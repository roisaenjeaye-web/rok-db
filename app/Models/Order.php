<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_name', 'kd_number', 'rss_type', 
        'amount_requested', 'amount_to_send', 
        'tax_percentage', 'total_price', 'status'
    ];
}