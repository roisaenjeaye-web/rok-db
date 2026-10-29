<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\Character;
use App\Models\KdFarmerStat;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ambil semua nama user yang berstatus Admin
        $adminNames = User::where('is_admin', true)->pluck('name')->toArray();
        if (empty($adminNames)) {
            $adminNames = ['Rois'];
        }

        // 2. Hapus log macul admin dari tabel kd_farmer_stats
        KdFarmerStat::whereIn('worker_name', $adminNames)->delete();

        // 3. Bersihkan log macul admin dari setiap karakter & hitung ulang total farm_count
        $characters = Character::all();
        foreach ($characters as $char) {
            $stats = $char->farmer_stats ?? [];
            $changed = false;

            foreach ($adminNames as $adm) {
                if (isset($stats[$adm])) {
                    unset($stats[$adm]);
                    $changed = true;
                }
            }

            if ($changed || in_array($char->last_farmed_by, $adminNames)) {
                $char->farmer_stats = !empty($stats) ? $stats : null;
                $char->farm_count = !empty($stats) ? array_sum($stats) : 0;
                if (in_array($char->last_farmed_by, $adminNames)) {
                    $char->last_farmed_by = !empty($stats) ? array_key_first($stats) : null;
                }
                $char->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu reverse
    }
};
