<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BotNotificationController;

class CheckFarmCooldownAndNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:check-farm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek semua KD dan kirim notifikasi/spam WhatsApp otomatis (10m jika belum dibalas, 5m jika lewat 30m belum dikelarin)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Menjalankan evaluasi bot pengingat farm RoK...");

        $result = BotNotificationController::evaluateAndNotifyKingdoms();

        if (!($result['success'] ?? false)) {
            $this->error("❌ Gagal: " . ($result['message'] ?? 'Unknown error'));
            return 1;
        }

        if (!($result['auto_enabled'] ?? true)) {
            $this->warn("⚠️ Auto-notify sedang dinonaktifkan di pengaturan bot.");
            return 0;
        }

        $notified = $result['notified_kds'] ?? [];
        if (empty($notified)) {
            $this->info("ℹ️ Tidak ada notifikasi/spam baru yang perlu dikirim saat ini.");
            return 0;
        }

        foreach ($notified as $item) {
            $type = $item['type'] ?? 'unknown';
            $kd = $item['kd'];
            $count = $item['ready_count'];
            $remCount = $item['reminder_count'] ?? 1;

            if ($type === 'initial_ready') {
                $this->info("🚨 [NOTIF SIAP] KD #{$kd} ({$count} char siap) -> Notifikasi pertama terkirim!");
            } elseif ($type === 'spam_10m_unreplied') {
                $this->warn("⚠️ [SPAM 10 MENIT] KD #{$kd} ({$count} char siap) -> Spam #{$remCount} terkirim karena belum ada balasan WA!");
            } elseif ($type === 'spam_5m_overdue') {
                $this->error("🔥 [SPAM 5 MENIT] KD #{$kd} ({$count} char siap) -> Spam overdue #{$remCount} terkirim karena lewat 30m belum diceklis!");
            }
        }

        $this->info("✅ Sukses memproses " . count($notified) . " pengingat/spam bot WhatsApp.");
        return 0;
    }
}
