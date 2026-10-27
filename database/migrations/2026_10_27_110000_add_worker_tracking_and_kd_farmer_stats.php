<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom ke characters
        Schema::table('characters', function (Blueprint $table) {
            $table->json('farmer_stats')->nullable()->after('farm_count');
            $table->string('last_farmed_by')->nullable()->after('farmer_stats');
        });

        // 2. Tabel untuk rekap log joki per Kingdom (misal: Rois di KD 2359 = 100x, Therta di KD 2359 = 20x)
        Schema::create('kd_farmer_stats', function (Blueprint $table) {
            $table->id();
            $table->integer('kd_number');
            $table->string('worker_name');
            $table->integer('farm_count')->default(0);
            $table->timestamp('last_farmed_at')->nullable();
            $table->timestamps();

            $table->unique(['kd_number', 'worker_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kd_farmer_stats');

        Schema::table('characters', function (Blueprint $table) {
            if (Schema::hasColumn('characters', 'farmer_stats')) {
                $table->dropColumn('farmer_stats');
            }
            if (Schema::hasColumn('characters', 'last_farmed_by')) {
                $table->dropColumn('last_farmed_by');
            }
        });
    }
};
