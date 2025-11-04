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
        Schema::create('paket_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paket_id');
            $table->unsignedBigInteger('barang_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('paket_id', 'idx_paket_detail_paket');
            $table->index('barang_id', 'idx_paket_detail_barang');
            $table->foreign('paket_id', 'fk_paket_detail_paket')->references('id')->on('paket')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('barang_id', 'fk_paket_detail_barang')->references('id')->on('barang')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('created_by', 'fk_paket_detail_created_by')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('updated_by', 'fk_paket_detail_updated_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_detail');
    }
};
