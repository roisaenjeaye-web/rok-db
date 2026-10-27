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
        Schema::create('kingdoms', function (Blueprint $table) {
            $table->id();
            $table->integer('kd_number')->unique(); // Nomor KD, misal: 3124
            // Pake decimal biar bisa simpan koma, misal 150.5 M
            $table->decimal('food', 10, 2)->default(0); 
            $table->decimal('wood', 10, 2)->default(0);
            $table->decimal('stone', 10, 2)->default(0);
            $table->decimal('gold', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kingdoms');
    }
};
