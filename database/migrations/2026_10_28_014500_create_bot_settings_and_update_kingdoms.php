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
        // 1. Tabel Bot Settings
        if (!Schema::hasTable('bot_settings')) {
            Schema::create('bot_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tambah kolom last_notified_at di tabel kingdoms jika belum ada
        if (Schema::hasTable('kingdoms') && !Schema::hasColumn('kingdoms', 'last_notified_at')) {
            Schema::table('kingdoms', function (Blueprint $table) {
                $table->timestamp('last_notified_at')->nullable()->after('cooldown_hours');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_settings');

        if (Schema::hasTable('kingdoms') && Schema::hasColumn('kingdoms', 'last_notified_at')) {
            Schema::table('kingdoms', function (Blueprint $table) {
                $table->dropColumn('last_notified_at');
            });
        }
    }
};
