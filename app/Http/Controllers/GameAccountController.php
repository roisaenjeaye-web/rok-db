<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameAccount;
use App\Models\Character;
use App\Models\KdFarmerStat;
use App\Models\Kingdom;
use App\Models\BotSetting;
use App\Services\FonnteService;
use Illuminate\Support\Facades\DB;

class GameAccountController extends Controller
{
    // Helper untuk ambil username joki yang sedang aktif (hanya user operator biasa, bukan admin)
    public function getActiveWorker(?Request $request = null): string
    {
        // 1. Jika ada worker di session yang dipilih (dan bukan admin)
        if (session()->has('active_worker')) {
            $sw = session('active_worker');
            $isAdmin = \App\Models\User::where('name', $sw)->where('is_admin', true)->exists();
            if (!$isAdmin && !empty($sw)) {
                return $sw;
            }
        }

        // 2. Jika user yang login adalah operator biasa (bukan admin)
        if (auth()->check() && !auth()->user()->isAdmin()) {
            return auth()->user()->name;
        }

        // 3. Jika yang login adalah admin, pilih operator pertama (bukan admin)
        $firstOperator = \App\Models\User::where('is_admin', false)->value('name');
        return $firstOperator ?: 'Therta';
    }

    // Helper untuk mengambil daftar semua nama operator joki yang terdaftar di database User (tidak termasuk admin)
    public function getKnownWorkers(): array
    {
        $dbUsers = \App\Models\User::where('is_admin', false)->pluck('name')->unique()->values()->toArray();
        return !empty($dbUsers) ? $dbUsers : ['Therta'];
    }

    // Mengganti atau menambah username joki yang aktif (Admin dilarang)
    public function setActiveWorker(Request $request)
    {
        $firstOp = \App\Models\User::where('is_admin', false)->value('name') ?: 'Therta';
        $worker = trim($request->input('worker_name', $firstOp));
        if (empty($worker)) {
            $worker = $firstOp;
        }

        // Admin tidak boleh dijadikan worker macul
        $isAdmin = \App\Models\User::where('name', $worker)->where('is_admin', true)->exists();
        if ($isAdmin) {
            $msg = "❌ {$worker} adalah Admin dan tidak ikutan macul!";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // Simpan ke session
        session(['active_worker' => $worker]);

        // Simpan ke list worker jika nama baru
        $customWorkers = session('custom_workers', []);
        if (!in_array($worker, $customWorkers)) {
            $customWorkers[] = $worker;
            session(['custom_workers' => $customWorkers]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Operator Joki aktif diset ke: 👤 {$worker}",
                'worker' => $worker,
            ]);
        }

        return back()->with('success', "Operator Joki aktif diset ke: 👤 {$worker}");
    }

    // Nampilin dashboard Akun & Char (dikelompokkan per KD seperti jadwal macul)
    public function index()
    {
        $accounts = GameAccount::with('characters')->orderBy('created_at', 'desc')->get();
        
        // Ambil semua karakter beserta data akunnya, lalu kelompokkan berdasarkan Nomor KD
        $characters = Character::with('gameAccount')->get();
        $groupedByKD = $characters->groupBy('kd_number')->sortKeys();

        // Akun Gmail yang belum memiliki karakter sama sekali
        $unassignedAccounts = $accounts->filter(function ($acc) {
            return $acc->characters->isEmpty();
        });

        $adminNames = \App\Models\User::where('is_admin', true)->pluck('name')->toArray();
        $kdStats = KdFarmerStat::whereNotIn('worker_name', $adminNames)->get()->groupBy('kd_number');
        $knownWorkers = $this->getKnownWorkers();
        $activeWorker = $this->getActiveWorker(request());
        $kingdoms = Kingdom::all()->keyBy('kd_number');
        $isAdmin = auth()->check() ? auth()->user()->isAdmin() : false;

        return view('akun', compact('accounts', 'characters', 'groupedByKD', 'unassignedAccounts', 'kdStats', 'knownWorkers', 'activeWorker', 'kingdoms', 'isAdmin'));
    }

