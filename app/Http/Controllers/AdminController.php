<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Character;

class AdminController extends Controller
{
    /**
     * Guard: pastikan yang akses adalah admin
     */
    private function requireAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, '❌ Akses ditolak! Halaman ini hanya untuk Admin.');
        }
    }

    /**
     * Halaman utama admin: manajemen user + adjust macul
     */
    public function index()
    {
        $this->requireAdmin();

        $users      = User::orderBy('is_admin', 'desc')->orderBy('name')->get();
        $characters = Character::with('gameAccount')->orderBy('kd_number')->get();
        $workers    = User::where('is_admin', false)->pluck('name')->unique()->values()->toArray();

        return view('admin', compact('users', 'characters', 'workers'));
    }

    /**
     * Hapus user (tidak boleh hapus diri sendiri)
     */
    public function deleteUser($id)
    {
        $this->requireAdmin();

        if ((int) $id === auth()->id()) {
            return back()->with('error', '❌ Tidak bisa menghapus akun sendiri!');
        }

        $user = User::findOrFail($id);
        $name = $user->name;
        $user->delete();

        return back()->with('success', "✅ User {$name} berhasil dihapus!");
    }

    /**
     * Toggle status admin / operator untuk user tertentu
     */
    public function toggleAdmin($id)
    {
        $this->requireAdmin();

        if ((int) $id === auth()->id()) {
            return back()->with('error', '❌ Tidak bisa mengubah status admin sendiri!');
        }

        $user = User::findOrFail($id);
        $user->is_admin = !$user->is_admin;
        $user->save();

        $status = $user->is_admin ? 'Admin' : 'Operator';
        return back()->with('success', "✅ {$user->name} sekarang berstatus {$status}!");
    }

    /**
     * Reset password user tertentu
     */
    public function resetPassword(Request $request, $id)
    {
        $this->requireAdmin();

        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user = User::findOrFail($id);
        $user->password = bcrypt($request->password);
        $user->save();

        return back()->with('success', "✅ Password user {$user->name} berhasil diubah ke: {$request->password}");
    }

    /**
     * Adjust jumlah macul (farm_count) per karakter per worker
     * Action: increment | decrement | reset | set
     */
    public function adjustFarm(Request $request, $charId)
    {
        $this->requireAdmin();

        $request->validate([
            'action' => 'required|in:increment,decrement,reset,set',
            'worker' => 'required|string|max:100',
            'count'  => 'nullable|integer|min:0',
        ]);

        $char   = Character::findOrFail($charId);
        $action = $request->action;
        $worker = $request->worker;
        $count  = (int) $request->input('count', 0);

        $char->adjustFarmLog($worker, $action, $count);

        $msg = "✅ Log macul {$char->ign} untuk {$worker} diupdate! Total: {$char->farm_count}x";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => $msg,
                'farm_count'   => $char->farm_count,
                'farmer_stats' => $char->farmer_stats,
            ]);
        }

        return back()->with('success', $msg);
    }
}
