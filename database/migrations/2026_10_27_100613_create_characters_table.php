<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('characters', function (Blueprint $table) {
        $table->id();
        // Relasi ke tabel game_accounts (Satu email punya banyak char)
        $table->foreignId('game_account_id')->constrained('game_accounts')->onDelete('cascade');
        
        $table->integer('kd_number'); // KD tempat char ini berada (contoh: 2357 atau 1111)
        $table->string('ign')->nullable(); // In-Game Name (contoh: Farm Rois 01)
        $table->enum('status', ['aktif', 'banned', 'zeroed'])->default('aktif');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