    // Nyimpen Akun Gmail Baru
    public function storeAccount(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:game_accounts,email'
        ]);

        GameAccount::create([
            'email' => $request->email,
            'password' => $request->password,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Akun Gmail ' . $request->email . ' berhasil dicatat!');
    }

    // Update / Edit Akun Gmail
    public function updateAccount(Request $request, $id)
    {
        $account = GameAccount::findOrFail($id);
        $request->validate([
            'email' => 'required|email|unique:game_accounts,email,' . $id,
        ]);

        $account->update([
            'email' => $request->email,
            'password' => $request->password,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Akun Gmail ' . $account->email . ' berhasil diperbarui!');
    }

    // Hapus Akun Gmail Beserta Semua Karakter di Dalamnya
    public function destroyAccount($id)
    {
        $account = GameAccount::findOrFail($id);
        $email = $account->email;
        $charCount = $account->characters()->count();

        DB::transaction(function () use ($account) {
            $account->characters()->delete();
            $account->delete();
        });

        return back()->with('success', "Akun {$email} beserta {$charCount} karakternya berhasil dihapus permanen!");
    }

    // Nyimpen Char ke dalam Gmail (Bisa banyak sekaligus)
    public function storeCharacter(Request $request)
    {
        $request->validate([
            'game_account_id' => 'required|exists:game_accounts,id',
            'kd_number' => 'required|numeric',
            'jumlah' => 'required|numeric|min:1'
        ]);

        for ($i = 0; $i < $request->jumlah; $i++) {
            Character::create([
                'game_account_id' => $request->game_account_id,
                'kd_number' => $request->kd_number,
                'ign' => $request->ign ? $request->ign . ' ' . ($i + 1) : 'Farm KD ' . $request->kd_number, 
                'status' => 'aktif',
                'farmer_stats' => [],
            ]);
        }

        // Otomatis sinkronkan KD ke Dashboard Stok agar tidak perlu input manual
        Kingdom::firstOrCreate(
            ['kd_number' => $request->kd_number],
            ['food' => 0, 'wood' => 0, 'stone' => 0, 'gold' => 0, 'cooldown_hours' => 4.0]
        );

        return back()->with('success', $request->jumlah . ' Karakter berhasil dimasukkan ke KD #' . $request->kd_number . '!');
    }

    // Update / Edit Karakter (Ubah IGN / Pindah KD)
    public function updateCharacter(Request $request, $id)
    {
        $char = Character::findOrFail($id);
        $request->validate([
            'ign' => 'required|string|max:100',
            'kd_number' => 'required|numeric',
        ]);

        $char->update([
            'ign' => $request->ign,
            'kd_number' => $request->kd_number,
        ]);

        Kingdom::firstOrCreate(
            ['kd_number' => $request->kd_number],
            ['food' => 0, 'wood' => 0, 'stone' => 0, 'gold' => 0, 'cooldown_hours' => 4.0]
        );

        return back()->with('success', "Karakter {$char->ign} di KD #{$char->kd_number} berhasil diperbarui!");
    }

    // Hapus Karakter Permanen (Admin Only)
    public function destroyCharacter($id)
    {
        // Hanya admin yang boleh hapus karakter
        if (auth()->check() && !auth()->user()->isAdmin()) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => '❌ Akses ditolak! Hanya Admin yang bisa menghapus karakter.'], 403);
            }
            return back()->with('error', '❌ Akses ditolak! Hanya Admin yang bisa menghapus karakter.');
        }

        $char = Character::findOrFail($id);
        $ign = $char->ign;
        $char->delete();

        return back()->with('success', "Karakter {$ign} berhasil dihapus permanen!");
    }

    // Menandai satu char selesai difarm dengan pencatatan nama joki
    public function farmDone(Request $request, $id)
    {
        $char = Character::findOrFail($id);
        $worker = $this->getActiveWorker($request);

        DB::transaction(function () use ($char, $worker) {
            $char->recordFarm($worker);
            KdFarmerStat::incrementCount((int)$char->kd_number, $worker, 1);
        });

        $kingdom = Kingdom::where('kd_number', $char->kd_number)->first();
        $cooldownHours = $kingdom ? (float) $kingdom->cooldown_hours : 4.0;

        if ($kingdom) {
            $cdMins = (int) round($cooldownHours * 60);
            $hasReady = Character::where('kd_number', $char->kd_number)
                ->where(function ($q) use ($cdMins) {
                    $q->whereNull('last_farmed_at')
                      ->orWhere('last_farmed_at', '<=', now()->subMinutes($cdMins));
                })->exists();

            if (!$hasReady) {
                $kingdom->replied_at = null;
                $kingdom->replied_by = null;
                $kingdom->ready_since = null;
                $kingdom->reminder_count = 0;
                $kingdom->save();
            }
        }

        $msg = "Char {$char->ign} selesai difarm oleh {$worker}! Jeda {$cooldownHours} jam dimulai.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'char_id' => $char->id,
                'ign' => $char->ign,
                'farm_count' => $char->farm_count,
                'cooldown_hours' => $cooldownHours
            ]);
        }

        return back()->with('success', $msg);
    }

    // Menandai SEMUA char di satu KD dalam satu akun selesai difarm
    public function farmDoneBulk(Request $request)
    {
        $request->validate([
            'game_account_id' => 'required|exists:game_accounts,id',
            'kd_number' => 'required|numeric',
        ]);

        $worker = $this->getActiveWorker($request);
        $kd = $request->kd_number;

        $kingdom = Kingdom::where('kd_number', $kd)->first();
        $cooldownHours = $kingdom ? (float) $kingdom->cooldown_hours : 4.0;
        $cooldownMinutes = (int) round($cooldownHours * 60);

        $updatedCount = DB::transaction(function () use ($request, $worker, $cooldownMinutes) {
            $chars = Character::where('game_account_id', $request->game_account_id)
                              ->where('kd_number', $request->kd_number)
                              ->get();

            $count = 0;
            $cooldownThreshold = now()->subMinutes($cooldownMinutes);
            foreach ($chars as $char) {
                if (!$char->last_farmed_at || $char->last_farmed_at <= $cooldownThreshold) {
                    $char->recordFarm($worker);
                    $count++;
                }
            }

            if ($count > 0) {
                KdFarmerStat::incrementCount((int)$request->kd_number, $worker, 1);
            }

            return $count;
        });

        if ($updatedCount > 0) {
            if ($kingdom) {
                $hasReady = Character::where('kd_number', $kd)
                    ->where(function ($q) use ($cooldownMinutes) {
                        $q->whereNull('last_farmed_at')
                          ->orWhere('last_farmed_at', '<=', now()->subMinutes($cooldownMinutes));
                    })->exists();

                if (!$hasReady) {
                    $kingdom->replied_at = null;
                    $kingdom->replied_by = null;
                    $kingdom->ready_since = null;
                    $kingdom->reminder_count = 0;
                    $kingdom->save();
                }
            }

            $msg = "{$updatedCount} Char di KD #{$request->kd_number} selesai difarm oleh {$worker}! Jeda {$cooldownHours} jam dimulai.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg, 'count' => $updatedCount]);
            }
            return back()->with('success', $msg);
        }

        $errMsg = "Semua char di KD ini masih dalam masa jeda ({$cooldownHours} jam).";
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $errMsg], 400);
        }
        return back()->with('error', $errMsg);
    }

    // Menampilkan Jadwal Farm KD (Minimalist View)
    public function jadwal()
    {
        // Pastikan semua KD yang ada di karakter terdaftar di tabel kingdoms
        $charKds = Character::distinct()->pluck('kd_number')->toArray();
        $existingKds = Kingdom::pluck('kd_number')->toArray();
        $missingKds = array_diff($charKds, $existingKds);
        if (!empty($missingKds)) {
            $now = now();
            $insertRows = [];
            foreach ($missingKds as $mKd) {
                $insertRows[] = [
                    'kd_number' => $mKd,
                    'cooldown_hours' => 4.0,
                    'food' => 0,
                    'wood' => 0,
                    'stone' => 0,
                    'gold' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            Kingdom::insert($insertRows);
        }

        $characters = Character::with('gameAccount')->get();
        $groupedByKD = $characters->groupBy('kd_number')->sortKeys();
        $adminNames = \App\Models\User::where('is_admin', true)->pluck('name')->toArray();
        $kdStats = KdFarmerStat::whereNotIn('worker_name', $adminNames)->get()->groupBy('kd_number');
        $knownWorkers = $this->getKnownWorkers();
        $activeWorker = $this->getActiveWorker(request());
        $kingdoms = Kingdom::all()->keyBy('kd_number');
        $isAdmin = auth()->check() ? auth()->user()->isAdmin() : false;
        $isAutoNotify = (bool) \App\Models\BotSetting::get('is_auto_notify', true);
        $spamUnrepliedInterval = (int) \App\Models\BotSetting::get('spam_unreplied_interval', 15);
        $maxWorkMinutes = (int) \App\Models\BotSetting::get('max_work_minutes', 30);
        $spamOverdueInterval = (int) \App\Models\BotSetting::get('spam_overdue_interval', 5);

        return view('jadwal', compact(
            'groupedByKD', 'kdStats', 'knownWorkers', 'activeWorker', 'kingdoms', 'isAdmin',
            'isAutoNotify', 'spamUnrepliedInterval', 'maxWorkMinutes', 'spamOverdueInterval'
        ));
    }

    // Panen Super Bulk satu Kingdom serentak dengan pencatatan nama joki
    public function farmDoneSuperBulk(Request $request, $kd)
    {
        $worker = $this->getActiveWorker($request);

        // Ambil jeda / cooldown khusus untuk KD ini (default 4 jam)
        $kingdom = Kingdom::where('kd_number', $kd)->first();
        $cooldownHours = $kingdom ? (float) $kingdom->cooldown_hours : 4.0;
        $cooldownMinutes = (int) round($cooldownHours * 60);

        $result = DB::transaction(function () use ($kd, $worker, $cooldownMinutes) {
            $chars = Character::where('kd_number', $kd)
                ->where(function ($query) use ($cooldownMinutes) {
                    $query->whereNull('last_farmed_at')
                          ->orWhere('last_farmed_at', '<=', now()->subMinutes($cooldownMinutes));
                })
                ->get();

            if ($chars->isEmpty()) {
                return null;
            }

            foreach ($chars as $char) {
                $char->recordFarm($worker);
            }

            // Tambah hitungan di level Kingdom untuk worker tersebut
            $stat = KdFarmerStat::incrementCount((int)$kd, $worker, 1);

            return [
                'count' => $chars->count(),
                'stat' => $stat,
            ];
        });

        if (!$result) {
            $errMsg = "Semua char di KD #{$kd} masih dalam masa jeda ({$cooldownHours} Jam).";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errMsg], 400);
            }
            return back()->with('error', $errMsg);
        }

        // Reset tracking spam & pengingat untuk KD ini karena panen sudah selesai (diceklis)
        if ($kingdom) {
            $kingdom->replied_at = null;
            $kingdom->replied_by = null;
            $kingdom->ready_since = null;
            $kingdom->reminder_count = 0;
            $kingdom->last_notified_at = now();
            $kingdom->save();

            // Kirim notifikasi panen selesai ke grup WA jika bot aktif
            $isAuto = (bool) BotSetting::get('is_auto_notify', true);
            $phone = BotSetting::get('target_phone');
            if ($isAuto && !empty($phone)) {
                $jamSelesai = now()->setTimezone('Asia/Jakarta')->addMinutes($cooldownMinutes)->format('H:i') . ' WIB';
                $cdStr = ((float)$cooldownHours == (int)$cooldownHours) ? (int)$cooldownHours : $cooldownHours;
                $dashUrl = BotNotificationController::resolveDashboardUrl();

                $selesaiMsg = "✅ *PANEN SERENTAK KD #{$kd} SELESAI!* 🌾🚜\n";
                $selesaiMsg .= "━━━━━━━━━━━━━━━━━\n";
                $selesaiMsg .= "👤 *Operator:* {$worker}\n";
                $selesaiMsg .= "👥 *Total Panen:* {$result['count']} Char\n";
                $selesaiMsg .= "⏱️ *Masa Jeda:* {$cdStr} Jam dimulai\n";
                $selesaiMsg .= "🎯 *Estimasi Siap Berikutnya:* pk {$jamSelesai}\n";
                $selesaiMsg .= "━━━━━━━━━━━━━━━━━\n";
                $selesaiMsg .= "🔗 *Buka Dashboard:* {$dashUrl}\n";
                $selesaiMsg .= "_Spam pengingat resmi dinonaktifkan. Terima kasih tim!_ ✨";

                FonnteService::sendMessage($phone, $selesaiMsg);
            }
        }

        $msg = "⚡ BOMBASTIS! {$result['count']} Char di KD #{$kd} dipanen serentak oleh {$worker}! Jeda {$cooldownHours} jam dimulai. (Total KD: {$result['stat']->farm_count}x)";
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'count' => $result['count'],
                'total_kd' => $result['stat']->farm_count,
                'worker' => $worker,
                'cooldown_hours' => $cooldownHours
            ]);
        }

        return back()->with('success', $msg);
    }

    // Fitur Set Waktu Jeda Macul per Kingdom (KD)
    public function setKdCooldown(Request $request, $kd)
    {
        $request->validate([
            'cooldown_hours' => 'required|numeric|min:0.1|max:48',
        ]);

        $cooldownHours = round((float) $request->input('cooldown_hours'), 1);

        $kingdom = Kingdom::firstOrCreate(
            ['kd_number' => $kd],
            ['food' => 0, 'wood' => 0, 'stone' => 0, 'gold' => 0, 'cooldown_hours' => $cooldownHours]
        );

        $kingdom->update(['cooldown_hours' => $cooldownHours]);

        // Pengaturan Mute/Tidur Khusus KD Ini (Admin Only)
        if (auth()->check() && auth()->user()->isAdmin() && $request->has('is_kd_muted')) {
            \App\Models\BotSetting::set("kd_mute_{$kd}", $request->boolean('is_kd_muted') ? '1' : '0');
        }

        $msg = "⏱️ Waktu jeda macul KD #{$kd} berhasil diatur menjadi {$cooldownHours} Jam sekali!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'kd_number' => (int) $kd,
                'cooldown_hours' => $cooldownHours
            ]);
        }

        return back()->with('success', $msg);
    }

    // Fitur Matikan / Jeda Cooldown KD Tertentu (Mode Tidur KD) - Admin Only
    public function togglePauseKdCd(Request $request, $kd)
    {
        self::checkAdminOrFail();

        $current = (bool) \App\Models\BotSetting::get("kd_mute_{$kd}", false);
        $newStatus = !$current;
        \App\Models\BotSetting::set("kd_mute_{$kd}", $newStatus ? '1' : '0');

        $user = auth()->user() ? auth()->user()->name : 'Admin';
        $statusStr = $newStatus ? 'dimatikan (Mode Tidur)' : 'diaktifkan kembali';
        $msg = "😴 Pengingat/CD KD #{$kd} berhasil {$statusStr} oleh Admin {$user}!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_muted' => $newStatus,
                'message' => $msg,
                'kd_number' => (int) $kd,
            ]);
        }

        return back()->with('success', $msg);
    }

    // Fitur Direct Pengingat WhatsApp (Server-Side Fallback)
    public function waReminder(Request $request, $kd = null)
    {
        $worker = $this->getActiveWorker($request);
        $phone = preg_replace('/[^0-9]/', '', $request->input('phone', ''));
        if (str_starts_with($phone, '08')) {
            $phone = '628' . substr($phone, 2);
        }

        $nowStr = now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB';
        $currentUrl = url('/jadwal');

        if ($kd !== null) {
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

            $cdStr = ((float)$cooldownHours == (int)$cooldownHours) ? (int)$cooldownHours : $cooldownHours;
            $currentUrl = \App\Http\Controllers\BotNotificationController::resolveDashboardUrl();

            if ($readyCount > 0 || !$maxCooldownEnd || now()->greaterThanOrEqualTo($maxCooldownEnd)) {
                $text = "🚨 *PANGGILAN PANEN FARM RoK* 🚨\n";
                $text .= "━━━━━━━━━━━━━━━━━\n";
                $text .= "🏰 *Kingdom:* KD #{$kd}\n";
                $text .= "✅ *Status:* SUDAH SIAP PANEN!\n";
                $text .= "👥 *Total Char:* " . ($readyCount ?: $chars->count()) . " Char Siap\n";
                $text .= "⏱️ *Siklus:* Tiap {$cdStr} Jam\n";
                $text .= "@all\n";
                $text .= "━━━━━━━━━━━━━━━━━\n";
                $text .= "🔗 *Buka Dashboard:* {$currentUrl}\n";
                $text .= "_Pesan otomatis dari Bot RoK Joki. Yuk gas macul sekarang!_ 🌾🚜";
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
                $text .= "👥 *Target Char:* " . $chars->count() . " Char\n";
                $text .= "⏱️ *Siklus:* Tiap {$cdStr} Jam\n";
                $text .= "@all\n";
                $text .= "━━━━━━━━━━━━━━━━━\n";
                $text .= "🔗 *Buka Dashboard:* {$currentUrl}\n";
                $text .= "_Pesan dari Bot RoK Joki. Set alarm jam {$jamSelesai}!_ ⏰";
            }
        } else {
            // Rekap Semua KD
            $groupedByKD = Character::with('gameAccount')->get()->groupBy('kd_number')->sortKeys();
            $kingdoms = Kingdom::all()->keyBy('kd_number');
            $currentUrl = \App\Http\Controllers\BotNotificationController::resolveDashboardUrl();

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

            $text = "📋 *REKAP PENGINGAT JADWAL FARM RoK* 📋\n";
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
            $text .= "_Update langsung dari RoK Joki Admin Satset!_ 🌾🚜";
        }

        $waUrl = !empty($phone)
            ? 'https://api.whatsapp.com/send?phone=' . $phone . '&text=' . rawurlencode($text)
            : 'https://api.whatsapp.com/send?text=' . rawurlencode($text);

        return redirect()->away($waUrl);
    }

    // Fitur Kurangi / Tambah / Reset / Atur Angka Log per KD (Khusus Admin)
    public function adjustKdLog(Request $request, $kd)
    {
        // Hanya Admin yang boleh mengubah atau mereset jumlah hitungan macul
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            $msg = '❌ Akses ditolak! Hanya Admin yang berhak mengubah atau mereset jumlah macul.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        $request->validate([
            'worker_name' => 'required|string',
            'action' => 'required|in:decrement,increment,reset,set',
            'custom_count' => 'nullable|numeric|min:0',
        ]);

        $worker = $request->input('worker_name');
        $action = $request->input('action');
        $customCount = (int) $request->input('custom_count', 0);

        $stat = DB::transaction(function () use ($kd, $worker, $action, $customCount, $request) {
            if ($action === 'decrement') {
                $s = KdFarmerStat::decrementCount((int)$kd, $worker, 1);
            } elseif ($action === 'increment') {
                $s = KdFarmerStat::incrementCount((int)$kd, $worker, 1);
            } elseif ($action === 'reset') {
                $s = KdFarmerStat::setCount((int)$kd, $worker, 0);
            } else {
                $s = KdFarmerStat::setCount((int)$kd, $worker, $customCount);
            }

            // Jika dicentang untuk update juga karakter di KD tersebut
            if ($request->boolean('apply_to_chars')) {
                $chars = Character::where('kd_number', $kd)->get();
                foreach ($chars as $char) {
                    $char->adjustFarmLog($worker, $action, $customCount);
                }
            }

            return $s;
        });

        if ($action === 'decrement') {
            $msg = "Log farm {$worker} di KD #{$kd} dikurangi 1. Sekarang: {$stat->farm_count}x.";
        } elseif ($action === 'increment') {
            $msg = "Log farm {$worker} di KD #{$kd} ditambah 1. Sekarang: {$stat->farm_count}x.";
        } elseif ($action === 'reset') {
            $msg = "Log farm {$worker} di KD #{$kd} berhasil di-reset ke 0.";
        } else {
            $msg = "Log farm {$worker} di KD #{$kd} berhasil diatur menjadi {$stat->farm_count}x.";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'worker' => $worker,
                'farm_count' => $stat->farm_count
            ]);
        }

        return back()->with('success', $msg);
    }

    // Fitur Kurangi / Reset / Atur Angka Log per Karakter (Log Admin Only, Reset Cooldown Semua User)
    public function adjustCharLog(Request $request, $id)
    {
        $request->validate([
            'worker_name' => 'nullable|string',
            'action' => 'required|in:decrement,reset,set,reset_cooldown',
            'custom_count' => 'nullable|numeric|min:0',
        ]);

        $action = $request->input('action');

        // Jika mengubah hitungan macul (bukan cuma reset jeda), wajib Admin
        if ($action !== 'reset_cooldown' && (!auth()->check() || !auth()->user()->isAdmin())) {
            $msg = '❌ Akses ditolak! Hanya Admin yang berhak mengubah jumlah hitungan macul karakter.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        $char = Character::findOrFail($id);
        $worker = $request->input('worker_name', $this->getActiveWorker());
        $customCount = (int) $request->input('custom_count', 0);

        if ($action === 'reset_cooldown') {
            $char->last_farmed_at = null;
            $char->save();
            $msg = "Cooldown untuk char {$char->ign} berhasil di-reset (status: Siap Panen)!";
        } else {
            DB::transaction(function () use ($char, $worker, $action, $customCount) {
                $char->adjustFarmLog($worker, $action, $customCount);
            });
            $msg = "Log {$worker} untuk char {$char->ign} berhasil diubah! (Total: {$char->farm_count}x)";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'worker' => $worker,
                'farm_count' => $char->farm_count
            ]);
        }

        return back()->with('success', $msg);
    }

    // Fitur Force Ready: Reset Cooldown SEMUA Char di satu KD sekaligus (Get Ready) - Atomic & Super Satset
    public function forceReadyKd(Request $request, $kd)
    {
        $charsCount = Character::where('kd_number', $kd)->count();

        if ($charsCount === 0) {
            $msg = "Tidak ada karakter di KD #{$kd}.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return back()->with('error', $msg);
        }

        DB::transaction(function () use ($kd) {
            // Update langsung atomic 1 query SQL (10ms instan tanpa lag looping save)
            Character::where('kd_number', $kd)->update(['last_farmed_at' => null]);

            // Reset tracking notif KD juga
            $kingdom = Kingdom::where('kd_number', $kd)->first();
            if ($kingdom) {
                $kingdom->replied_at = null;
                $kingdom->replied_by = null;
                $kingdom->ready_since = null;
                $kingdom->reminder_count = 0;
                $kingdom->save();
            }
        });

        $msg = "✅ {$charsCount} Char di KD #{$kd} berhasil di-Get Ready (cooldown direset, siap panen sekarang)!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'kd_number' => (int) $kd,
                'reset_count' => $charsCount,
            ]);
        }

        return back()->with('success', $msg);
    }

    // Fitur Get Ready per Karakter Tunggal (Reset jeda cooldown jadi siap panen sekarang)
    public function forceReadyChar(Request $request, $id)
    {
        $char = Character::findOrFail($id);
        $char->last_farmed_at = null;
        $char->save();

        $msg = "✅ Char {$char->ign} berhasil di-Get Ready (siap panen sekarang)!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'char_id' => $char->id,
            ]);
        }

        return back()->with('success', $msg);
    }

    // Fitur Tambah Waktu Cooldown KD Saat Ini (Kendala Lapangan / Molor)
    public function extendKdCooldown(Request $request, $kd)
    {
        $request->validate([
            'minutes' => 'required|numeric|min:1|max:1440',
        ]);

        $minutes = (int) $request->input('minutes');
        $charsCount = Character::where('kd_number', $kd)->count();

        if ($charsCount === 0) {
            $msg = "Tidak ada karakter di KD #{$kd}.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return back()->with('error', $msg);
        }

        $kingdom = Kingdom::where('kd_number', $kd)->first();
        $cdHours = $kingdom ? (float)$kingdom->cooldown_hours : 4.0;
        $cdMinutes = (int) round($cdHours * 60);

        DB::transaction(function () use ($kd, $minutes, $cdMinutes, $kingdom) {
            // Karakter yang sedang cooldown (last_farmed_at NOT NULL): majukan last_farmed_at
            DB::statement("UPDATE characters SET last_farmed_at = DATE_ADD(last_farmed_at, INTERVAL ? MINUTE) WHERE kd_number = ? AND last_farmed_at IS NOT NULL", [$minutes, $kd]);

            // Karakter yang siap panen (last_farmed_at IS NULL): beri cooldown sebesar $minutes
            DB::statement("UPDATE characters SET last_farmed_at = DATE_ADD(DATE_SUB(NOW(), INTERVAL ? MINUTE), INTERVAL ? MINUTE) WHERE kd_number = ? AND last_farmed_at IS NULL", [$cdMinutes, $minutes, $kd]);

            // Reset status pengingat bot untuk siklus ini
            if ($kingdom) {
                $kingdom->replied_at = null;
                $kingdom->replied_by = null;
                $kingdom->ready_since = null;
                $kingdom->reminder_count = 0;
                $kingdom->save();
            }
        });

        $user = auth()->user() ? auth()->user()->name : 'Operator';
        $msg = "⏱️ Cooldown KD #{$kd} berhasil ditambah {$minutes} Menit oleh {$user}! Hanya berlaku untuk siklus ini.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'kd_number' => (int) $kd,
                'added_minutes' => $minutes,
            ]);
        }

        return back()->with('success', $msg);
    }

    // Fitur Tambah Waktu Cooldown Char Tunggal Saat Ini
    public function extendCharCooldown(Request $request, $id)
    {
        $request->validate([
            'minutes' => 'required|numeric|min:1|max:1440',
        ]);

        $minutes = (int) $request->input('minutes');
        $char = Character::findOrFail($id);

        $kingdom = Kingdom::where('kd_number', $char->kd_number)->first();
        $cdHours = $kingdom ? (float)$kingdom->cooldown_hours : 4.0;
        $cdMinutes = (int) round($cdHours * 60);

        if ($char->last_farmed_at !== null) {
            $char->last_farmed_at = $char->last_farmed_at->copy()->addMinutes($minutes);
        } else {
            $char->last_farmed_at = now()->subMinutes($cdMinutes)->addMinutes($minutes);
        }
        $char->save();

        $user = auth()->user() ? auth()->user()->name : 'Operator';
        $msg = "⏱️ Cooldown char {$char->ign} berhasil ditambah {$minutes} Menit oleh {$user}!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'char_id' => $char->id,
                'added_minutes' => $minutes,
            ]);
        }

        return back()->with('success', $msg);
    }

    // Atur Gaji Joki (Admin Only - via KingdomController, tapi guard bisa dipakai dari sini juga)
    public static function checkAdminOrFail()
    {
        if (auth()->check() && !auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak! Hanya Admin yang bisa melakukan aksi ini.');
        }
    }
}