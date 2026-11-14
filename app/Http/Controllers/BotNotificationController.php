<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BotSetting;
use App\Models\Kingdom;
use App\Models\Character;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Log;

class BotNotificationController extends Controller
{
    /**
     * Guard: pastikan yang akses adalah admin
     */
    private function requireAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, '❌ Akses ditolak! Fitur ini hanya untuk Admin.');
        }
    }

    /**
     * Cek apakah saat ini berada di Jam Hening (Quiet Hours / Jam Istirahat Malam).
     * Default: 00:00 s/d 08:00 WIB (Spam bot berhenti jam 12 malam dan mulai lagi jam 8 pagi).
     */
    public static function isQuietHours(): bool
    {
        $quietEnabled = BotSetting::getBoolean('quiet_hours_enabled', true);
        if (!$quietEnabled) {
            return false;
        }

        $nowWib = now()->setTimezone('Asia/Jakarta');
        $hour   = (int) $nowWib->format('H'); // 0 sampai 23

        $quietStart = (int) BotSetting::get('quiet_hours_start', 0); // 0 = 00:00 (12 malam)
        $quietEnd   = (int) BotSetting::get('quiet_hours_end', 8);   // 8 = 08:00 pagi

        if ($quietStart < $quietEnd) {
            return ($hour >= $quietStart && $hour < $quietEnd);
        } else {
            return ($hour >= $quietStart || $hour < $quietEnd);
        }
    }

    /**
     * Helper untuk mengambil seluruh pengaturan bot saat ini
     */
    private function currentSettings(): array
    {
        return [
            'fonnte_token' => BotSetting::get('fonnte_token', ''),
            'target_phone' => BotSetting::get('target_phone', ''),
            'is_auto_notify' => BotSetting::getBoolean('is_auto_notify', true),
            'spam_unreplied_interval' => (int) BotSetting::get('spam_unreplied_interval', 30),
            'max_work_minutes' => (int) BotSetting::get('max_work_minutes', 30),
            'spam_overdue_interval' => (int) BotSetting::get('spam_overdue_interval', 15),
            'quiet_hours_enabled' => BotSetting::getBoolean('quiet_hours_enabled', true),
            'quiet_hours_start' => (int) BotSetting::get('quiet_hours_start', 0),
            'quiet_hours_end' => (int) BotSetting::get('quiet_hours_end', 8),
            'is_quiet_hours' => self::isQuietHours(),
            'webhook_url' => $this->getWebhookUrl(),
        ];
    }

    // Ambil setting bot
    public function getSettings()
    {
        return response()->json(array_merge(
            ['success' => true],
            $this->currentSettings(),
            ['is_admin' => auth()->check() && auth()->user()->isAdmin()]
        ));
    }

    // Toggle Cepat Matikan / Aktifkan Bot CD (Mode Tidur) - Admin & Operator
    public function toggleAutoNotify(Request $request)
    {
        if (!auth()->check()) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        $current = BotSetting::getBoolean('is_auto_notify', true);
        $newStatus = !$current;
        BotSetting::set('is_auto_notify', $newStatus ? '1' : '0');

        $actorName = auth()->user()->name ?? 'Operator';
        $roleName  = (auth()->user()->isAdmin()) ? 'Admin' : 'Operator';
        $msg = $newStatus
            ? "🔔 Bot Pengingat CD berhasil diaktifkan kembali oleh {$roleName} {$actorName}!"
            : "😴 Bot Pengingat CD dimatikan (Mode Tidur) oleh {$roleName} {$actorName}! Pengingat & spam dinonaktifkan.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_auto_notify' => $newStatus,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    // Simpan setting bot (Admin Only)
    public function saveSettings(Request $request)
    {
        $this->requireAdmin();

        $request->validate([
            'fonnte_token' => 'nullable|string',
            'target_phone' => 'nullable|string',
            'is_auto_notify' => 'nullable|boolean',
            'spam_unreplied_interval' => 'nullable|integer|min:1|max:1440',
            'max_work_minutes' => 'nullable|integer|min:1|max:1440',
            'spam_overdue_interval' => 'nullable|integer|min:1|max:1440',
            'quiet_hours_enabled' => 'nullable|boolean',
            'quiet_hours_start' => 'nullable|integer|min:0|max:23',
            'quiet_hours_end' => 'nullable|integer|min:0|max:23',
        ]);

        if ($request->has('fonnte_token')) {
            BotSetting::set('fonnte_token', trim($request->input('fonnte_token', '')));
        }

        if ($request->has('target_phone')) {
            BotSetting::set('target_phone', trim($request->input('target_phone', '')));
        }

        if ($request->has('is_auto_notify')) {
            BotSetting::set('is_auto_notify', $request->boolean('is_auto_notify') ? '1' : '0');
        }

        if ($request->has('spam_unreplied_interval')) {
            BotSetting::set('spam_unreplied_interval', (int) $request->input('spam_unreplied_interval'));
        }

        if ($request->has('max_work_minutes')) {
            BotSetting::set('max_work_minutes', (int) $request->input('max_work_minutes'));
        }

        if ($request->has('spam_overdue_interval')) {
            BotSetting::set('spam_overdue_interval', (int) $request->input('spam_overdue_interval'));
        }

        if ($request->has('quiet_hours_enabled')) {
            BotSetting::set('quiet_hours_enabled', $request->boolean('quiet_hours_enabled') ? '1' : '0');
        }

        if ($request->has('quiet_hours_start')) {
            BotSetting::set('quiet_hours_start', (int) $request->input('quiet_hours_start'));
        }

        if ($request->has('quiet_hours_end')) {
            BotSetting::set('quiet_hours_end', (int) $request->input('quiet_hours_end'));
        }

        $webhookUrl = $this->getWebhookUrl();
        $fonnteToken = BotSetting::get('fonnte_token', '');
        if (!empty($fonnteToken)) {
            FonnteService::updateDeviceWebhook($webhookUrl, $fonnteToken);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan Bot WhatsApp Fonnte berhasil disimpan dan Webhook otomatis disinkronkan!',
            'settings' => $this->currentSettings(),
        ]);
    }

    // Cek status device Fonnte
    public function testConnection(Request $request)
    {
        $token = $request->input('fonnte_token') ?: BotSetting::get('fonnte_token');
        $result = FonnteService::checkDeviceStatus($token);

        return response()->json($result);
    }

    // Ambil daftar grup WhatsApp
    public function getGroups(Request $request)
    {
        $token = $request->input('fonnte_token') ?: BotSetting::get('fonnte_token');
        $result = FonnteService::getGroups($token);

        return response()->json($result);
    }

    // Tes kirim pesan manual via bot
    public function testSend(Request $request)
    {
        $phone = $request->input('target_phone') ?: BotSetting::get('target_phone');
        $token = $request->input('fonnte_token') ?: BotSetting::get('fonnte_token');

        if (empty($phone)) {
            return response()->json(['success' => false, 'message' => 'Nomor WhatsApp tujuan belum diisi!'], 400);
        }

        $nowStr = now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB';
        $testMsg = "🤖 *TES BOT WHATSAPP RoK JOKI BERHASIL!* 🚀\n";
        $testMsg .= "━━━━━━━━━━━━━━━━━\n";
        $testMsg .= "✅ Sistem Bot Fonnte berhasil terhubung & siap mengirim pengingat panen otomatis serta sistem anti-lupa (spam pengingat)!\n";
        $testMsg .= "📅 Waktu: {$nowStr}\n";
        $testMsg .= "@all\n";
        $testMsg .= "━━━━━━━━━━━━━━━━━\n";
        $testMsg .= "🔗 *Buka Dashboard:* " . $this->getDashboardUrl() . "\n";
        $testMsg .= "_Semangat macul & panen RSS!_ 🌾🚜";

        $result = FonnteService::sendMessage($phone, $testMsg, $token);

        return response()->json($result);
    }

    // Kirim notifikasi bot untuk 1 KD tertentu
    public function notifySingleKd(Request $request, $kd)
    {
        $phone = $request->input('target_phone') ?: BotSetting::get('target_phone');
        if (empty($phone)) {
            return response()->json(['success' => false, 'message' => 'Nomor WhatsApp tujuan belum diatur!'], 400);
        }

        $gameCtrl = app(GameAccountController::class);
        $worker = $gameCtrl->getActiveWorker($request);

        $kingdom = Kingdom::where('kd_number', $kd)->first();
        $cooldownHours = $kingdom ? (float) $kingdom->cooldown_hours : 4.0;
        $cooldownMinutes = (int) round($cooldownHours * 60);

        $chars = Character::where('kd_number', $kd)->get();
        $readyCount = 0;
        $maxCooldownEnd = null;

        foreach ($chars as $c) {
            if (!$c->last_farmed_at || now()->greaterThanOrEqualTo($c->last_farmed_at->copy()->addMinutes($cooldownMinutes))) {
                $readyCount++;
            } else {
                $end = $c->last_farmed_at->copy()->addMinutes($cooldownMinutes);
                if ($maxCooldownEnd === null || $end->greaterThan($maxCooldownEnd)) {
                    $maxCooldownEnd = $end;
                }
            }
        }

        $msg = $this->buildSingleKdText($kd, $readyCount, $chars->count(), $cooldownHours, $maxCooldownEnd, $worker);
        $result = FonnteService::sendMessage($phone, $msg);

        if ($result['success'] && $kingdom) {
            $kingdom->last_notified_at = now();
            if ($readyCount > 0 && !$kingdom->ready_since) {
                $kingdom->ready_since = now();
            }
            $kingdom->save();
        }

        return response()->json($result);
    }

    // Kirim rekap bot untuk semua KD
    public function notifyAllKds(Request $request)
    {
        $phone = $request->input('target_phone') ?: BotSetting::get('target_phone');
        if (empty($phone)) {
            return response()->json(['success' => false, 'message' => 'Nomor WhatsApp tujuan belum diatur!'], 400);
        }

        $gameCtrl = app(GameAccountController::class);
        $worker = $gameCtrl->getActiveWorker($request);

        $msg = $this->buildAllKdsText($worker);
        $result = FonnteService::sendMessage($phone, $msg);

        return response()->json($result);
    }

    // Tandai KD sudah dibalas secara manual lewat Web Dashboard
    public function acknowledgeKd(Request $request, $kd)
    {
        $kingdom = Kingdom::where('kd_number', $kd)->first();
        if (!$kingdom) {
            $kingdom = Kingdom::firstOrCreate(['kd_number' => $kd]);
        }

        $worker = auth()->check() ? auth()->user()->name : ($request->input('worker') ?: 'Operator Web');
        $kingdom->replied_at = now();
        $kingdom->replied_by = $worker;
        $kingdom->save();

        $maxWorkMinutes = (int) BotSetting::get('max_work_minutes', 30);
        $spamOverdueInterval = (int) BotSetting::get('spam_overdue_interval', 15);

        $targetPhone = BotSetting::get('target_phone');
        if (!empty($targetPhone)) {
            $msg = "👍 *INFO: KD #{$kd} SEDANG DIKERJAKAN!* 🎯\n";
            $msg .= "━━━━━━━━━━━━━━━━━\n";
            $msg .= "👤 *Operator:* {$worker} (via Web Dashboard)\n";
            $msg .= "⏱️ *Tenggat Waktu:* {$maxWorkMinutes} Menit dari sekarang\n";
            $msg .= "━━━━━━━━━━━━━━━━━\n";
            $msg .= "🔗 *Buka Dashboard:* " . $this->getDashboardUrl() . "\n";
            $msg .= "_Spam pengingat dijeda {$maxWorkMinutes} menit. Selesaikan panen sebelum spam overdue aktif!_ 🌾🚜";

            FonnteService::sendMessage($targetPhone, $msg);
        }

        return response()->json([
            'success' => true,
            'message' => "KD #{$kd} berhasil ditandai sedang dikerjakan oleh {$worker}! Tenggat {$maxWorkMinutes} menit dimulai.",
            'replied_at' => $kingdom->replied_at->toIso8601String(),
            'replied_by' => $kingdom->replied_by,
        ]);
    }

    // Webhook Fonnte untuk menerima balasan chat dari member grup atau nomor user
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        if (empty($payload) || empty($payload['sender'])) {
            $raw = $request->getContent() ?: @file_get_contents('php://input');
            if ($raw) {
                $decoded = @json_decode($raw, true);
                if (is_array($decoded)) {
                    $payload = array_merge($decoded, $payload);
                }
            }
        }

        Log::info('Fonnte Webhook Received: ' . json_encode($payload));

        // Abaikan webhook status pengiriman pesan (delivery receipt: sent, delivered, pending, failed)
        if (!empty($payload['status']) && in_array(strtolower($payload['status']), ['sent', 'delivered', 'pending', 'failed', 'read']) && empty($payload['message'])) {
            return response()->json(['status' => true, 'message' => 'Status delivery diabaikan']);
        }

        $sender = (string) ($payload['sender'] ?? $request->input('sender', ''));
        $member = (string) ($payload['member'] ?? $request->input('member', ''));
        $rawName = trim((string) ($payload['name'] ?? ($payload['pushname'] ?? ($request->input('name') ?: ''))));
        $message = trim((string) ($payload['message'] ?? $request->input('message', '')));
        $device = (string) ($payload['device'] ?? $request->input('device', ''));
        $inboxid = (string) ($payload['inboxid'] ?? $request->input('inboxid', ''));

        // Bersihkan angka untuk deteksi nomor bot sendiri
        $cleanDevice = preg_replace('/[^0-9]/', '', $device);
        $cleanMember = preg_replace('/[^0-9]/', '', $member);
        $cleanSender = preg_replace('/[^0-9]/', '', $sender);

        // Resolusi nama pengirim: prioritaskan name/pushname dari WA, atau nama Operator dari User DB
        if (!empty($rawName) && $rawName !== 'Anggota Grup') {
            $name = $rawName;
        } elseif (!empty($cleanMember)) {
            $matchedUser = \App\Models\User::where('phone', 'like', "%{$cleanMember}%")->first();
            $name = $matchedUser ? $matchedUser->name : ('+' . $cleanMember);
        } else {
            $name = 'Operator';
        }

        // 1. FILTER UTAMA ANTI-LOOPING: Abaikan jika pesan berisi teks template bot sendiri
        if (str_contains($message, 'BALASAN DITERIMA') 
            || str_contains($message, 'PANGGILAN PANEN') 
            || str_contains($message, 'PANEN SERENTAK')
            || str_contains($message, 'Sent via fonnte.com')
            || str_contains($message, 'Buka & Ceklis di Web')
            || str_contains($message, 'Buka Dashboard')
            || str_contains($message, 'Semangat macul')
            || str_contains($message, 'rok-db.vercel.app')
            || in_array(strtolower($rawName), ['bot rok', 'rokalth', 'bot'])) {
            return response()->json(['status' => true, 'message' => 'Abaikan pesan otomatis bot sendiri']);
        }

        // 2. Abaikan jika pesan dikirim oleh nomor bot sendiri (agar tidak looping self-reply)
        if ($cleanMember === '6289506819226' || $cleanSender === '6289506819226' || (!empty($cleanDevice) && ($cleanMember === $cleanDevice || $cleanSender === $cleanDevice))) {
            return response()->json(['status' => true, 'message' => 'Abaikan pesan sendiri dari bot']);
        }

        // 3. Debounce antrean duplikat pesan yang sama dalam 30 detik
        $debounceKey = 'fonnte_msg_' . md5($sender . '_' . $member . '_' . $message);
        if (\Illuminate\Support\Facades\Cache::has($debounceKey)) {
            return response()->json(['status' => true, 'message' => 'Pesan duplikat diabaikan (debounce)']);
        }
        \Illuminate\Support\Facades\Cache::put($debounceKey, true, now()->addSeconds(30));

        $targetPhone = (string) BotSetting::get('target_phone', '');
        if (!empty($targetPhone)) {
            $cleanTarget = preg_replace('/[^0-9]/', '', $targetPhone);

            // Validasi apakah pesan berasal dari grup target, member grup target, atau japri langsung ke bot
            $isTargetGroup = ($cleanSender && $cleanTarget && $cleanSender === $cleanTarget)
                || str_contains($targetPhone, $sender)
                || (!empty($cleanSender) && str_contains($sender, $cleanTarget))
                || (!empty($cleanTarget) && str_contains($cleanSender, $cleanTarget));
            $isTargetDirect = ($member && str_contains($targetPhone, $member))
                || (!empty($cleanMember) && !empty($cleanTarget) && str_contains($cleanTarget, $cleanMember));
            $isSenderDirect = ($sender === $targetPhone)
                || (!empty($cleanSender) && !empty($cleanTarget) && $cleanSender === $cleanTarget);
            $isPrivateChat = !str_contains($sender, '@g.us');

            if (!$isTargetGroup && !$isTargetDirect && !$isSenderDirect && !$isPrivateChat) {
                return response()->json(['status' => true, 'message' => 'Pesan dari luar target diabaikan']);
            }
        }

        // 1. Cek apakah pesan mengandung negasi / pembatalan (contoh: "ga bisa otw", "belum siap", "jangan gas")
        if (preg_match('/\b(ga|gak|ngga|nggak|bukan|belum|blm|jangan|batal|cancel)\b/i', $message)) {
            return response()->json(['status' => true, 'message' => 'Pesan mengandung negasi/pembatalan, diabaikan']);
        }

        // 2. Deteksi apakah pesan menyebut KD spesifik yang valid di database (contoh: "kd 4180 otw", "4180 siap", "#4180", "4180")
        $targetKd = null;
        if (preg_match('/(?:kd\s*#?|#)?\b(\d{3,5})\b/i', $message, $m)) {
            $candidateKd = (int) $m[1];
            if (Kingdom::where('kd_number', $candidateKd)->exists()) {
                $targetKd = $candidateKd;
            }
        }

        // 3. Deteksi kata kunci konfirmasi aksi joki yang jelas (HAPUS kata obrolan santai: gua, gw, saya, aku, on, iya, dah, bentar)
        $isActionKeyword = (bool) preg_match('/\b(otw+|otewe|siap+|siaap+|gas+|gass+|gaskeun|macul|login|handle|kerjakan|kerjain|panen|farming|farm)\b/i', $message);

        // Respons singkat spesifik (hanya jika pesan pendek <= 15 karakter)
        $trimmedMsg = trim(strtolower($message));
        $isShortAck = strlen($trimmedMsg) <= 15 && (bool) preg_match('/^(?:ok+|oke+|ok\s*siap|ok\s*gas|done|aman|siap\s*(?:min|gan|bang|bro|bos)?)$/i', $trimmedMsg);

        $isAckKeyword = $isActionKeyword || $isShortAck;

        // Jika tidak ada nomor KD valid dan juga bukan kata kunci konfirmasi, abaikan agar obrolan biasa di grup tidak memicu bot
        if ($targetKd === null && !$isAckKeyword) {
            return response()->json(['status' => true, 'message' => 'Bukan pesan konfirmasi KD']);
        }

        $maxWorkMinutes = (int) BotSetting::get('max_work_minutes', 30);
        $spamOverdueInterval = (int) BotSetting::get('spam_overdue_interval', 15);

        // Evaluasi KD yang berstatus Siap Panen (readyCount > 0)
        $groupedByKD = Character::with('gameAccount')->get()->groupBy('kd_number')->sortKeys();
        $kingdoms = Kingdom::all()->keyBy('kd_number');

        $acknowledgedKds = [];

        foreach ($groupedByKD as $kd => $kChars) {
            // Jika user menyebut KD spesifik, hanya proses KD tersebut
            if ($targetKd !== null && (int)$kd !== $targetKd) {
                continue;
            }

            $kingdom = $kingdoms->get($kd);
            $cooldownHours = $kingdom ? (float) $kingdom->cooldown_hours : 4.0;
            $cooldownMinutes = (int) round($cooldownHours * 60);

            // Hitung jumlah char yang siap panen
            $readyCount = 0;
            foreach ($kChars as $c) {
                if (!$c->last_farmed_at || now()->greaterThanOrEqualTo($c->last_farmed_at->copy()->addMinutes($cooldownMinutes))) {
                    $readyCount++;
                }
            }

            // ATURAN 1: KD HARUS MEMILIKI CHAR SIAP PANEN (readyCount > 0)!
            // Jika semua char masih cooldown atau baru selesai panen, JANGAN PERNAH tandai sedang dikerjakan!
            if ($readyCount === 0) {
                if ($kingdom && ($kingdom->replied_at || $kingdom->ready_since || ($kingdom->reminder_count ?? 0) > 0)) {
                    $kingdom->replied_at       = null;
                    $kingdom->replied_by       = null;
                    $kingdom->ready_since      = null;
                    $kingdom->reminder_count   = 0;
                    $kingdom->save();
                }
                continue;
            }

            // ATURAN 2: TASK SEDANG JALAN (Sudah dibalas & masih dalam tenggat maxWorkMinutes)
            // Jika KD sudah dibalas joki dalam 30 menit terakhir dan belum overdue, jangan di-acknowledge berulang kali!
            $isCurrentlyWorking = $kingdom && $kingdom->replied_at && $kingdom->replied_at->greaterThan(now()->subMinutes($maxWorkMinutes));
            if ($isCurrentlyWorking) {
                continue;
            }

            // ATURAN 3: ANTI-SPAM GUARD 5 MENIT
            if ($kingdom && $kingdom->replied_at && $kingdom->replied_at->greaterThan(now()->subMinutes(5))) {
                continue;
            }

            // ATURAN 4: JIKA PESAN TIDAK MENYEBUT NOMOR KD SECARA SPESIFIK:
            // HANYA proses KD yang memang sedang dipanggil bot / aktif siap panen dalam 2 jam terakhir
            if ($targetKd === null) {
                $hasActiveNotif = $kingdom && (
                    ($kingdom->last_notified_at && $kingdom->last_notified_at->greaterThanOrEqualTo(now()->subHours(2)))
                    || $kingdom->ready_since !== null
                );
                if (!$hasActiveNotif) {
                    continue;
                }
            }

            // Lolos semua kriteria -> Tandai KD sedang dikerjakan oleh joki
            if (!$kingdom) {
                $kingdom = Kingdom::firstOrCreate(['kd_number' => $kd]);
            }

            $kingdom->replied_at = now();
            $kingdom->replied_by = $name;
            if (!$kingdom->ready_since) {
                $kingdom->ready_since = now();
            }
            $kingdom->save();

            $acknowledgedKds[] = [
                'kd' => $kd,
                'ready_count' => $readyCount,
            ];
        }

        // CATATAN: Fallback ke recentKingdom (KD jeda/cooldown) DIHAPUS TOTAL!
        // Jika tidak ada KD yang siap panen atau task sedang jalan, bot diam dan tidak mengirim apa-apa.

        // Jika ada KD yang diakui balasan, kirim balasan otomatis ke grup
        if (!empty($acknowledgedKds)) {
            $kdListStr = implode(', ', array_map(fn($item) => "KD #{$item['kd']}", $acknowledgedKds));
            $replyMsg = "👍 *BALASAN DITERIMA DARI {$name}!* 🎯\n";
            $replyMsg .= "━━━━━━━━━━━━━━━━━\n";
            $replyMsg .= "🏰 *Target:* {$kdListStr}\n";
            $replyMsg .= "⏳ *Status:* Ditandai sedang dikerjakan!\n";
            $replyMsg .= "⏱️ *Tenggat Waktu:* {$maxWorkMinutes} Menit dari sekarang.\n";
            $replyMsg .= "━━━━━━━━━━━━━━━━━\n";
            $replyMsg .= "🔗 *Buka & Ceklis di Web:* " . $this->getDashboardUrl() . "\n";
            $replyMsg .= "_Jika dalam {$maxWorkMinutes} menit belum selesai/diceklis di web, bot akan spam tiap {$spamOverdueInterval} menit. Semangat macul!_ 🌾🚜";

            $phoneToSend = (str_contains($sender, '@g.us') && !empty($sender)) ? $sender : ($targetPhone ?: $sender);
            FonnteService::sendMessage($phoneToSend, $replyMsg, null, $inboxid ?: null);

            return response()->json([
                'status' => true,
                'message' => 'Webhook berhasil diproses: balasan joki diterima',
                'acknowledged_kds' => $acknowledgedKds,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Tidak ada panggilan panen aktif yang membutuhkan balasan saat ini',
            'acknowledged_kds' => [],
        ]);
    }

    // Heartbeat Auto-Notify: Dipanggil oleh browser satset.js
    public function checkAndAutoNotify(Request $request)
    {
        $gameCtrl = app(GameAccountController::class);
        $worker = $gameCtrl->getActiveWorker($request);

        $result = self::evaluateAndNotifyKingdoms($worker);

        return response()->json($result);
    }

    /**
     * Core Engine: Mengevaluasi status semua KD dan mengirim notifikasi / spam gabungan (Single Chat).
     *
     * Aturan Kerja Bot:
     *  1. Jam Hening (Quiet Hours): 00:00 s/d 08:00 WIB spam berhenti dan mulai lagi pukul 08:00 WIB.
     *  2. Single Batched Message: Semua KD yang butuh perhatian digabung ke dalam 1 pesan WA tunggal (hemat kuota token).
     *  3. Cooldown Selaras (Mengikuti yang paling baru):
     *     - Jika ada KD yang baru ready, kirim 1 chat gabungan (menyertakan KD yang sedang menunggu).
     *     - Seluruh KD dalam chat tersebut disetel ulang waktu notifikasinya ke waktu kirim terbaru.
     *     - Spam pengingat berikutnya baru akan dikirim 30 menit (atau 15 menit overdue) dari waktu pesan terbaru!
     *  4. Anti-Concurrency Guard: Menahan spam beruntun dalam 90 detik dari tab browser atau cron simultan.
     */
    public static function evaluateAndNotifyKingdoms(?string $worker = null): array
    {
        $isAuto = BotSetting::getBoolean('is_auto_notify', true);
        if (!$isAuto) {
            return ['success' => true, 'auto_enabled' => false, 'notified_count' => 0, 'notified_kds' => []];
        }

        // 1. CEK JAM HENING (QUIET HOURS: 00:00 - 08:00 WIB)
        if (self::isQuietHours()) {
            return [
                'success' => true,
                'auto_enabled' => true,
                'quiet_hours' => true,
                'message' => 'Bot sedang dalam mode jam hening malam (00:00 - 08:00 WIB). Spam dinonaktifkan.',
                'notified_count' => 0,
                'notified_kds' => []
            ];
        }

        $phone = BotSetting::get('target_phone');
        $token = BotSetting::get('fonnte_token');
        if (empty($phone) || empty($token)) {
            return ['success' => false, 'message' => 'Token atau Nomor WA belum diset', 'notified_count' => 0, 'notified_kds' => []];
        }

        // Concurrency Guard: jangan kirim pesan jika pesan gabungan baru saja dikirim < 90 detik lalu
        $lastSentIso = BotSetting::get('bot_last_bundled_sent_at');
        if ($lastSentIso) {
            try {
                $lastSentTime = \Carbon\Carbon::parse($lastSentIso);
                if ($lastSentTime->greaterThan(now()->subSeconds(90))) {
                    return [
                        'success' => true,
                        'throttled' => true,
                        'message' => 'Notifikasi bot baru saja dikirim dalam 90 detik terakhir.',
                        'notified_count' => 0,
                        'notified_kds' => []
                    ];
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $groupedByKD = Character::with('gameAccount')->get()->groupBy('kd_number')->sortKeys();
        $kingdoms = Kingdom::all()->keyBy('kd_number');

        $spamUnrepliedInterval = (int) BotSetting::get('spam_unreplied_interval', 30);
        $maxWorkMinutes        = (int) BotSetting::get('max_work_minutes', 30);
        $spamOverdueInterval   = (int) BotSetting::get('spam_overdue_interval', 15);

        $unrepliedCandidates = [];
        $overdueCandidates   = [];

        $hasNewReadyTrigger   = false;
        $hasNewOverdueTrigger = false;

        foreach ($groupedByKD as $kd => $kChars) {
            // Cek apakah KD ini dimatikan/dijeda oleh admin (Mode Tidur KD)
            $isKdMuted = BotSetting::getBoolean("kd_mute_{$kd}", false);
            if ($isKdMuted) {
                continue;
            }

            $kingdom = $kingdoms->get($kd);
            $cooldownHours = $kingdom ? (float) $kingdom->cooldown_hours : 4.0;
            $cooldownMinutes = (int) round($cooldownHours * 60);

            // ANTI-SPAM GUARD: Jika KD ini baru saja dibalas dalam 5 menit terakhir, abaikan
            if ($kingdom && $kingdom->replied_at && $kingdom->replied_at->greaterThan(now()->subMinutes(5))) {
                continue;
            }

            $readyCount = 0;
            $latestFarmedAt = null;

            foreach ($kChars as $c) {
                if ($c->last_farmed_at) {
                    if ($latestFarmedAt === null || $c->last_farmed_at->greaterThan($latestFarmedAt)) {
                        $latestFarmedAt = $c->last_farmed_at;
                    }
                }
                if (!$c->last_farmed_at || now()->greaterThanOrEqualTo($c->last_farmed_at->copy()->addMinutes($cooldownMinutes))) {
                    $readyCount++;
                }
            }

            // KASUS 0: Semua char masih cooldown -- bersihkan state KD
            if ($readyCount === 0) {
                if ($kingdom && ($kingdom->replied_at || $kingdom->ready_since || ($kingdom->reminder_count ?? 0) > 0 || $kingdom->last_notified_at)) {
                    $kingdom->replied_at       = null;
                    $kingdom->replied_by       = null;
                    $kingdom->ready_since      = null;
                    $kingdom->reminder_count   = 0;
                    $kingdom->last_notified_at = null;
                    $kingdom->save();
                }
                continue;
            }

            // Pastikan record Kingdom ada
            if (!$kingdom) {
                $kingdom = Kingdom::firstOrCreate(['kd_number' => $kd]);
            }

            // Deteksi siklus farm baru: jika latestFarmedAt >= ready_since, char baru selesai panen & cooldown lagi
            $isNewCycle = $latestFarmedAt && (
                $kingdom->ready_since === null
                || $kingdom->ready_since->lte($latestFarmedAt)
            );

            if ($isNewCycle) {
                $kingdom->ready_since      = now();
                $kingdom->reminder_count   = 0;
                $kingdom->last_notified_at = null;
                $kingdom->replied_at       = null;
                $kingdom->replied_by       = null;
                $kingdom->save();
            } elseif ($kingdom->ready_since === null) {
                $kingdom->ready_since    = now();
                $kingdom->reminder_count = 0;
                $kingdom->save();
            }

            $lastNotified = $kingdom->last_notified_at;
            $repliedAt    = $kingdom->replied_at;

            // KASUS A: Belum dibalas (replied_at === null)
            if ($repliedAt === null) {
                $minsSinceReady = $kingdom->ready_since ? (int) round(now()->diffInMinutes($kingdom->ready_since, true)) : 0;
                $minsSinceLastNotif = $lastNotified ? (int) round(now()->diffInMinutes($lastNotified, true)) : null;

                $isNewReady = ($lastNotified === null);
                if ($isNewReady) {
                    $hasNewReadyTrigger = true;
                }

                $unrepliedCandidates[] = [
                    'category'              => 'unreplied',
                    'kd'                    => $kd,
                    'ready_count'           => $readyCount,
                    'total_chars'           => $kChars->count(),
                    'cooldown_hours'        => $cooldownHours,
                    'kingdom'               => $kingdom,
                    'mins_since_ready'      => $minsSinceReady,
                    'mins_since_last_notif' => $minsSinceLastNotif,
                    'is_new_ready'          => $isNewReady,
                    'reminder_count'        => (int) ($kingdom->reminder_count ?? 0),
                ];
            }
            // KASUS B: Sudah dibalas (replied_at !== null)
            else {
                $minsSinceReply = (int) round(now()->diffInMinutes($repliedAt, true));

                // Jika sudah melewati batas toleransi waktu pengerjaan -> OVERDUE!
                if ($minsSinceReply >= $maxWorkMinutes) {
                    $minsOverdue = $minsSinceReply - $maxWorkMinutes;
                    $minsSinceLastNotif = $lastNotified ? (int) round(now()->diffInMinutes($lastNotified, true)) : null;

                    // Waktu mulai overdue = repliedAt + maxWorkMinutes
                    $overdueStartTime = $repliedAt->copy()->addMinutes($maxWorkMinutes);
                    $isNewOverdue = ($lastNotified === null || $lastNotified->lt($overdueStartTime));

                    if ($isNewOverdue) {
                        $hasNewOverdueTrigger = true;
                    }

                    $overdueCandidates[] = [
                        'category'              => 'overdue',
                        'kd'                    => $kd,
                        'ready_count'           => $readyCount,
                        'total_chars'           => $kChars->count(),
                        'cooldown_hours'        => $cooldownHours,
                        'kingdom'               => $kingdom,
                        'mins_since_reply'      => $minsSinceReply,
                        'mins_overdue'          => $minsOverdue,
                        'replied_by'            => $kingdom->replied_by ?: 'Anggota Tim',
                        'mins_since_last_notif' => $minsSinceLastNotif,
                        'is_new_overdue'        => $isNewOverdue,
                        'reminder_count'        => (int) ($kingdom->reminder_count ?? 0),
                    ];
                }
            }
        }

        // EVALUASI APAKAH SAATNYA MENGIRIM PESAN GABUNGAN:
        // 1. Untuk Unreplied:
        //    - Ada yang baru ready ($hasNewReadyTrigger = true), ATAU
        //    - Jeda dari pengingat terbaru sudah mencapai $spamUnrepliedInterval (misal 30 menit)
        $unrepliedDue = false;
        if (!empty($unrepliedCandidates)) {
            if ($hasNewReadyTrigger) {
                $unrepliedDue = true;
            } else {
                $minUnrepliedElapsed = 9999;
                foreach ($unrepliedCandidates as $c) {
                    if ($c['mins_since_last_notif'] !== null && $c['mins_since_last_notif'] < $minUnrepliedElapsed) {
                        $minUnrepliedElapsed = $c['mins_since_last_notif'];
                    }
                }
                if ($minUnrepliedElapsed >= max(1, $spamUnrepliedInterval - 1)) {
                    $unrepliedDue = true;
                }
            }
        }

        // 2. Untuk Overdue:
        //    - Ada yang baru overdue ($hasNewOverdueTrigger = true), ATAU
        //    - Jeda dari pengingat terbaru sudah mencapai $spamOverdueInterval (misal 15 menit)
        $overdueDue = false;
        if (!empty($overdueCandidates)) {
            if ($hasNewOverdueTrigger) {
                $overdueDue = true;
            } else {
                $minOverdueElapsed = 9999;
                foreach ($overdueCandidates as $c) {
                    if ($c['mins_since_last_notif'] !== null && $c['mins_since_last_notif'] < $minOverdueElapsed) {
                        $minOverdueElapsed = $c['mins_since_last_notif'];
                    }
                }
                if ($minOverdueElapsed >= max(1, $spamOverdueInterval - 1)) {
                    $overdueDue = true;
                }
            }
        }

        // Jika tidak ada yang jatuh tempo kirim, keluar tanpa kirim apa-apa
        if (!$unrepliedDue && !$overdueDue) {
            return [
                'success'         => true,
                'auto_enabled'    => true,
                'notified_count'  => 0,
                'notified_kds'    => [],
                'unreplied_count' => count($unrepliedCandidates),
                'overdue_count'   => count($overdueCandidates),
            ];
        }

        // Urutkan kandidat: yang baru ready / baru overdue di atas agar langsung diperhatikan joki
        usort($unrepliedCandidates, function ($a, $b) {
            if ($a['is_new_ready'] !== $b['is_new_ready']) {
                return $b['is_new_ready'] <=> $a['is_new_ready'];
            }
            return $b['mins_since_ready'] <=> $a['mins_since_ready'];
        });

        usort($overdueCandidates, function ($a, $b) {
            if ($a['is_new_overdue'] !== $b['is_new_overdue']) {
                return $b['is_new_overdue'] <=> $a['is_new_overdue'];
            }
            return $b['mins_overdue'] <=> $a['mins_overdue'];
        });

        // Bangun 1 pesan WA gabungan tunggal
        $bundledMsg = self::buildBundledNotificationText(
            $unrepliedCandidates,
            $overdueCandidates,
            $spamUnrepliedInterval,
            $spamOverdueInterval,
            $maxWorkMinutes,
            $worker
        );

        // Kirim HANYA 1 CHAT ke WhatsApp via Fonnte
        $res = FonnteService::sendMessage($phone, $bundledMsg, $token);

        if (!$res['success']) {
            $errReason = $res['message'] ?? 'Gagal mengirim pesan via Fonnte';
            Log::warning("Bot Auto-Notify bundled message failed: {$errReason}");
            $deviceHealth = FonnteService::checkDeviceStatus($token);
            return [
                'success'        => false,
                'message'        => $errReason,
                'device_health'  => $deviceHealth,
                'notified_count' => 0,
                'notified_kds'   => [],
            ];
        }

        // Berhasil terkirim!
        // Perbarui last_notified_at & reminder_count untuk SEMUA KD yang masuk dalam chat gabungan ini
        // Sehingga CD spam pengingat berikutnya otomatis mengikuti waktu paling baru!
        $notifiedKds = [];
        $now = now();

        $allIncluded = array_merge($unrepliedCandidates, $overdueCandidates);
        foreach ($allIncluded as $c) {
            $kdModel = $c['kingdom'];
            $kdModel->last_notified_at = $now;
            $kdModel->reminder_count   = ($kdModel->reminder_count ?? 0) + 1;
            $kdModel->save();

            $isUnreplied = ($c['category'] === 'unreplied');
            $notifiedKds[] = [
                'kd'             => $c['kd'],
                'ready_count'    => $c['ready_count'],
                'type'           => $isUnreplied ? ($c['is_new_ready'] ? 'initial_ready' : 'spam_unreplied') : ($c['is_new_overdue'] ? 'initial_overdue' : 'spam_overdue'),
                'reminder_count' => $kdModel->reminder_count,
                'delay_minutes'  => $isUnreplied ? $c['mins_since_ready'] : $c['mins_overdue'],
            ];
        }

        BotSetting::set('bot_last_bundled_sent_at', $now->toIso8601String());

        return [
            'success'        => true,
            'auto_enabled'   => true,
            'bundled'        => true,
            'message'        => 'Notifikasi gabungan 1 chat berhasil dikirim ke grup WhatsApp!',
            'notified_count' => count($notifiedKds),
            'notified_kds'   => $notifiedKds,
        ];
    }

    /**
     * Format pesan WhatsApp Tunggal (Bundled Single Chat) untuk semua KD yang butuh perhatian.
     * Menggabungkan KD Siap Panen (Belum Dibalas) dan KD Overdue dalam 1 pesan hemat token Fonnte.
     */
    protected static function buildBundledNotificationText(
        array $unrepliedList,
        array $overdueList,
        int $spamUnrepliedInterval,
        int $spamOverdueInterval,
        int $maxWorkMinutes,
        ?string $worker = null
    ): string {
        $nowStr = now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB';
        $currentUrl = self::resolveDashboardUrl();

        $hasUnreplied = !empty($unrepliedList);
        $hasOverdue   = !empty($overdueList);

        if ($hasUnreplied && $hasOverdue) {
            $text = "🚨 *PANGGILAN PANEN & PERINGATAN OVERDUE* 🚨\n";
        } elseif ($hasOverdue) {
            $text = "🔥 *PERINGATAN OVERDUE: BELUM DICEKLIS SELESAI* 🔥\n";
        } else {
            $text = "🚨 *PANGGILAN PANEN FARM RoK (AUTO BOT)* 🚨\n";
        }

        $text .= "━━━━━━━━━━━━━━━━━\n";
        $text .= "⏰ *Waktu:* {$nowStr}\n\n";

        // 1. Bagian KD Siap Panen (Belum Dibalas)
        if ($hasUnreplied) {
            $countU = count($unrepliedList);
            $text .= "🟢 *SIAP PANEN (BELUM DIBALAS) — {$countU} KD:*\n";

            foreach ($unrepliedList as $item) {
                $kd = $item['kd'];
                $readyCount = $item['ready_count'];
                $cdHours = $item['cooldown_hours'];
                $cdStr = ((float)$cdHours == (int)$cdHours) ? (int)$cdHours : $cdHours;
                $minsReady = $item['mins_since_ready'];
                $isNew = $item['is_new_ready'];

                if ($isNew || $minsReady <= 2) {
                    $statusStr = "✨ Baru Saja Siap";
                } else {
                    $statusStr = "⏳ Menunggu Balasan: {$minsReady} Menit";
                }

                $text .= "• *KD #{$kd}*: {$readyCount} Char Siap ({$statusStr})\n";
            }
            $text .= "\n";
        }

        // 2. Bagian KD Overdue (Lewat batas waktu pengerjaan)
        if ($hasOverdue) {
            $countO = count($overdueList);
            $text .= "🔥 *OVERDUE (> {$maxWorkMinutes}m BELUM SELESAI) — {$countO} KD:*\n";

            foreach ($overdueList as $item) {
                $kd = $item['kd'];
                $readyCount = $item['ready_count'];
                $minsOverdue = $item['mins_overdue'];
                $repliedBy = $item['replied_by'] ?: 'Anggota Tim';
                $isNew = $item['is_new_overdue'];

                if ($isNew || $minsOverdue <= 2) {
                    $statusStr = "⚠️ Baru Lewat Batas {$maxWorkMinutes} Menit";
                } else {
                    $statusStr = "🚨 Terlambat: {$minsOverdue} Menit";
                }

                $text .= "• *KD #{$kd}*: {$readyCount} Char ({$statusStr} | Dibalas {$repliedBy})\n";
            }
            $text .= "\n";
        }

        $text .= "━━━━━━━━━━━━━━━━━\n";
        $text .= "🔗 *Buka Dashboard:* {$currentUrl}\n";

        if ($hasUnreplied && $hasOverdue) {
            $text .= "💬 _Ketik balasan (misal: *otw*) untuk KD siap panen, dan segera ceklis selesai di web untuk KD overdue!_ 🌾🚜\n";
        } elseif ($hasOverdue) {
            $text .= "💬 _Segera ceklis selesai panen di dashboard agar bot berhenti spam tiap {$spamOverdueInterval} menit!_ 🚜🌾\n";
        } else {
            $firstKd = $unrepliedList[0]['kd'] ?? '';
            $text .= "💬 _Ketik balasan di WA (contoh: *otw* atau *otw {$firstKd}*) agar bot tidak spam tiap {$spamUnrepliedInterval} menit!_ 🌾🚜\n";
        }

        $text .= "@all";

        return $text;
    }

    // Format pesan: Notif Siap Panen Pertama atau Spam jika belum dibalas
    protected static function buildReadyOrSpam5Text($kd, $readyCount, $totalChars, $cooldownHours, $kingdom, $isSpam, $worker, $spamInterval = 15)
    {
        $currentUrl = self::resolveDashboardUrl();
        $cdStr = ((float)$cooldownHours == (int)$cooldownHours) ? (int)$cooldownHours : $cooldownHours;

        if ($isSpam) {
            $minsSinceReady = $kingdom->ready_since ? abs((int) round(now()->diffInMinutes($kingdom->ready_since))) : $spamInterval;
            $count = ($kingdom->reminder_count ?? 0) + 1;

            $text = "⚠️ *SPAM PENGINGAT #{$count} (BELUM ADA BALASAN)* ⚠️\n";
            $text .= "━━━━━━━━━━━━━━━━━\n";
            $text .= "🏰 *Kingdom:* KD #{$kd}\n";
            $text .= "⏳ *Status:* SUDAH SIAP PANEN!\n";
            $text .= "🚨 *Keterangan:* Sudah {$minsSinceReady} menit siap panen, tapi *BELUM ADA YANG MEMBALAS WA*!\n";
            $text .= "👥 *Total Char:* {$readyCount} Char Siap Macul\n";
            $text .= "⏱️ *Siklus:* Tiap {$cdStr} Jam\n";
            $text .= "@all\n";
            $text .= "━━━━━━━━━━━━━━━━━\n";
            $text .= "🔗 *Buka Dashboard:* {$currentUrl}\n";
            $text .= "💬 _Ketik balasan di WA (contoh: 'otw' / 'siap') untuk mematikan spam {$spamInterval} menit ini!_ ⏰";
        } else {
            $text = "🚨 *PANGGILAN PANEN FARM RoK (AUTO BOT)* 🚨\n";
            $text .= "━━━━━━━━━━━━━━━━━\n";
            $text .= "🏰 *Kingdom:* KD #{$kd}\n";
            $text .= "✅ *Status:* SUDAH SIAP PANEN!\n";
            $text .= "👥 *Total Char:* {$readyCount} Char Siap Panen\n";
            $text .= "⏱️ *Siklus:* Tiap {$cdStr} Jam\n";
            $text .= "@all\n";
            $text .= "━━━━━━━━━━━━━━━━━\n";
            $text .= "🔗 *Buka Dashboard:* {$currentUrl}\n";
            $text .= "💬 _Balas chat ini di grup (misal: 'otw') agar bot tidak spam tiap {$spamInterval} menit!_ 🌾🚜";
        }

        return $text;
    }

    // Format pesan: Spam Overdue (Sudah dibalas tapi > maxWorkMinutes belum diceklis selesai)
    protected static function buildOverdueSpam5Text($kd, $readyCount, $totalChars, $cooldownHours, $kingdom, $minsSinceReply, $maxWorkMinutes = 30, $spamOverdueInterval = 5)
    {
        $currentUrl = self::resolveDashboardUrl();
        $cdStr = ((float)$cooldownHours == (int)$cooldownHours) ? (int)$cooldownHours : $cooldownHours;
        $repliedBy = $kingdom->replied_by ?: 'Anggota Tim';
        $count = ($kingdom->reminder_count ?? 0) + 1;

        $text = "🔥 *PERINGATAN URGENT: BELUM SELESAI DICEKLIS! #{$count}* 🔥\n";
        $text .= "━━━━━━━━━━━━━━━━━\n";
        $text .= "🏰 *Kingdom:* KD #{$kd}\n";
        $text .= "⚠️ *Status:* OVERDUE (> {$maxWorkMinutes} Menit)!\n";
        $text .= "👤 *Dibalas Oleh:* {$repliedBy} ({$minsSinceReply} menit lalu)\n";
        $text .= "🚨 *Keterangan:* Sudah di-reply {$maxWorkMinutes} menit lalu tapi *BELUM DIKELARIN / DICEKLIS* di web!\n";
        $text .= "👥 *Sisa Char Belum Panen:* {$readyCount} Char\n";
        $text .= "⏱️ *Siklus:* Tiap {$cdStr} Jam\n";
        $text .= "@all\n";
        $text .= "━━━━━━━━━━━━━━━━━\n";
        $text .= "🔗 *Buka & Ceklis di Web:* {$currentUrl}\n";
        $text .= "_Bot akan spam setiap {$spamOverdueInterval} menit sampai KD ini dikelarin / diceklis selesai panen!_ 🌾🚜";

        return $text;
    }

    // Helper format teks pesan 1 KD manual
    protected function buildSingleKdText($kd, $readyCount, $totalChars, $cooldownHours, $maxCooldownEnd, $worker)
    {
        $currentUrl = $this->getDashboardUrl();
        $cdStr = ((float)$cooldownHours == (int)$cooldownHours) ? (int)$cooldownHours : $cooldownHours;
        $spamUnrepliedInterval = (int) BotSetting::get('spam_unreplied_interval', 30);

        if ($readyCount > 0 || !$maxCooldownEnd || now()->greaterThanOrEqualTo($maxCooldownEnd)) {
            $text = "🚨 *PANGGILAN PANEN FARM RoK (AUTO BOT)* 🚨\n";
            $text .= "━━━━━━━━━━━━━━━━━\n";
            $text .= "🏰 *Kingdom:* KD #{$kd}\n";
            $text .= "✅ *Status:* SUDAH SIAP PANEN!\n";
            $text .= "👥 *Total Char:* {$readyCount} Char Siap\n";
            $text .= "⏱️ *Siklus:* Tiap {$cdStr} Jam\n";
            $text .= "@all\n";
            $text .= "━━━━━━━━━━━━━━━━━\n";
            $text .= "🔗 *Buka Dashboard:* {$currentUrl}\n";
            $text .= "💬 _Balas chat ini di grup (misal: 'otw') agar bot tidak spam tiap {$spamUnrepliedInterval} menit!_ 🌾🚜";
        } else {
            $sisaMenit = abs((int) round(now()->diffInMinutes($maxCooldownEnd)));
            $jam = floor($sisaMenit / 60);
            $menit = $sisaMenit % 60;
            $sisaStr = ($jam > 0 ? "{$jam} Jam " : "") . "{$menit} Menit";
            $jamSelesai = $maxCooldownEnd->copy()->setTimezone('Asia/Jakarta')->format('H:i') . ' WIB';

            $text = "🔔 *PENGINGAT JADWAL FARM RoK* 🔔\n";
            $text .= "━━━━━━━━━━━━━━━━━\n";
            $text .= "🏰 *Kingdom:* KD #{$kd}\n";
            $text .= "⏳ *Status:* Sedang Masa Jeda (Cooldown)\n";
            $text .= "⏱️ *Sisa Waktu:* {$sisaStr} lagi\n";
            $text .= "🎯 *Estimasi Siap:* pk {$jamSelesai}\n";
            $text .= "👥 *Target Char:* {$totalChars} Char\n";
            $text .= "⏱️ *Siklus:* Tiap {$cdStr} Jam\n";
            $text .= "@all\n";
            $text .= "━━━━━━━━━━━━━━━━━\n";
            $text .= "🔗 *Buka Dashboard:* {$currentUrl}\n";
            $text .= "_Pesan dari Bot RoK Joki. Set alarm jam {$jamSelesai}!_ ⏰";
        }

        return $text;
    }

    // Helper format teks pesan Semua KD
    protected function buildAllKdsText($worker)
    {
        $nowStr = now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB';
        $currentUrl = $this->getDashboardUrl();

        $groupedByKD = Character::with('gameAccount')->get()->groupBy('kd_number')->sortKeys();
        $kingdoms = Kingdom::all()->keyBy('kd_number');

        $readyList = [];
        $cooldownList = [];

        foreach ($groupedByKD as $kNum => $kChars) {
            $kdCdHours = isset($kingdoms[$kNum]) ? (float)$kingdoms[$kNum]->cooldown_hours : 4.0;
            $kdCdMinutes = (int) round($kdCdHours * 60);
            $rCount = 0;
            $maxEnd = null;

            foreach ($kChars as $kc) {
                if (!$kc->last_farmed_at || now()->greaterThanOrEqualTo($kc->last_farmed_at->copy()->addMinutes($kdCdMinutes))) {
                    $rCount++;
                } else {
                    $e = $kc->last_farmed_at->copy()->addMinutes($kdCdMinutes);
                    if ($maxEnd === null || $e->greaterThan($maxEnd)) {
                        $maxEnd = $e;
                    }
                }
            }

            if ($rCount > 0 || !$maxEnd) {
                $readyList[] = "• *KD #{$kNum}*: " . ($rCount ?: $kChars->count()) . " Char Siap Panen!";
            } else {
                $sisaM = abs((int) round(now()->diffInMinutes($maxEnd)));
                $j = floor($sisaM / 60);
                $m = $sisaM % 60;
                $sStr = ($j > 0 ? "{$j}j " : "") . "{$m}m";
                $cooldownList[] = "• *KD #{$kNum}*: Tunggu {$sStr} (pk " . $maxEnd->copy()->setTimezone('Asia/Jakarta')->format('H:i') . " WIB)";
            }
        }

        $text = "📋 *REKAP PENGINGAT JADWAL FARM RoK (AUTO BOT)* 📋\n";
        $text .= "🗓️ *Waktu:* {$nowStr}\n";
        $text .= "━━━━━━━━━━━━━━━━━\n\n";

        if (!empty($readyList)) {
            $text .= "🟢 *SIAP PANEN (READY - " . count($readyList) . " KD):*\n";
            $text .= implode("\n", $readyList) . "\n\n";
        } else {
            $text .= "🟢 *SIAP PANEN:* (Semua KD sedang masa jeda)\n\n";
        }

        if (!empty($cooldownList)) {
            $text .= "⏳ *SEDANG JEDA (COOLDOWN - " . count($cooldownList) . " KD):*\n";
            $text .= implode("\n", $cooldownList) . "\n\n";
        }

        $text .= "@all\n";
        $text .= "━━━━━━━━━━━━━━━━━\n";
        $text .= "🔗 *Buka Dashboard:* {$currentUrl}\n";
        $text .= "_Kirim otomatis oleh Bot RoK Joki Fonnte!_ 🌾🚜";

        return $text;
    }

    // Helper ambil URL dashboard publik yang bisa diakses langsung via WA
    protected function getDashboardUrl(): string
    {
        return self::resolveDashboardUrl();
    }

    public static function resolveDashboardUrl(): string
    {
        if (request() && request()->getHttpHost() && !in_array(request()->getHost(), ['localhost', '127.0.0.1'])) {
            return request()->getSchemeAndHttpHost() . '/jadwal';
        }
        $appUrl = env('APP_URL') ?: config('app.url');
        if ($appUrl && !str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
            return rtrim($appUrl, '/') . '/jadwal';
        }
        return 'https://rok-db.vercel.app/jadwal';
    }

    // Helper ambil URL Webhook Fonnte publik
    protected function getWebhookUrl(): string
    {
        if (request() && request()->getHttpHost() && !in_array(request()->getHost(), ['localhost', '127.0.0.1'])) {
            return request()->getSchemeAndHttpHost() . '/api/fonnte/webhook';
        }
        $appUrl = env('APP_URL') ?: config('app.url');
        if ($appUrl && !str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
            return rtrim($appUrl, '/') . '/api/fonnte/webhook';
        }
        return 'https://rok-db.vercel.app/api/fonnte/webhook';
    }
}
