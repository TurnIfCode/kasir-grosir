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
        Schema::create('profil_toko', function (Blueprint $table) {
            $table->id();
            $table->string('nama_toko', 150);                // Nama resmi toko
            $table->string('slogan', 255)->nullable();       // Tagline atau motto toko
            $table->text('alamat')->nullable();               // Alamat lengkap toko
            $table->string('kota', 100)->nullable();         // Kota lokasi toko
            $table->string('provinsi', 100)->nullable();     // Provinsi toko
            $table->string('kode_pos', 20)->nullable();      // Kode pos toko
            $table->string('no_telp', 50)->nullable();       // Nomor telepon / WA
            $table->string('email', 150)->nullable();        // Email toko (opsional)
            $table->string('website', 150)->nullable();      // Website (jika ada)
            $table->string('npwp', 50)->nullable();          // NPWP (opsional)
            $table->string('logo', 255)->nullable();         // Path file logo toko
            $table->text('deskripsi')->nullable();            // Deskripsi singkat toko
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profil_toko');
    }
};
