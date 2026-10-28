<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Kingdom; // Tambahkan ini biar bisa manggil tabel kingdoms

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at', 'desc')->get();
        return view('riwayat', compact('orders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'buyer_name' => 'required|string',
            'kd_number' => 'required|numeric',
        ]);

        Order::create([
            'buyer_name' => $request->buyer_name,
            'kd_number' => $request->kd_number,
            'rss_type' => $request->rss_type,
            'amount_requested' => $request->amount_requested,
            'amount_to_send' => $request->amount_to_send,
            'tax_percentage' => $request->tax_percentage,
            'total_price' => $request->total_price,
            'status' => 'pending', 
        ]);

        return redirect('/riwayat')->with('success', 'Pesanan atas nama ' . $request->buyer_name . ' berhasil dicatat!');
    }

    // FUNGSI BARU: Tandai selesai + Kurangi stok KD otomatis
    public function complete($id)
    {
        $order = Order::findOrFail($id);

        if ($order->status == 'pending') {
            // 1. Cari KD yang sesuai dengan pesanan ini
            $kingdom = Kingdom::where('kd_number', $order->kd_number)->first();

            // 2. Kalau KD-nya ketemu di database, kurangi stoknya
            if ($kingdom) {
                // Ubah tipe RSS jadi huruf kecil biar sesuai sama nama kolom di tabel (food, wood, stone, gold)
                $type = strtolower($order->rss_type); 
                
                // Pastikan tipenya valid, lalu kurangi stok dengan nominal kotor (amount_to_send)
                if (in_array($type, ['food', 'wood', 'stone', 'gold'])) {
                    $kingdom->$type -= $order->amount_to_send;
                    $kingdom->save();
                }
                
                $pesan = 'Pesanan ' . $order->buyer_name . ' lunas! Stok ' . $order->rss_type . ' di KD #' . $order->kd_number . ' otomatis dikurangi ' . $order->amount_to_send . 'M.';
            } else {
                $pesan = 'Pesanan lunas, tapi peringatan: KD #' . $order->kd_number . ' tidak ditemukan di data stok, jadi stok tidak dipotong otomatis.';
            }

            // 3. Ubah status pesanan jadi completed
            $order->update(['status' => 'completed']);

            return back()->with('success', $pesan);
        }

        return back();
    }

    // Hapus Riwayat Pesanan
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $buyer = $order->buyer_name;
        $order->delete();

        return back()->with('success', "Pesanan atas nama {$buyer} berhasil dihapus dari Buku Kas!");
    }
}