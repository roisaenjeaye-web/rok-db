<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BotNotificationController;

class BotAutoNotify extends Command
{
    /**
     * Nama command yang dijalankan via artisan
     */
    protected $signature = 'bot:notify';

    /**
     * Deskripsi command
     */
    protected $description = 'Evaluasi dan kirim notifikasi WhatsApp otomatis untuk semua KD yang siap panen';

    /**
     * Jalankan command
     */
    public function handle(): int
    {
        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Mengecek status KD...');

        try {
            $result = BotNotificationController::evaluateAndNotifyKingdoms('Auto-Scheduler');

            if (!$result['auto_enabled']) {
                $this->line('  → Auto-notify dimatikan, skip.');
                return self::SUCCESS;
            }

            $count = $result['notified_count'] ?? 0;

            if ($count > 0) {
                $this->info("  ✅ Terkirim {$count} notifikasi:");
                foreach ($result['notified_kds'] as $item) {
                    $this->line("     • KD #{$item['kd']} ({$item['type']}) — {$item['ready_count']} char siap");
                }
            } else {
                $this->line('  → Tidak ada notifikasi yang perlu dikirim.');
            }
        } catch (\Throwable $e) {
            $this->error('  ❌ Error: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
