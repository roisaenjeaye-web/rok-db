<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KdFarmerStat extends Model
{
    use HasFactory;

    protected $table = 'kd_farmer_stats';

    protected $fillable = [
        'kd_number',
        'worker_name',
        'farm_count',
        'last_farmed_at',
    ];

    protected $casts = [
        'last_farmed_at' => 'datetime',
        'farm_count' => 'integer',
        'kd_number' => 'integer',
    ];

    // Helper untuk menambah log farm untuk worker dan KD tertentu (Admin diabaikan)
    public static function incrementCount(int $kdNumber, string $workerName, int $by = 1): ?self
    {
        // Admin tidak dihitung macul
        $isAdmin = User::where('name', $workerName)->where('is_admin', true)->exists();
        if ($isAdmin) {
            return null;
        }

        $stat = self::firstOrCreate(
            ['kd_number' => $kdNumber, 'worker_name' => $workerName],
            ['farm_count' => 0]
        );

        $stat->farm_count += $by;
        $stat->last_farmed_at = now();
        $stat->save();

        return $stat;
    }

    // Helper untuk mengurangi log farm (tidak boleh minus dari 0)
    public static function decrementCount(int $kdNumber, string $workerName, int $by = 1): ?self
    {
        $isAdmin = User::where('name', $workerName)->where('is_admin', true)->exists();
        if ($isAdmin) {
            return null;
        }

        $stat = self::firstOrCreate(
            ['kd_number' => $kdNumber, 'worker_name' => $workerName],
            ['farm_count' => 0]
        );

        $stat->farm_count = max(0, $stat->farm_count - $by);
        $stat->save();

        return $stat;
    }

    // Helper untuk mereset log farm ke 0 atau angka tertentu
    public static function setCount(int $kdNumber, string $workerName, int $count = 0): ?self
    {
        $isAdmin = User::where('name', $workerName)->where('is_admin', true)->exists();
        if ($isAdmin) {
            return null;
        }

        $stat = self::firstOrCreate(
            ['kd_number' => $kdNumber, 'worker_name' => $workerName],
            ['farm_count' => 0]
        );

        $stat->farm_count = max(0, $count);
        $stat->save();

        return $stat;
    }
}
