<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    // Helper: admin tidak pernah dihitung macul (pakai cache 1 query per-request)
    protected static function isAdminName(string $workerName): bool
    {
        static $cache = [];
        if (array_key_exists($workerName, $cache)) {
            return $cache[$workerName];
        }

        try {
            $cache[$workerName] = User::where('name', $workerName)
                ->where('is_admin', true)
                ->exists();
        } catch (\Throwable $e) {
            $cache[$workerName] = false;
        }

        return $cache[$workerName];
    }

    // Helper untuk menambah log farm untuk worker dan KD tertentu (Admin diabaikan)
    public static function incrementCount(int $kdNumber, string $workerName, int $by = 1): ?self
    {
        // Admin tidak dihitung macul
        if (self::isAdminName($workerName)) {
            return null;
        }

        $stat = self::firstOrCreate(
            ['kd_number' => $kdNumber, 'worker_name' => $workerName],
            ['farm_count' => 0]
        );

        // Atomic update: aman walau 2 operator klik bersamaan (hasil tidak bisa "hilang")
        $stat->newQuery()
            ->whereKey($stat->getKey())
            ->update([
                'farm_count' => DB::raw('farm_count + ' . (int) $by),
                'last_farmed_at' => now(),
                'updated_at' => now(),
            ]);

        return $stat->refresh();
    }

    // Helper untuk mengurangi log farm (tidak boleh minus dari 0)
    public static function decrementCount(int $kdNumber, string $workerName, int $by = 1): ?self
    {
        if (self::isAdminName($workerName)) {
            return null;
        }

        $stat = self::firstOrCreate(
            ['kd_number' => $kdNumber, 'worker_name' => $workerName],
            ['farm_count' => 0]
        );

        $stat->newQuery()
            ->whereKey($stat->getKey())
            ->update([
                'farm_count' => DB::raw('CASE WHEN farm_count - ' . (int) $by . ' < 0 THEN 0 ELSE farm_count - ' . (int) $by . ' END'),
                'updated_at' => now(),
            ]);

        return $stat->refresh();
    }

    // Helper untuk mereset log farm ke 0 atau angka tertentu
    public static function setCount(int $kdNumber, string $workerName, int $count = 0): ?self
    {
        if (self::isAdminName($workerName)) {
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
