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
        Schema::create('barang_pengganti_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('barang_id');
            $table->integer('qty');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('supplier');
            $table->foreign('barang_id')->references('id')->on('barang');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_pengganti_sales');
    }
};
