<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paket', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->integer('total_qty')->unsigned();
            $table->decimal('harga', 15, 2);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');

            // audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable()->default(null)->useCurrentOnUpdate();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket');
    }
};
