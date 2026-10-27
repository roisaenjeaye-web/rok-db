<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->timestamp('last_farmed_at')->nullable(); // Waktu terakhir farm
            $table->integer('farm_count')->default(0);       // Total berapa kali login/farm
        });
    }

    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            if (Schema::hasColumn('characters', 'last_farmed_at')) {
                $table->dropColumn('last_farmed_at');
            }
            if (Schema::hasColumn('characters', 'farm_count')) {
                $table->dropColumn('farm_count');
            }
        });
    }
};