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
        if (Schema::hasTable('kingdoms')) {
            Schema::table('kingdoms', function (Blueprint $table) {
                if (!Schema::hasColumn('kingdoms', 'locked_by')) {
                    $table->string('locked_by')->nullable()->after('reminder_count');
                }
                if (!Schema::hasColumn('kingdoms', 'locked_at')) {
                    $table->timestamp('locked_at')->nullable()->after('locked_by');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kingdoms')) {
            Schema::table('kingdoms', function (Blueprint $table) {
                $cols = ['locked_by', 'locked_at'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('kingdoms', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
