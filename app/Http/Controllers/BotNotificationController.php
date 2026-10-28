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
    public function getSettings()
    {
        return response()->json([
            'success' => true,
            'fonnte_token' => BotSetting::get('fonnte_token', ''),
            'target_phone' => BotSetting::get('target_phone', ''),
            'is_auto_notify' => (bool) BotSetting::get('is_auto_notify', true),
            'webhook_url' => $this->getWebhookUrl(),
        ]);
    }

    // Simpan setting bot
    public function saveSettings(Request $request)
    {
        $request->validate([
            'fonnte_token' => 'nullable|string',
            'target_phone' => 'nullable|string',
            'is_auto_notify' => 'nullable|boolean',
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

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan Bot WhatsApp Fonnte berhasil disimpan!',
            'settings' => [
                'fonnte_token' => BotSetting::get('fonnte_token', ''),
                'target_phone' => BotSetting::get('target_phone', ''),
                'is_auto_notify' => (bool) BotSetting::get('is_auto_notify', true),
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
        $testMsg .= "✅ Sistem Bot Fonnte berhasil terhubung & siap mengirim pengingat panen otomatis serta sistem anti-lupa (spam 10m / 5m)!\n";
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

        $targetPhone = BotSetting::get('target_phone');
        if (!empty($targetPhone)) {
            $msg = "👍 *INFO: KD #{$kd} SEDANG DIKERJAKAN!* 🎯\n";
            $msg .= "━━━━━━━━━━━━━━━━━\n";
            $msg .= "👤 *Operator:* {$worker} (via Web Dashboard)\n";
            $msg .= "⏱️ *Tenggat Waktu:* 30 Menit dari sekarang\n";
            $msg .= "━━━━━━━━━━━━━━━━━\n";
            $msg .= "🔗 *Buka Dashboard:* " . $this->getDashboardUrl() . "\n";
            $msg .= "_Spam pengingat 10 menit dijeda. Selesaikan panen sebelum spam 5 menit aktif!_ 🌾🚜";

            FonnteService::sendMessage($targetPhone, $msg);
        }

        return response()->json([
            'success' => true,
            'message' => "KD #{$kd} berhasil ditandai sedang dikerjakan oleh {$worker}! Tenggat 30 menit dimulai.",
            'replied_at' => $kingdom->replied_at->toIso8601String(),
            'replied_by' => $kingdom->replied_by,
        ]);
    }

    // Webhook Fonnte untuk menerima balasan chat dari member grup atau nomor user
    public function handleWebhook(Request $request)
    {
        Log::info('Fonnte Webhook Received: ' . json_encode($request->all()));

        $sender = (string) $request->input('sender', '');
        $member = (string) $request->input('member', '');
        $name = (string) ($request->input('name') ?: 'Anggota Grup');
        $message = trim((string) $request->input('message', ''));
        $device = (string) $request->input('device', '');

        // Abaikan jika pesan dikirim oleh nomor bot sendiri (agar tidak looping self-reply)
        if (($member && $member === $device) || ($sender && $sender === $device)) {
            return response()->json(['status' => true, 'message' => 'Abaikan pesan sendiri dari bot']);
        }

        $targetPhone = (string) BotSetting::get('target_phone', '');
        if (!empty($targetPhone)) {
            // Validasi apakah pesan berasal dari grup target atau member grup target
            $isTargetGroup = ($sender === $targetPhone);
            $isTargetDirect = ($member && str_contains($targetPhone, $member));
            $isSenderDirect = ($sender === $targetPhone);

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
            $kdListStr = implode(', ', array_map(fn($item) => "KD #{$item['kd']}", $acknowledgedKds));
            $replyMsg = "👍 *BALASAN DITERIMA DARI {$name}!* 🎯\n";
            $replyMsg .= "━━━━━━━━━━━━━━━━━\n";
            $replyMsg .= "🏰 *Target:* {$kdListStr}\n";
            $replyMsg .= "⏳ *Status:* Ditandai sedang dikerjakan!\n";
            $replyMsg .= "⏱️ *Tenggat Waktu:* 30 Menit dari sekarang.\n";
            $replyMsg .= "━━━━━━━━━━━━━━━━━\n";
            $replyMsg .= "🔗 *Buka & Ceklis di Web:* " . $this->getDashboardUrl() . "\n";
            $replyMsg .= "_Jika dalam 30 menit belum selesai/diceklis di web, bot akan spam tiap 5 menit. Semangat macul!_ 🌾🚜";

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
     * - Belum dibalas: spam tiap 10 menit
     * - Sudah dibalas tapi lewat 30 menit belum dikelarin/diceklis: spam tiap 5 menit
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

        $notifiedKds = [];

        foreach ($groupedByKD as $kd => $kChars) {
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

            // KASUS 0: SEMUA CHAR SEDANG COOLDOWN (Sudah beres dipanen)
            if ($readyCount === 0) {
                if ($kingdom && ($kingdom->replied_at || $kingdom->ready_since || ($kingdom->reminder_count ?? 0) > 0)) {
                    $kingdom->replied_at = null;
                    $kingdom->replied_by = null;
                    $kingdom->ready_since = null;
                    $kingdom->reminder_count = 0;
                    $kingdom->save();
                }
                continue;
            }

            // KASUS KD MEMILIKI CHAR SIAP PANEN
            if (!$kingdom) {
                $kingdom = Kingdom::firstOrCreate(['kd_number' => $kd]);
            }

            if (!$kingdom->ready_since) {
                $kingdom->ready_since = now();
                $kingdom->reminder_count = 0;
                $kingdom->save();
            }

            $lastNotified = $kingdom->last_notified_at;
            $repliedAt = $kingdom->replied_at;

            // KASUS 1: BELUM ADA YANG BALAS WA (replied_at == null)
            // Logika: Notifikasi pertama kali saat siap, lalu spam setiap 10 menit jika belum dibalas
            if ($repliedAt === null) {
                $shouldSend = false;
                $isSpam = false;

                if (!$lastNotified || ($latestFarmedAt && $lastNotified->lessThan($latestFarmedAt))) {
                    // Notifikasi awal siap panen
                    $shouldSend = true;
                    $isSpam = false;
                } else {
                    // Cek apakah sudah lewat 10 menit sejak notif/spam terakhir
                    $diffMinutes = abs((int) round(now()->diffInMinutes($lastNotified)));
                    if ($diffMinutes >= 10) {
                        $shouldSend = true;
                        $isSpam = true; // Spam 10 menit
                    }
                }

                if ($shouldSend) {
                    $msg = self::buildReadyOrSpam10Text($kd, $readyCount, $kChars->count(), $cooldownHours, $kingdom, $isSpam, $worker);
                    $res = FonnteService::sendMessage($phone, $msg, $token);

                    if ($res['success']) {
                        $kingdom->last_notified_at = now();
                        $kingdom->reminder_count = ($kingdom->reminder_count ?? 0) + 1;
                        $kingdom->save();

                        $notifiedKds[] = [
                            'kd' => $kd,
                            'ready_count' => $readyCount,
                            'type' => $isSpam ? 'spam_10m_unreplied' : 'initial_ready',
                            'reminder_count' => $kingdom->reminder_count,
                        ];
                    }
                }
            }
            // KASUS 2: SUDAH ADA YANG BALAS WA (replied_at != null)
            // Logika: Diberi jeda 30 menit. Jika > 30 menit belum dikelarin/diceklis di web, bot spam setiap 5 menit!
            else {
                $minsSinceReply = abs((int) round(now()->diffInMinutes($repliedAt)));

                if ($minsSinceReply >= 30) {
                    $diffSinceLastNotif = $lastNotified ? abs((int) round(now()->diffInMinutes($lastNotified))) : 999;

                    // Spam setiap 5 menit jika belum dikelarin
                    if ($diffSinceLastNotif >= 5) {
                        $msg = self::buildOverdueSpam5Text($kd, $readyCount, $kChars->count(), $cooldownHours, $kingdom, $minsSinceReply);
                        $res = FonnteService::sendMessage($phone, $msg, $token);

                        if ($res['success']) {
                            $kingdom->last_notified_at = now();
                            $kingdom->reminder_count = ($kingdom->reminder_count ?? 0) + 1;
                            $kingdom->save();

                            $notifiedKds[] = [
                                'kd' => $kd,
                                'ready_count' => $readyCount,
                                'type' => 'spam_5m_overdue',
                                'reminder_count' => $kingdom->reminder_count,
                            ];
                        }
                    }
                }
            }
        }

        return [
            'success' => true,
            'auto_enabled' => true,
            'notified_count' => count($notifiedKds),
            'notified_kds' => $notifiedKds,
        ];
    }

    // Format pesan: Notif Siap Panen Pertama atau Spam 10 Menit jika belum dibalas
    protected static function buildReadyOrSpam10Text($kd, $readyCount, $totalChars, $cooldownHours, $kingdom, $isSpam, $worker)
    {
        $currentUrl = self::resolveDashboardUrl();
        $cdStr = ((float)$cooldownHours == (int)$cooldownHours) ? (int)$cooldownHours : $cooldownHours;

        if ($isSpam) {
            $minsSinceReady = $kingdom->ready_since ? abs((int) round(now()->diffInMinutes($kingdom->ready_since))) : 10;
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
            $text .= "💬 _Ketik balasan di WA (contoh: 'otw' / 'siap') untuk mematikan spam 10 menit ini!_ ⏰";
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
            $text .= "💬 _Balas chat ini di grup (misal: 'otw') agar bot tidak spam tiap 10 menit!_ 🌾🚜";
        }

        return $text;
    }

    // Format pesan: Spam Overdue Tiap 5 Menit (Sudah dibalas tapi > 30 menit belum diceklis selesai)
    protected static function buildOverdueSpam5Text($kd, $readyCount, $totalChars, $cooldownHours, $kingdom, $minsSinceReply)
    {
        $currentUrl = self::resolveDashboardUrl();
        $cdStr = ((float)$cooldownHours == (int)$cooldownHours) ? (int)$cooldownHours : $cooldownHours;
        $repliedBy = $kingdom->replied_by ?: 'Anggota Tim';
        $count = ($kingdom->reminder_count ?? 0) + 1;

        $text = "🔥 *PERINGATAN URGENT: BELUM SELESAI DICEKLIS! #{$count}* 🔥\n";
        $text .= "━━━━━━━━━━━━━━━━━\n";
        $text .= "🏰 *Kingdom:* KD #{$kd}\n";
        $text .= "⚠️ *Status:* OVERDUE (> 30 Menit)!\n";
        $text .= "👤 *Dibalas Oleh:* {$repliedBy} ({$minsSinceReply} menit lalu)\n";
        $text .= "🚨 *Keterangan:* Sudah di-reply 30 menit lalu tapi *BELUM DIKELARIN / DICEKLIS* di web!\n";
        $text .= "👥 *Sisa Char Belum Panen:* {$readyCount} Char\n";
        $text .= "⏱️ *Siklus:* Tiap {$cdStr} Jam\n";
        $text .= "@all\n";
        $text .= "━━━━━━━━━━━━━━━━━\n";
        $text .= "🔗 *Buka & Ceklis di Web:* {$currentUrl}\n";
        $text .= "_Bot akan spam setiap 5 menit sampai KD ini dikelarin / diceklis selesai panen!_ 🌾🚜";

        return $text;
    }

    // Helper format teks pesan 1 KD manual
    protected function buildSingleKdText($kd, $readyCount, $totalChars, $cooldownHours, $maxCooldownEnd, $worker)
    {
        $currentUrl = $this->getDashboardUrl();
        $cdStr = ((float)$cooldownHours == (int)$cooldownHours) ? (int)$cooldownHours : $cooldownHours;

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
            $text .= "💬 _Balas chat ini di grup (misal: 'otw') agar bot tidak spam tiap 10 menit!_ 🌾🚜";
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
