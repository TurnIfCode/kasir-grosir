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
        Schema::create('penjualan_pembayaran', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('penjualan_id');
            $table->foreign('penjualan_id')->references('id')->on('penjualan')->onDelete('cascade');

            $table->enum('metode', ['tunai', 'transfer', 'qris', 'debit', 'kredit'])->default('tunai');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();

            $table->index('penjualan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_pembayaran');
    }
};
