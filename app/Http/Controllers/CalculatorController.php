<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kingdom;

class CalculatorController extends Controller
{
    public function index()
    {
        return view('calculator');
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'calc_mode' => 'nullable|in:deduct_tax,target_net',
            'food' => 'nullable|numeric|min:0',
            'wood' => 'nullable|numeric|min:0',
            'stone' => 'nullable|numeric|min:0',
            'gold' => 'nullable|numeric|min:0',
            'tax' => 'required|numeric|min:0|max:99.9',
            'price_per_m' => 'nullable|numeric|min:0',
            'kd_number' => 'nullable|numeric|min:1',
            'stock_action' => 'nullable|in:add,set,deduct',
        ]);

        $mode = $request->input('calc_mode', 'deduct_tax');
        $taxPercentage = (float) $request->input('tax', 8);
        $taxDecimal = $taxPercentage / 100;
        $pricePerM = (float) $request->input('price_per_m', 0);
        $stockAction = $request->input('stock_action', 'add');
        $kdNumber = $request->filled('kd_number') ? (int) $request->input('kd_number') : null;

        $inputs = [
            'food' => (float) $request->input('food', 0),
            'wood' => (float) $request->input('wood', 0),
            'stone' => (float) $request->input('stone', 0),
            'gold' => (float) $request->input('gold', 0),
        ];

        // Validasi: minimal ada salah satu nilai RSS
        if (array_sum($inputs) <= 0) {
            return back()->withInput()->with('error', 'Masukkan minimal salah satu jumlah RSS (Food, Wood, Stone, atau Gold)!');
        }

        $results = [];
        $totalGross = 0;
        $totalNet = 0;
        $totalTax = 0;

        foreach ($inputs as $key => $amount) {
            if ($mode === 'deduct_tax') {
                // Mode 1: Total RSS - Pajak (Contoh: 100M - 20% = Diterima 80M)
                $gross = $amount;
                $taxVal = round($gross * $taxDecimal, 2);
                $net = round($gross - $taxVal, 2);
            } else {
                // Mode 2: Target Bersih (Contoh: Mau terima 100M bersih, harus kirim 125M)
                $net = $amount;
                $gross = $taxDecimal >= 1 ? $net : round($net / (1 - $taxDecimal), 2);
                $taxVal = round($gross - $net, 2);
            }

            $priceSubtotal = round($net * $pricePerM, 0);

            $results[$key] = [
                'input' => $amount,
                'gross' => $gross,
                'tax' => $taxVal,
                'net' => $net,
                'subtotal_price' => $priceSubtotal,
            ];

            $totalGross += $gross;
            $totalNet += $net;
            $totalTax += $taxVal;
        }

        $totalPrice = round($totalNet * $pricePerM, 0);

        // Jika nomor KD diisi -> Simpan / update ke Dashboard Stok (tabel kingdoms)
        $savedToDashboard = false;
        $stockMessage = '';

        if ($kdNumber) {
            $kingdom = Kingdom::firstOrNew(['kd_number' => $kdNumber]);
            if (!$kingdom->exists) {
                $kingdom->food = 0;
                $kingdom->wood = 0;
                $kingdom->stone = 0;
                $kingdom->gold = 0;
            }

            foreach (['food', 'wood', 'stone', 'gold'] as $res) {
                $val = $results[$res]['net'];

                if ($stockAction === 'add') {
                    $kingdom->$res += $val;
                } elseif ($stockAction === 'deduct') {
                    $deductVal = ($mode === 'target_net') ? $results[$res]['gross'] : $val;
                    $kingdom->$res = max(0, $kingdom->$res - $deductVal);
                } elseif ($stockAction === 'set') {
                    $kingdom->$res = $val;
                }
            }

            $kingdom->save();
            $savedToDashboard = true;

            $actionText = [
                'add' => 'ditambahkan ke',
                'set' => 'diset menjadi stok',
                'deduct' => 'dikurangi dari',
            ][$stockAction] ?? 'masuk ke';

            $stockMessage = "Kalkulasi berhasil dan data otomatis {$actionText} Dashboard Stok KD #{$kdNumber}!";
        } else {
            $stockMessage = "Kalkulasi berhasil. (Mode Simulasi: KD tidak diisi sehingga tidak disimpan ke Dashboard Stok).";
        }

        return back()->withInput()->with([
            'result' => true,
            'calc_mode' => $mode,
            'tax' => $taxPercentage,
            'price_per_m' => $pricePerM,
            'kd_number' => $kdNumber,
            'stock_action' => $stockAction,
            'saved_to_dashboard' => $savedToDashboard,
            'stock_message' => $stockMessage,
            'results' => $results,
            'total_gross' => round($totalGross, 2),
            'total_net' => round($totalNet, 2),
            'total_tax' => round($totalTax, 2),
            'total_price' => $totalPrice,
            'success' => $stockMessage,
        ]);
    }
}