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
    // Ambil setting bot
    /**
     * Guard: pastikan yang akses adalah admin
     */
    private function requireAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, '❌ Akses ditolak! Fitur ini hanya untuk Admin.');
        }
    }

    // Ambil setting bot
    public function getSettings()
    {
        return response()->json([
            'success' => true,
            'fonnte_token' => BotSetting::get('fonnte_token', ''),
            'target_phone' => BotSetting::get('target_phone', ''),
            'is_auto_notify' => (bool) BotSetting::get('is_auto_notify', true),
            'spam_unreplied_interval' => (int) BotSetting::get('spam_unreplied_interval', 15),
            'max_work_minutes' => (int) BotSetting::get('max_work_minutes', 30),
            'spam_overdue_interval' => (int) BotSetting::get('spam_overdue_interval', 5),
            'webhook_url' => $this->getWebhookUrl(),
            'is_admin' => auth()->check() && auth()->user()->isAdmin(),
        ]);
    }

    // Toggle Cepat Matikan / Aktifkan Bot CD (Mode Tidur) - Admin Only
    public function toggleAutoNotify(Request $request)
    {
        $this->requireAdmin();

        $current = (bool) BotSetting::get('is_auto_notify', true);
        $newStatus = !$current;
        BotSetting::set('is_auto_notify', $newStatus ? '1' : '0');

        $adminName = auth()->user()->name ?? 'Admin';
        $statusText = $newStatus ? 'DIAKTIFKAN KEMBALI' : 'DINONAKTIFKAN (Mode Tidur)';
        $msg = $newStatus
            ? "🔔 Bot Pengingat CD berhasil diaktifkan kembali oleh Admin {$adminName}!"
            : "😴 Bot Pengingat CD dimatikan (Mode Tidur) oleh Admin {$adminName}! Pengingat & spam dinonaktifkan.";

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

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan Bot WhatsApp Fonnte berhasil disimpan!',
            'settings' => [
                'fonnte_token' => BotSetting::get('fonnte_token', ''),
                'target_phone' => BotSetting::get('target_phone', ''),
                'is_auto_notify' => (bool) BotSetting::get('is_auto_notify', true),
                'spam_unreplied_interval' => (int) BotSetting::get('spam_unreplied_interval', 15),
                'max_work_minutes' => (int) BotSetting::get('max_work_minutes', 30),
                'spam_overdue_interval' => (int) BotSetting::get('spam_overdue_interval', 5),
                'webhook_url' => $this->getWebhookUrl(),
            ]
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
        $testMsg .= "✅ Sistem Bot Fonnte berhasil terhubung & siap mengirim pengingat panen otomatis serta sistem anti-lupa (spam 5m)!\n";
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
        $spamOverdueInterval = (int) BotSetting::get('spam_overdue_interval', 5);

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

        $sender = (string) ($payload['sender'] ?? $request->input('sender', ''));
        $member = (string) ($payload['member'] ?? $request->input('member', ''));
        $name = (string) ($payload['name'] ?? ($request->input('name') ?: 'Anggota Grup'));
        $message = trim((string) ($payload['message'] ?? $request->input('message', '')));
        $device = (string) ($payload['device'] ?? $request->input('device', ''));

        // Bersihkan angka untuk deteksi nomor bot sendiri
        $cleanDevice = preg_replace('/[^0-9]/', '', $device);
        $cleanMember = preg_replace('/[^0-9]/', '', $member);
        $cleanSender = preg_replace('/[^0-9]/', '', $sender);

        // Abaikan jika pesan dikirim oleh nomor bot sendiri (agar tidak looping self-reply)
        if (!empty($cleanDevice) && (($cleanMember && $cleanMember === $cleanDevice) || ($cleanSender && $cleanSender === $cleanDevice))) {
            return response()->json(['status' => true, 'message' => 'Abaikan pesan sendiri dari bot']);
        }

        $targetPhone = (string) BotSetting::get('target_phone', '');
        if (!empty($targetPhone)) {
            $cleanTarget = preg_replace('/[^0-9]/', '', $targetPhone);

            // Validasi apakah pesan berasal dari grup target atau member grup target
            $isTargetGroup = ($cleanSender && $cleanTarget && $cleanSender === $cleanTarget)
                || str_contains($targetPhone, $sender)
                || (!empty($cleanSender) && str_contains($sender, $cleanTarget))
                || (!empty($cleanTarget) && str_contains($cleanSender, $cleanTarget));
            $isTargetDirect = ($member && str_contains($targetPhone, $member))
                || (!empty($cleanMember) && !empty($cleanTarget) && str_contains($cleanTarget, $cleanMember));
            $isSenderDirect = ($sender === $targetPhone)
                || (!empty($cleanSender) && !empty($cleanTarget) && $cleanSender === $cleanTarget);

            if (!$isTargetGroup && !$isTargetDirect && !$isSenderDirect) {
                return response()->json(['status' => true, 'message' => 'Pesan dari luar target diabaikan']);
            }
        }

        // Cek apakah pesan menyebut KD spesifik (contoh: "kd 2359 otw", "2359 siap", "#2359")
        $targetKd = null;
        if (preg_match('/(?:kd\s*#?|#)\b(\d{3,5})\b/i', $message, $m)) {
            $targetKd = (int) $m[1];
        }

        // Cari KD yang saat ini berstatus Siap Panen (readyCount > 0)
        $groupedByKD = Character::with('gameAccount')->get()->groupBy('kd_number')->sortKeys();
        $kingdoms = Kingdom::all()->keyBy('kd_number');

        $acknowledgedKds = [];

        foreach ($groupedByKD as $kd => $kChars) {
            if ($targetKd !== null && (int)$kd !== $targetKd) {
                continue;
            }

            $kingdom = $kingdoms->get($kd);
            $cooldownHours = $kingdom ? (float) $kingdom->cooldown_hours : 4.0;
            $cooldownMinutes = (int) round($cooldownHours * 60);

            $readyCount = 0;
            foreach ($kChars as $c) {
                if (!$c->last_farmed_at || now()->greaterThanOrEqualTo($c->last_farmed_at->copy()->addMinutes($cooldownMinutes))) {
                    $readyCount++;
                }
            }

            if ($readyCount > 0) {
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
        }

        // Jika ada KD yang diakui balasan, kirim balasan otomatis ke grup
        if (!empty($acknowledgedKds)) {
            $maxWorkMinutes = (int) BotSetting::get('max_work_minutes', 30);
            $spamOverdueInterval = (int) BotSetting::get('spam_overdue_interval', 5);

            $kdListStr = implode(', ', array_map(fn($item) => "KD #{$item['kd']}", $acknowledgedKds));
            $replyMsg = "👍 *BALASAN DITERIMA DARI {$name}!* 🎯\n";
            $replyMsg .= "━━━━━━━━━━━━━━━━━\n";
            $replyMsg .= "🏰 *Target:* {$kdListStr}\n";
            $replyMsg .= "⏳ *Status:* Ditandai sedang dikerjakan!\n";
            $replyMsg .= "⏱️ *Tenggat Waktu:* {$maxWorkMinutes} Menit dari sekarang.\n";
            $replyMsg .= "━━━━━━━━━━━━━━━━━\n";
            $replyMsg .= "🔗 *Buka & Ceklis di Web:* " . $this->getDashboardUrl() . "\n";
            $replyMsg .= "_Jika dalam {$maxWorkMinutes} menit belum selesai/diceklis di web, bot akan spam tiap {$spamOverdueInterval} menit. Semangat macul!_ 🌾🚜";

            $phoneToSend = $targetPhone ?: $sender;
            FonnteService::sendMessage($phoneToSend, $replyMsg);
        }

        return response()->json([
            'status' => true,
            'message' => 'Webhook berhasil diproses',
            'acknowledged_kds' => $acknowledgedKds,
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
     * Core Engine: Mengevaluasi status semua KD dan mengirim notifikasi / spam
     *
     * Alur yang benar:
     *  1. KD siap panen dan last_notified_at null -> kirim notif awal SEGERA
     *  2. Belum dibalas dan >= 4-5 menit sejak notif terakhir -> spam 5 menit
     *  3. Sudah dibalas dan >= 30 menit belum diceklis dan >= 5 menit sejak notif terakhir -> spam 5 menit
     *  4. Guard 2 menit: cegah double-fire dari concurrent heartbeat browser
     *  5. Deteksi siklus baru: jika char baru saja di-farm, reset state notifikasi
     */
    public static function evaluateAndNotifyKingdoms(?string $worker = null): array
    {
        $isAuto = (bool) BotSetting::get('is_auto_notify', true);
        if (!$isAuto) {
            return ['success' => true, 'auto_enabled' => false, 'notified_count' => 0, 'notified_kds' => []];
        }

        $phone = BotSetting::get('target_phone');
        $token = BotSetting::get('fonnte_token');
        if (empty($phone) || empty($token)) {
            return ['success' => false, 'message' => 'Token atau Nomor WA belum diset', 'notified_count' => 0, 'notified_kds' => []];
        }

        $groupedByKD = Character::with('gameAccount')->get()->groupBy('kd_number')->sortKeys();
        $kingdoms = Kingdom::all()->keyBy('kd_number');

        $spamUnrepliedInterval = (int) BotSetting::get('spam_unreplied_interval', 15);
        $maxWorkMinutes        = (int) BotSetting::get('max_work_minutes', 30);
        $spamOverdueInterval   = (int) BotSetting::get('spam_overdue_interval', 5);

        $notifiedKds = [];

        foreach ($groupedByKD as $kd => $kChars) {
            // Cek apakah KD ini secara spesifik dimatikan/dijeda oleh admin (Mode Tidur KD)
            $isKdMuted = (bool) BotSetting::get("kd_mute_{$kd}", false);
            if ($isKdMuted) {
                continue;
            }

            $kingdom = $kingdoms->get($kd);
            $cooldownHours = $kingdom ? (float) $kingdom->cooldown_hours : 4.0;
            $cooldownMinutes = (int) round($cooldownHours * 60);

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
                if ($kingdom && ($kingdom->replied_at || $kingdom->ready_since || ($kingdom->reminder_count ?? 0) > 0)) {
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

            // Deteksi siklus farm baru:
            // Jika latestFarmedAt >= ready_since, char baru saja selesai cooldown setelah di-farm
            // -> ini siklus notifikasi baru, reset semua state notifikasi
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
                // Char belum pernah di-farm sebelumnya
                $kingdom->ready_since    = now();
                $kingdom->reminder_count = 0;
                $kingdom->save();
            }

            $lastNotified = $kingdom->last_notified_at;
            $repliedAt    = $kingdom->replied_at;

            // Guard: cegah double-fire dalam 2 menit (dari concurrent heartbeat browser multiple tab)
            if ($lastNotified && $lastNotified->gte(now()->subMinutes(2))) {
                continue;
            }

            // KASUS 1: Belum dibalas (replied_at == null)
            // last_notified null -> notif awal SEGERA
            // last_notified ada dan >= ($spamUnrepliedInterval - 1) menit -> spam unreplied
            if ($repliedAt === null) {
                $shouldSend = false;
                $isSpam     = false;

                if ($lastNotified === null) {
                    // Notifikasi pertama -- kirim segera
                    $shouldSend = true;
                    $isSpam     = false;
                } else {
                    $minsElapsed = (int) now()->diffInMinutes($lastNotified, true);
                    if ($minsElapsed >= max(1, $spamUnrepliedInterval - 1)) {
                        $shouldSend = true;
                        $isSpam     = true;
                    }
                }

                if ($shouldSend) {
                    $msg = self::buildReadyOrSpam5Text($kd, $readyCount, $kChars->count(), $cooldownHours, $kingdom, $isSpam, $worker, $spamUnrepliedInterval);
                    $res = FonnteService::sendMessage($phone, $msg, $token);

                    if ($res['success']) {
                        $kingdom->last_notified_at = now();
                        $kingdom->reminder_count   = ($kingdom->reminder_count ?? 0) + 1;
                        $kingdom->save();

                        $notifiedKds[] = [
                            'kd'             => $kd,
                            'ready_count'    => $readyCount,
                            'type'           => $isSpam ? "spam_{$spamUnrepliedInterval}m_unreplied" : 'initial_ready',
                            'reminder_count' => $kingdom->reminder_count,
                        ];
                    }
                }
            }
            // KASUS 2: Sudah dibalas (replied_at != null)
            // Diberi jeda $maxWorkMinutes. Lewat $maxWorkMinutes dan >= $spamOverdueInterval menit sejak notif terakhir -> spam
            else {
                $minsSinceReply = (int) now()->diffInMinutes($repliedAt, true);

                if ($minsSinceReply >= $maxWorkMinutes) {
                    $diffSinceLastNotif = $lastNotified
                        ? (int) now()->diffInMinutes($lastNotified, true)
                        : 999;

                    // Spam tiap $spamOverdueInterval menit selama belum dikelarin
                    if ($diffSinceLastNotif >= max(1, $spamOverdueInterval - 1)) {
                        $msg = self::buildOverdueSpam5Text($kd, $readyCount, $kChars->count(), $cooldownHours, $kingdom, $minsSinceReply, $maxWorkMinutes, $spamOverdueInterval);
                        $res = FonnteService::sendMessage($phone, $msg, $token);

                        if ($res['success']) {
                            $kingdom->last_notified_at = now();
                            $kingdom->reminder_count   = ($kingdom->reminder_count ?? 0) + 1;
                            $kingdom->save();

                            $notifiedKds[] = [
                                'kd'             => $kd,
                                'ready_count'    => $readyCount,
                                'type'           => "spam_{$spamOverdueInterval}m_overdue",
                                'reminder_count' => $kingdom->reminder_count,
                            ];
                        }
                    }
                }
                // Sudah dibalas tapi belum lewat $maxWorkMinutes: diam, tidak spam
            }
        }

        return [
            'success' => true,
            'auto_enabled' => true,
            'notified_count' => count($notifiedKds),
            'notified_kds' => $notifiedKds,
        ];
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
        $spamUnrepliedInterval = (int) BotSetting::get('spam_unreplied_interval', 15);

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
        return 'https://rokjoki.vercel.app/jadwal';
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
        return 'https://rokjoki.vercel.app/api/fonnte/webhook';
    }
}
