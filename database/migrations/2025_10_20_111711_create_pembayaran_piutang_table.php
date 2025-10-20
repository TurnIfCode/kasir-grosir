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
        Schema::create('pembayaran_piutang', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('piutang_id');
            $table->unsignedBigInteger('pelanggan_id');
            $table->date('tanggal_bayar');
            $table->decimal('nominal', 15, 2);
            $table->string('metode', 50)->default('Tunai');
            $table->string('sumber_kas', 100)->default('Kas Utama');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('piutang_id')->references('id')->on('piutang')->onDelete('cascade');
            $table->foreign('pelanggan_id')->references('id')->on('pelanggan')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_piutang');
    }
};
