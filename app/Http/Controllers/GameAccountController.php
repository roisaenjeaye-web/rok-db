<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameAccount;
use App\Models\Character;
use App\Models\KdFarmerStat;
use Illuminate\Support\Facades\DB;

class GameAccountController extends Controller
{
    // Helper untuk ambil username joki yang sedang aktif (otomatis dari auth login)
    protected function getActiveWorker(Request $request): string
    {
        if (auth()->check()) {
            return auth()->user()->name;
        }

        return trim($request->input('worker_name') ?: 'Rois');
    }

    // Helper untuk mengambil daftar semua nama joki yang terdaftar (dari User & Stats)
    protected function getKnownWorkers(): array
    {
        $defaultWorkers = ['Rois', 'Therta'];
        $dbUsers = \App\Models\User::pluck('name')->toArray();
        $statWorkers = KdFarmerStat::select('worker_name')->distinct()->pluck('worker_name')->toArray();

        $all = array_values(array_unique(array_filter(array_merge($defaultWorkers, $dbUsers, $statWorkers))));
        return $all;
    }

    // Mengganti atau menambah username joki yang aktif
    public function setActiveWorker(Request $request)
    {
        $worker = trim($request->input('worker_name', 'Rois'));
        if (empty($worker)) {
            $worker = 'Rois';
        }

        // Simpan ke session
        session(['active_worker' => $worker]);

        // Simpan ke list worker jika nama baru
        $customWorkers = session('custom_workers', []);
        if (!in_array($worker, $customWorkers)) {
            $customWorkers[] = $worker;
            session(['custom_workers' => $customWorkers]);
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

        $kdStats = KdFarmerStat::all()->groupBy('kd_number');
        $knownWorkers = $this->getKnownWorkers();

        return view('akun', compact('accounts', 'groupedByKD', 'unassignedAccounts', 'kdStats', 'knownWorkers'));
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
        \App\Models\Kingdom::firstOrCreate(
            ['kd_number' => $request->kd_number],
            ['food' => 0, 'wood' => 0, 'stone' => 0, 'gold' => 0]
        );

        return back()->with('success', $request->jumlah . ' Karakter berhasil dimasukkan ke KD #' . $request->kd_number . '!');
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

        $msg = "Char {$char->ign} selesai difarm oleh {$worker}! Jeda 4 jam dimulai.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'char_id' => $char->id,
                'ign' => $char->ign,
                'farm_count' => $char->farm_count,
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

        $updatedCount = DB::transaction(function () use ($request, $worker) {
            $chars = Character::where('game_account_id', $request->game_account_id)
                              ->where('kd_number', $request->kd_number)
                              ->get();

            $count = 0;
            $fourHoursAgo = now()->subHours(4);
            foreach ($chars as $char) {
                if (!$char->last_farmed_at || $char->last_farmed_at <= $fourHoursAgo) {
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
            $msg = "{$updatedCount} Char di KD #{$request->kd_number} selesai difarm oleh {$worker}! Jeda dimulai.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg, 'count' => $updatedCount]);
            }
            return back()->with('success', $msg);
        }

        $errMsg = 'Semua char di KD ini masih dalam masa cooldown.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $errMsg], 400);
        }
        return back()->with('error', $errMsg);
    }

    // Menampilkan Jadwal Farm KD (Minimalist View)
    public function jadwal()
    {
        $characters = Character::with('gameAccount')->get();
        $groupedByKD = $characters->groupBy('kd_number')->sortKeys();
        $kdStats = KdFarmerStat::all()->groupBy('kd_number');
        $knownWorkers = $this->getKnownWorkers();

        return view('jadwal', compact('groupedByKD', 'kdStats', 'knownWorkers'));
    }

    // Panen Super Bulk satu Kingdom serentak dengan pencatatan nama joki
    public function farmDoneSuperBulk(Request $request, $kd)
    {
        $worker = $this->getActiveWorker($request);

        $result = DB::transaction(function () use ($kd, $worker) {
            $chars = Character::where('kd_number', $kd)
                ->where(function ($query) {
                    $query->whereNull('last_farmed_at')
                          ->orWhere('last_farmed_at', '<=', now()->subHours(4));
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
            $errMsg = 'Semua char di KD #' . $kd . ' masih dalam masa jeda (Cooldown).';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errMsg], 400);
            }
            return back()->with('error', $errMsg);
        }

        $msg = "⚡ BOMBASTIS! {$result['count']} Char di KD #{$kd} dipanen serentak oleh {$worker}! (Total KD #{$kd} oleh {$worker}: {$result['stat']->farm_count}x)";
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'count' => $result['count'],
                'total_kd' => $result['stat']->farm_count,
                'worker' => $worker
            ]);
        }

        return back()->with('success', $msg);
    }

    // Fitur Kurangi / Reset / Atur Angka Log per KD
    public function adjustKdLog(Request $request, $kd)
    {
        $request->validate([
            'worker_name' => 'required|string',
            'action' => 'required|in:decrement,reset,set',
            'custom_count' => 'nullable|numeric|min:0',
        ]);

        $worker = $request->input('worker_name');
        $action = $request->input('action');
        $customCount = (int) $request->input('custom_count', 0);

        $stat = DB::transaction(function () use ($kd, $worker, $action, $customCount, $request) {
            if ($action === 'decrement') {
                $s = KdFarmerStat::decrementCount((int)$kd, $worker, 1);
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

    // Fitur Kurangi / Reset / Atur Angka Log per Karakter
    public function adjustCharLog(Request $request, $id)
    {
        $request->validate([
            'worker_name' => 'required|string',
            'action' => 'required|in:decrement,reset,set',
            'custom_count' => 'nullable|numeric|min:0',
        ]);

        $char = Character::findOrFail($id);
        $worker = $request->input('worker_name');
        $action = $request->input('action');
        $customCount = (int) $request->input('custom_count', 0);

        DB::transaction(function () use ($char, $worker, $action, $customCount) {
            $char->adjustFarmLog($worker, $action, $customCount);
        });

        $msg = "Log {$worker} untuk char {$char->ign} berhasil diubah! (Total: {$char->farm_count}x)";
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
}