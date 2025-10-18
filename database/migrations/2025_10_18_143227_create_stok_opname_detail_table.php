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
        Schema::create('stok_opname_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stok_opname_id');
            $table->unsignedBigInteger('barang_id');
            $table->decimal('stok_sistem', 10, 2)->default(0);
            $table->decimal('stok_fisik', 10, 2)->default(0);
            $table->decimal('selisih', 10, 2)->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('stok_opname_id')->references('id')->on('stok_opname')->onDelete('cascade');
            $table->foreign('barang_id')->references('id')->on('barang')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_opname_detail');
    }
};
