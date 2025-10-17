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
        Schema::create('penjualan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_penjualan', 50)->unique();
            $table->date('tanggal_penjualan');
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('ppn', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('jenis_pembayaran', ['tunai', 'non_tunai', 'campuran'])->default('tunai');
            $table->decimal('dibayar', 15, 2)->default(0);
            $table->decimal('kembalian', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->enum('status', ['draft', 'selesai', 'batal'])->default('draft');
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();

            $table->index('tanggal_penjualan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
