<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index untuk query "whereNotIn worker_name" + grouping per worker di Dashboard Stok
        Schema::table('salaries', function (Blueprint $table) {
            $table->index('worker_name');
            $table->index('date');
        });

        Schema::table('kd_farmer_stats', function (Blueprint $table) {
            $table->index('worker_name');
        });

        // Index untuk lookup Kingdom by kd_number (dipanggil hampir di semua aksi farm/bot)
        Schema::table('kingdoms', function (Blueprint $table) {
            $table->index('kd_number');
        });

        // Index untuk filter game_account + kd (dipakai di farmDoneBulk)
        Schema::table('characters', function (Blueprint $table) {
            $table->index(['game_account_id', 'kd_number']);
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropIndex(['salaries_worker_name_index']);
            $table->dropIndex(['salaries_date_index']);
        });

        Schema::table('kd_farmer_stats', function (Blueprint $table) {
            $table->dropIndex(['kd_farmer_stats_worker_name_index']);
        });

        Schema::table('kingdoms', function (Blueprint $table) {
            $table->dropIndex(['kingdoms_kd_number_index']);
        });

        Schema::table('characters', function (Blueprint $table) {
            $table->dropIndex(['characters_game_account_id_kd_number_index']);
        });
    }
};
