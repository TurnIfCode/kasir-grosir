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
        Schema::create('kas_saldo_transaksi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kas_saldo_id')->nullable();
            $table->foreign('kas_saldo_id')->references('id')->on('kas_saldo')->onDelete('set null')->onUpdate('cascade');
            $table->string('tipe', 50)->nullable();
            $table->decimal('saldo_awal', 15, 2)->nullable();
            $table->decimal('saldo_akhir', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_saldo_transaksi');
    }
};
