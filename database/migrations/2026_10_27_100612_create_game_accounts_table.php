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
    Schema::create('game_accounts', function (Blueprint $table) {
        $table->id();
        $table->string('email')->unique(); // Contoh: roisalif113@gmail.com
        $table->string('password')->nullable(); // Opsional kalau mau nyatet pass
        $table->text('notes')->nullable(); // Catatan tambahan (misal: "Kena limit captcha")
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_accounts');
    }
};
