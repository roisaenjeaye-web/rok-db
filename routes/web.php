<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\KingdomController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\GameAccountController;
use App\Http\Controllers\BotNotificationController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return redirect('/stok');
});

Route::get('/ping-version', function () {
    return response()->json([
        'status' => 'live',
        'build' => 'get-ready-v1',
        'time' => now()->toIso8601String()
    ]);
});

Route::get('/api/bot/debug', function () {
    $isAuto = (bool) \App\Models\BotSetting::get('is_auto_notify', true);
    $phone = \App\Models\BotSetting::get('target_phone');
    $token = \App\Models\BotSetting::get('fonnte_token');

    $groupedByKD = \App\Models\Character::with('gameAccount')->get()->groupBy('kd_number')->sortKeys();
    $kingdoms = \App\Models\Kingdom::all()->keyBy('kd_number');

    $diagnostics = [];
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

        $lastNotified = $kingdom ? $kingdom->last_notified_at : null;
        $repliedAt = $kingdom ? $kingdom->replied_at : null;

        $diagnostics[] = [
            'kd' => $kd,
            'total_chars' => $kChars->count(),
            'ready_count' => $readyCount,
            'latest_farmed_at' => $latestFarmedAt ? $latestFarmedAt->toIso8601String() : null,
            'ready_since' => $kingdom && $kingdom->ready_since ? $kingdom->ready_since->toIso8601String() : null,
            'last_notified_at' => $lastNotified ? $lastNotified->toIso8601String() : null,
            'replied_at' => $repliedAt ? $repliedAt->toIso8601String() : null,
            'reminder_count' => $kingdom ? $kingdom->reminder_count : null,
            'guard_2m' => $lastNotified ? $lastNotified->gte(now()->subMinutes(2)) : false,
            'mins_since_last_notified' => $lastNotified ? (int) now()->diffInMinutes($lastNotified, true) : null,
            'now' => now()->toIso8601String(),
        ];
    }

    return response()->json([
        'is_auto' => $isAuto,
        'has_phone' => !empty($phone),
        'has_token' => !empty($token),
        'diagnostics' => $diagnostics,
    ]);
});

// Panel Admin — hanya bisa diakses user yang is_admin = true
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/user/{id}/delete', [AdminController::class, 'deleteUser'])->name('admin.deleteUser');
    Route::post('/user/{id}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('admin.toggleAdmin');
    Route::post('/char/{id}/adjust-farm', [AdminController::class, 'adjustFarm'])->name('admin.adjustFarm');
});


