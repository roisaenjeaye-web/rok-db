<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->index('kd_number');
            $table->index('last_farmed_at');
            $table->index(['kd_number', 'last_farmed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropIndex(['characters_kd_number_index']);
            $table->dropIndex(['characters_last_farmed_at_index']);
            $table->dropIndex(['characters_kd_number_last_farmed_at_index']);
        });
    }
};
