<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BotSetting;
use App\Models\Kingdom;
use App\Services\FonnteService;

class SendTestWaCommand extends Command
{
    protected $signature = 'wa:test';
    protected $description = 'Kirim pesan tes WhatsApp dan daftarkan webhook ke Fonnte';

    public function handle(): int
    {
        $this->info("1. Mengupdate Webhook di server Fonnte...");
        $webhookUrl = 'https://rok-db.vercel.app/api/fonnte/webhook';
        $res = FonnteService::updateDeviceWebhook($webhookUrl);
        $this->info("   Hasil: " . json_encode($res));

        $phone = BotSetting::get('target_phone');
        $token = BotSetting::get('fonnte_token');
        if (empty($phone) || empty($token)) {
            $this->error("Nomor target atau Token belum diset di bot_settings!");
            return self::FAILURE;
        }

        $nowStr = now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB';

        $msg = "🌾 *TES DETEKSI BALASAN BOT RO-K JOKI* 🌾\n";
        $msg .= "━━━━━━━━━━━━━━━━━\n";
        $msg .= "🏰 *Target:* KD #2359 (Tes Balasan Otomatis)\n";
        $msg .= "🟢 *Status:* Siap Panen (25 Char)\n";
        $msg .= "📅 *Waktu:* {$nowStr}\n";
        $msg .= "@all\n";
        $msg .= "━━━━━━━━━━━━━━━━━\n";
        $msg .= "⚠️ *Mohon Konfirmasi Sekarang!*\n";
        $msg .= "Silakan balas chat ini sekarang dengan:\n";
        $msg .= "👉 *\"2359 otw\"* atau *\"otw\"*\n\n";
        $msg .= "Bot akan otomatis memproses dan membalas konfirmasi kamu! 🚜";

        $this->info("2. Mengirim pesan ke target: {$phone}...");
        $sendRes = FonnteService::sendMessage($phone, $msg, $token);
        $this->info("   Hasil Kirim: " . json_encode($sendRes));

        // Set last_notified_at untuk KD 2359 agar terhubung dengan balasan
        $kd = Kingdom::where('kd_number', 2359)->first();
        if ($kd) {
            $kd->last_notified_at = now();
            $kd->replied_at = null; // Menunggu balasan
            $kd->save();
        }

        return self::SUCCESS;
    }
}

