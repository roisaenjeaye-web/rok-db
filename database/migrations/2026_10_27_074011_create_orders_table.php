<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->string('buyer_name');
        $table->integer('kd_number');
        $table->string('rss_type'); // Food, Wood, Stone, Gold
        $table->decimal('amount_requested', 10, 2); // Bersih yang diminta (M)
        $table->decimal('amount_to_send', 10, 2); // Kotor yang harus dikirim dari farm (M)
        $table->integer('tax_percentage')->default(8); // Pajak Trading Post
        $table->decimal('total_price', 15, 2); // Harga rupiah
        $table->enum('status', ['pending', 'completed'])->default('pending');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