// Semua halaman operasional joki diwajibkan login agar otomatis mengenali operator
Route::middleware('auth')->group(function () {
    // Fitur Operator Joki Aktif (Switch Operator)
    Route::post('/operator/switch', [GameAccountController::class, 'setActiveWorker']);
    Route::post('/akun/set-worker', [GameAccountController::class, 'setActiveWorker']);

    // Fitur CRUD Stok Kingdom
    Route::get('/stok', [KingdomController::class, 'index'])->name('home');
    Route::post('/stok/tambah', [KingdomController::class, 'store']);
    Route::get('/stok/{id}/edit', [KingdomController::class, 'edit']);
    Route::post('/stok/{id}/update', [KingdomController::class, 'update']);
    Route::post('/stok/{id}/hapus', [KingdomController::class, 'destroy']);

    // Fitur Manajemen Gaji Joki (Input Manual, Edit, Hapus)
    Route::post('/gaji/simpan', [KingdomController::class, 'storeSalary']);
    Route::post('/gaji/{id}/update', [KingdomController::class, 'updateSalary']);
    Route::post('/gaji/{id}/hapus', [KingdomController::class, 'destroySalary']);

    // Fitur Kalkulator Joki & Order
    Route::get('/kalkulator', [CalculatorController::class, 'index']);
    Route::post('/kalkulator/hitung', [CalculatorController::class, 'calculate']);
    Route::post('/kalkulator/simpan', [OrderController::class, 'store']);
    Route::get('/riwayat', [OrderController::class, 'index']);
    Route::post('/riwayat/{id}/selesai', [OrderController::class, 'complete']);
    Route::post('/riwayat/{id}/hapus', [OrderController::class, 'destroy']);

    // Fitur Manajemen Akun & Ternak Farm
    Route::get('/akun', [GameAccountController::class, 'index']);
    Route::post('/akun/tambah', [GameAccountController::class, 'storeAccount']);
    Route::post('/akun/{id}/update', [GameAccountController::class, 'updateAccount']);
    Route::post('/akun/{id}/hapus', [GameAccountController::class, 'destroyAccount']);
    Route::post('/akun/tambah-char', [GameAccountController::class, 'storeCharacter']);
    Route::post('/akun/char/{id}/update', [GameAccountController::class, 'updateCharacter']);
    Route::post('/akun/char/{id}/hapus', [GameAccountController::class, 'destroyCharacter']);
    Route::post('/akun/char/{id}/farm', [GameAccountController::class, 'farmDone']);
    Route::post('/akun/char-bulk/farm', [GameAccountController::class, 'farmDoneBulk']);
    Route::post('/akun/char/{id}/adjust-log', [GameAccountController::class, 'adjustCharLog']);
    Route::post('/akun/char/{id}/force-ready', [GameAccountController::class, 'forceReadyChar']);

    // Fitur Jadwal Farm & Panen Serentak
    Route::get('/jadwal', [GameAccountController::class, 'jadwal']);
    Route::get('/jadwal/wa-reminder', [GameAccountController::class, 'waReminder']);
    Route::get('/jadwal/wa-reminder/{kd}', [GameAccountController::class, 'waReminder']);
    Route::post('/jadwal/kd/{kd}/farm', [GameAccountController::class, 'farmDoneSuperBulk']);
    Route::post('/jadwal/kd/{kd}/adjust-log', [GameAccountController::class, 'adjustKdLog']);
    Route::post('/jadwal/kd/{kd}/set-cooldown', [GameAccountController::class, 'setKdCooldown']);
    Route::post('/jadwal/kd/{kd}/force-ready', [GameAccountController::class, 'forceReadyKd']);

    // Fitur Bot WhatsApp Otomatis (Fonnte API Gateway)
    Route::get('/bot/settings', [BotNotificationController::class, 'getSettings']);
    Route::post('/bot/settings', [BotNotificationController::class, 'saveSettings']);
    Route::post('/bot/test-connection', [BotNotificationController::class, 'testConnection']);
    Route::post('/bot/get-groups', [BotNotificationController::class, 'getGroups']);
    Route::post('/bot/test-send', [BotNotificationController::class, 'testSend']);
    Route::post('/bot/notify-kd/{kd}', [BotNotificationController::class, 'notifySingleKd']);
    Route::post('/bot/notify-all', [BotNotificationController::class, 'notifyAllKds']);
    Route::post('/bot/check-auto-notify', [BotNotificationController::class, 'checkAndAutoNotify']);
    Route::post('/bot/acknowledge-kd/{kd}', [BotNotificationController::class, 'acknowledgeKd']);
    Route::get('/bot/{any}', fn() => redirect('/jadwal'))->where('any', '.*');

    // Graceful Fallback: Mencegah error "405 Method Not Allowed" jika ada yang akses URL aksi via GET (misal refresh/back browser)
    Route::get('/stok/tambah', fn() => redirect('/stok'));
    Route::get('/stok/{id}/hapus', fn() => redirect('/stok'));
    Route::get('/stok/{id}/update', fn($id) => redirect("/stok/{$id}/edit"));
    Route::get('/gaji/{any}', fn() => redirect('/stok'))->where('any', '.*');
    Route::get('/kalkulator/hitung', fn() => redirect('/kalkulator'));
    Route::get('/kalkulator/simpan', fn() => redirect('/kalkulator'));
    Route::get('/riwayat/{any}', fn() => redirect('/riwayat'))->where('any', '.*');
    Route::get('/akun/tambah', fn() => redirect('/akun'));
    Route::get('/akun/tambah-char', fn() => redirect('/akun'));
    Route::get('/akun/{any}', fn() => redirect('/akun'))->where('any', '.*');
    Route::get('/jadwal/{any}', fn() => redirect('/jadwal'))->where('any', '.*');
});

// Webhook Fonnte untuk menerima balasan chat WhatsApp dari member grup / user (tanpa CSRF & tanpa auth)
Route::post('/api/fonnte/webhook', [BotNotificationController::class, 'handleWebhook']);
Route::post('/bot/webhook', [BotNotificationController::class, 'handleWebhook']);

// Trigger Cron Bot Pengingat / Spam (dipanggil GitHub Actions setiap 5 menit)
// Dilindungi BOT_CRON_SECRET agar tidak bisa di-spam orang lain
Route::match(['get', 'post'], '/api/bot/cron', function (\Illuminate\Http\Request $request) {
    $secret = env('BOT_CRON_SECRET', '');
    $incoming = $request->bearerToken() ?? $request->input('secret', '');

    // Jika secret diset di .env, wajib cocok. Jika belum diset, izinkan (backward compatible)
    if (!empty($secret) && $incoming !== $secret) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    return app(\App\Http\Controllers\BotNotificationController::class)->checkAndAutoNotify($request);
});
Route::match(['get', 'post'], '/api/bot/check-auto-notify', [BotNotificationController::class, 'checkAndAutoNotify']);



// Serve satset.js directly with no-cache headers to bypass Vercel CDN static cache
Route::get('/js/satset-v2.js', function () {
    return response()->file(public_path('js/satset.js'), [
        'Content-Type' => 'application/javascript; charset=utf-8',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
    ]);
});
Route::get('/js/satset.js', function () {
    return response()->file(public_path('js/satset.js'), [
        'Content-Type' => 'application/javascript; charset=utf-8',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
    ]);
});