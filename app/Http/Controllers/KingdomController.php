<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kingdom;
use App\Models\KdFarmerStat;
use App\Models\Character;
use App\Models\Salary;
use App\Models\User;

class KingdomController extends Controller
{
    public function index(Request $request)
    {
        // 1. OTOMATIS SINKRONISASI: Semua KD dari data Akun & Char langsung masuk ke Dashboard Stok
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

        $kingdoms = Kingdom::orderBy('kd_number', 'asc')->get();

        // 2. Daftar operator joki (hanya dari User terdaftar di database)
        $dbUsers = User::pluck('name')->toArray();
        $allWorkers = !empty($dbUsers) ? $dbUsers : ['Rois'];

        $allStats = KdFarmerStat::all();
        $allSalaries = Salary::all();

        $resumeJoki = [];
        $totalOverallFarm = 0;
        $totalOverallSalary = (float) $allSalaries->sum('amount');

        foreach ($allWorkers as $worker) {
            $workerStats = $allStats->where('worker_name', $worker);
            $totalFarm = (int) $workerStats->sum('farm_count');
            $workerSalary = (float) $allSalaries->where('worker_name', $worker)->sum('amount');

            $kdBreakdown = $workerStats->where('farm_count', '>', 0)->map(function ($stat) {
                return [
                    'kd_number' => $stat->kd_number,
                    'count' => $stat->farm_count,
                ];
            })->values();

            $resumeJoki[] = [
                'name' => $worker,
                'total_farm' => $totalFarm,
                'total_salary' => $workerSalary,
                'kd_breakdown' => $kdBreakdown,
            ];

            $totalOverallFarm += $totalFarm;
        }

        // Urutkan worker berdasarkan total macul / aktivitas
        usort($resumeJoki, function ($a, $b) {
            return $b['total_farm'] <=> $a['total_farm'];
        });

        // Riwayat transaksi gaji manual
        $salaries = Salary::orderBy('date', 'desc')->orderBy('created_at', 'desc')->get();

        return view('kingdom', compact(
            'kingdoms',
            'resumeJoki',
            'salaries',
            'allWorkers',
            'totalOverallFarm',
            'totalOverallSalary'
        ))->with('isAdmin', auth()->check() ? auth()->user()->isAdmin() : false);
    }

    // Input Gaji Manual (Admin Only)
    public function storeSalary(Request $request)
    {
        if (auth()->check() && !auth()->user()->isAdmin()) {
            return back()->with('error', '❌ Akses ditolak! Hanya Admin yang bisa mengatur gaji.');
        }

        $request->validate([
            'worker_name' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        Salary::create([
            'worker_name' => $request->worker_name,
            'amount' => $request->amount,
            'notes' => $request->notes,
            'date' => $request->date ?: now()->toDateString(),
        ]);

        return back()->with('success', 'Catatan gaji Rp ' . number_format($request->amount, 0, ',', '.') . ' untuk ' . $request->worker_name . ' berhasil dicatat!');
    }

    // Edit Gaji Manual (Admin Only)
    public function updateSalary(Request $request, $id)
    {
        if (auth()->check() && !auth()->user()->isAdmin()) {
            return back()->with('error', '❌ Akses ditolak! Hanya Admin yang bisa mengatur gaji.');
        }

        $salary = Salary::findOrFail($id);
        $request->validate([
            'worker_name' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        $salary->update([
            'worker_name' => $request->worker_name,
            'amount' => $request->amount,
            'notes' => $request->notes,
            'date' => $request->date ?: $salary->date,
        ]);

        return back()->with('success', 'Data gaji ' . $salary->worker_name . ' berhasil diperbarui!');
    }

    // Hapus Gaji Manual (Admin Only)
    public function destroySalary($id)
    {
        if (auth()->check() && !auth()->user()->isAdmin()) {
            return back()->with('error', '❌ Akses ditolak! Hanya Admin yang bisa menghapus data gaji.');
        }

        $salary = Salary::findOrFail($id);
        $worker = $salary->worker_name;
        $salary->delete();

        return back()->with('success', 'Catatan gaji ' . $worker . ' berhasil dihapus!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kd_number' => 'required|numeric|unique:kingdoms,kd_number',
            'cooldown_hours' => 'nullable|numeric|min:0.1|max:48',
        ]);

        Kingdom::create([
            'kd_number' => $request->kd_number,
            'cooldown_hours' => $request->cooldown_hours ?? 4.0,
            'food' => $request->food ?? 0,
            'wood' => $request->wood ?? 0,
            'stone' => $request->stone ?? 0,
            'gold' => $request->gold ?? 0,
        ]);

        return back()->with('success', 'Kingdom & Stok KD #' . $request->kd_number . ' berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $kingdom = Kingdom::findOrFail($id);
        return view('edit_kingdom', compact('kingdom'));
    }

    public function update(Request $request, $id)
    {
        $kingdom = Kingdom::findOrFail($id);
        
        $request->validate([
            'cooldown_hours' => 'nullable|numeric|min:0.1|max:48',
        ]);

        $kingdom->update([
            'cooldown_hours' => $request->cooldown_hours ?? $kingdom->cooldown_hours ?? 4.0,
            'food' => $request->food ?? 0,
            'wood' => $request->wood ?? 0,
            'stone' => $request->stone ?? 0,
            'gold' => $request->gold ?? 0,
        ]);

        return redirect('/stok')->with('success', 'Stok & Pengaturan KD #' . $kingdom->kd_number . ' berhasil diupdate!');
    }

    public function destroy($id)
    {
        $kingdom = Kingdom::findOrFail($id);
        $kd_number = $kingdom->kd_number;
        $kingdom->delete();

        return redirect('/stok')->with('success', 'KD #' . $kd_number . ' beserta stoknya berhasil dihapus permanen!');
    }
}