<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_account_id', 'kd_number', 'ign', 'status', 
        'last_farmed_at', 'farm_count', 'farmer_stats', 'last_farmed_by'
    ];

    // Beritahu Laravel kalau ini adalah format Jam/Tanggal (Carbon) dan Array JSON
    protected $casts = [
        'last_farmed_at' => 'datetime',
        'farmer_stats' => 'array',
        'farm_count' => 'integer',
    ];

    public function gameAccount()
    {
        return $this->belongsTo(GameAccount::class);
    }

    // Accessor: Pastikan nama user Admin tidak pernah muncul di farmer_stats
    public function getFarmerStatsAttribute($value)
    {
        $stats = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($stats) || empty($stats)) {
            return [];
        }

        static $adminNames = null;
        if ($adminNames === null) {
            $adminNames = User::where('is_admin', true)->pluck('name')->toArray();
            if (empty($adminNames)) {
                $adminNames = ['Rois'];
            }
        }

        foreach ($adminNames as $adm) {
            unset($stats[$adm]);
        }

        return $stats;
    }

    // Helper untuk menambah log farm atas nama seorang worker/joki (Admin tidak dicatat)
    public function recordFarm(string $worker): void
    {
        // Admin tidak ikutan dicatat macul
        $isAdmin = User::where('name', $worker)->where('is_admin', true)->exists();
        if ($isAdmin) {
            return;
        }

        $stats = $this->farmer_stats ?? [];
        $stats[$worker] = ($stats[$worker] ?? 0) + 1;

        $this->farmer_stats = $stats;
        $this->farm_count = array_sum($stats);
        $this->last_farmed_by = $worker;
        $this->last_farmed_at = now();
        $this->save();
    }

    // Helper untuk mengurangi atau reset log farm per worker
    public function adjustFarmLog(string $worker, string $action, int $customCount = 0): void
    {
        // Admin tidak memiliki log macul
        $isAdmin = User::where('name', $worker)->where('is_admin', true)->exists();
        if ($isAdmin) {
            $stats = $this->farmer_stats ?? [];
            unset($stats[$worker]);
            $this->farmer_stats = $stats;
            $this->farm_count = array_sum($stats);
            $this->save();
            return;
        }

        $stats = $this->farmer_stats ?? [];

        if ($action === 'decrement') {
            $stats[$worker] = max(0, ($stats[$worker] ?? 0) - 1);
        } elseif ($action === 'increment') {
            $stats[$worker] = ($stats[$worker] ?? 0) + 1;
        } elseif ($action === 'reset') {
            $stats[$worker] = 0;
        } elseif ($action === 'set') {
            $stats[$worker] = max(0, $customCount);
        }

        $this->farmer_stats = $stats;
        $this->farm_count = array_sum($stats);
        $this->save();
    }
}