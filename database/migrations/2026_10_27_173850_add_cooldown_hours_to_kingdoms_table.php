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
        Schema::table('kingdoms', function (Blueprint $table) {
            $table->decimal('cooldown_hours', 4, 1)->default(4.0)->after('kd_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kingdoms', function (Blueprint $table) {
            $table->dropColumn('cooldown_hours');
        });
    }
};
