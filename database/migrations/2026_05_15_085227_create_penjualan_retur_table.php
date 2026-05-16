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
        Schema::create('penjualan_retur', function (Blueprint $table) {
            $table->id();
            $table->string('kode_retur', 100);
            $table->string('kode_penjualan', 100);
            $table->decimal('grand_total_penjualan', 15, 2)->default(0);
            $table->decimal('grand_total_retur', 15, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_retur');
    }
};
