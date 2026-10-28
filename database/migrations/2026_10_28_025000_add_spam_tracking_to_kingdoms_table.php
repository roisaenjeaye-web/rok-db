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
                if (!Schema::hasColumn('kingdoms', 'replied_at')) {
                    $table->timestamp('replied_at')->nullable()->after('last_notified_at');
                }
                if (!Schema::hasColumn('kingdoms', 'replied_by')) {
                    $table->string('replied_by')->nullable()->after('replied_at');
                }
                if (!Schema::hasColumn('kingdoms', 'ready_since')) {
                    $table->timestamp('ready_since')->nullable()->after('replied_by');
                }
                if (!Schema::hasColumn('kingdoms', 'reminder_count')) {
                    $table->unsignedInteger('reminder_count')->default(0)->after('ready_since');
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
                $columns = ['replied_at', 'replied_by', 'ready_since', 'reminder_count'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('kingdoms', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
