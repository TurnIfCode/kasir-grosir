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
        Schema::create('mutasi_stok', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barang_id');
            $table->integer('qty');
            $table->enum('dari_gudang', ['GS', 'BS']);
            $table->enum('ke_gudang', ['GS', 'BS']);
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('barang_id')->references('id')->on('barang');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_stok');
    }
};
