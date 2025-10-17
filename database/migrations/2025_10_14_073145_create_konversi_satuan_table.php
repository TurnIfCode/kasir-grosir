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
        Schema::create('konversi_satuan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('barang_id');
            $table->foreign('barang_id')->references('id')->on('barang')->onDelete('cascade');

            // satuan utama dan satuan konversi
            $table->unsignedBigInteger('satuan_dasar_id');
            $table->unsignedBigInteger('satuan_konversi_id');
            $table->foreign('satuan_dasar_id')->references('id')->on('satuan')->onDelete('cascade');
            $table->foreign('satuan_konversi_id')->references('id')->on('satuan')->onDelete('cascade');

            // jumlah konversi, contoh: 1 pack = 12 bungkus
            $table->decimal('nilai_konversi', 10, 2)->default(1);

            // harga jual dan beli untuk satuan konversi
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);

            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konversi_satuan');
    }
};
