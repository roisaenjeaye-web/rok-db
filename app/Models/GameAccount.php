<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameAccount extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'password', 'notes'];

    // Relasi: 1 Akun Game memiliki Banyak Karakter
    public function characters()
    {
        return $this->hasMany(Character::class);
    }
}