<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\KonversiSatuanController;
use App\Http\Controllers\HargaBarangController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\LaporanController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/login', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('login');
})->name('login');

Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('user')->group(function () {
        Route::get('/add', [UserController::class, 'add'])->name('user.add');
        Route::get('/data', [UserController::class, 'data'])->name('user.data');
        Route::post('/store', [UserController::class, 'store'])->name('user.store');
        Route::get('/{id}/find', [UserController::class, 'find'])->name('user.find');
        Route::put('/{id}/update', [UserController::class, 'update'])->name('user.update');
        Route::delete('/{id}/delete', [UserController::class, 'delete'])->name('user.delete');
    });

    Route::prefix('kategori')->group(function () {
        Route::get('/add', [KategoriController::class, 'add'])->name('kategori.add');
        Route::get('/data', [KategoriController::class, 'data'])->name('kategori.data');
        Route::post('/store', [KategoriController::class, 'store'])->name('kategori.store');
        Route::get('/{id}/find', [KategoriController::class, 'find'])->name('kategori.find');
        Route::put('/{id}/update', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/{id}/delete', [KategoriController::class, 'delete'])->name('kategori.delete');
    });

    Route::prefix('barang')->group(function () {
        Route::get('/', [BarangController::class, 'index'])->name('barang.index');
        Route::get('/add', [BarangController::class, 'add'])->name('barang.add');
        Route::get('/data', [BarangController::class, 'data'])->name('barang.data');
        Route::post('/store', [BarangController::class, 'store'])->name('barang.store');
        Route::get('/{id}/find', [BarangController::class, 'find'])->name('barang.find');
        Route::put('/{id}/update', [BarangController::class, 'update'])->name('barang.update');
        Route::delete('/{id}/delete', [BarangController::class, 'delete'])->name('barang.delete');

        // API endpoints untuk pembelian
        Route::get('/search', [BarangController::class, 'search'])->name('barang.search');
        Route::get('/{id}/satuan', [BarangController::class, 'getSatuan'])->name('barang.satuan');
    });

    Route::prefix('satuan')->group(function () {
        Route::get('/add', [SatuanController::class, 'add'])->name('satuan.add');
        Route::get('/data', [SatuanController::class, 'data'])->name('satuan.data');
        Route::post('/store', [SatuanController::class, 'store'])->name('satuan.store');
        Route::get('/{id}/find', [SatuanController::class, 'find'])->name('satuan.find');
        Route::put('/{id}/update', [SatuanController::class, 'update'])->name('satuan.update');
        Route::delete('/{id}/delete', [SatuanController::class, 'delete'])->name('satuan.delete');
    });

    Route::prefix('konversi-satuan')->group(function () {
        Route::get('/', [KonversiSatuanController::class, 'index'])->name('konversi-satuan.index');
        Route::get('/add', [KonversiSatuanController::class, 'add'])->name('konversi-satuan.add');
        Route::get('/data', [KonversiSatuanController::class, 'data'])->name('konversi-satuan.data');
        Route::post('/store', [KonversiSatuanController::class, 'store'])->name('konversi-satuan.store');
        Route::get('/{id}/find', [KonversiSatuanController::class, 'find'])->name('konversi-satuan.find');
        Route::put('/{id}/update', [KonversiSatuanController::class, 'update'])->name('konversi-satuan.update');
        Route::delete('/{id}/delete', [KonversiSatuanController::class, 'delete'])->name('konversi-satuan.delete');
    });

    Route::prefix('harga-barang')->group(function () {
        Route::get('/', [HargaBarangController::class, 'index'])->name('harga-barang.index');
        Route::get('/create', [HargaBarangController::class, 'create'])->name('harga-barang.create');
        Route::post('/', [HargaBarangController::class, 'store'])->name('harga-barang.store');
        Route::get('/{id}/edit', [HargaBarangController::class, 'edit'])->name('harga-barang.edit');
        Route::put('/{id}', [HargaBarangController::class, 'update'])->name('harga-barang.update');
        Route::delete('/{id}', [HargaBarangController::class, 'destroy'])->name('harga-barang.destroy');
        Route::get('/data', [HargaBarangController::class, 'data'])->name('harga-barang.data');
        Route::get('/get-harga', [HargaBarangController::class, 'getHarga'])->name('harga-barang.get-harga');
    });

    Route::prefix('supplier')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('supplier.index');
        Route::get('/add', [SupplierController::class, 'add'])->name('supplier.add');
        Route::get('/data', [SupplierController::class, 'data'])->name('supplier.data');
        Route::post('/store', [SupplierController::class, 'store'])->name('supplier.store');
        Route::get('/{id}/find', [SupplierController::class, 'find'])->name('supplier.find');
        Route::put('/{id}/update', [SupplierController::class, 'update'])->name('supplier.update');
        Route::delete('/{id}/delete', [SupplierController::class, 'delete'])->name('supplier.delete');
        Route::get('/generate-kode', [SupplierController::class, 'generateKode'])->name('supplier.generate-kode');
    });

    Route::prefix('pelanggan')->group(function () {
        Route::get('/', [PelangganController::class, 'index'])->name('pelanggan.index');
        Route::get('/add', [PelangganController::class, 'add'])->name('pelanggan.add');
        Route::get('/data', [PelangganController::class, 'data'])->name('pelanggan.data');
        Route::post('/store', [PelangganController::class, 'store'])->name('pelanggan.store');
        Route::get('/{id}/find', [PelangganController::class, 'find'])->name('pelanggan.find');
        Route::put('/{id}/update', [PelangganController::class, 'update'])->name('pelanggan.update');
        Route::delete('/{id}/delete', [PelangganController::class, 'delete'])->name('pelanggan.delete');
        Route::get('/generate-kode', [PelangganController::class, 'generateKode'])->name('pelanggan.generate-kode');
    });

    Route::prefix('pembelian')->group(function () {
        Route::get('/', [\App\Http\Controllers\Transaksi\PembelianController::class, 'index'])->name('pembelian.index');
        Route::get('/data', [\App\Http\Controllers\Transaksi\PembelianController::class, 'data'])->name('pembelian.data');
        Route::get('/create', [\App\Http\Controllers\Transaksi\PembelianController::class, 'create'])->name('pembelian.create');
        Route::post('/', [\App\Http\Controllers\Transaksi\PembelianController::class, 'store'])->name('pembelian.store');

        // AJAX endpoints menggunakan API dari master-barang

        Route::get('/{id}', [\App\Http\Controllers\Transaksi\PembelianController::class, 'show'])->name('pembelian.show');
        Route::get('/{id}/edit', [\App\Http\Controllers\Transaksi\PembelianController::class, 'edit'])->name('pembelian.edit');
        Route::put('/{id}', [\App\Http\Controllers\Transaksi\PembelianController::class, 'update'])->name('pembelian.update');
        Route::delete('/{id}', [\App\Http\Controllers\Transaksi\PembelianController::class, 'destroy'])->name('pembelian.destroy');
        Route::patch('/{id}/status', [\App\Http\Controllers\Transaksi\PembelianController::class, 'updateStatus'])->name('pembelian.update-status');
    });

    Route::prefix('penjualan')->group(function () {
        Route::get('/', [PenjualanController::class, 'index'])->name('penjualan.index');
        Route::get('/data', [PenjualanController::class, 'data'])->name('penjualan.data');
        Route::get('/create', [PenjualanController::class, 'create'])->name('penjualan.create');
        Route::post('/', [PenjualanController::class, 'store'])->name('penjualan.store');

        // API endpoint for microservice
        Route::post('/api', [\App\Http\Controllers\Api\PenjualanApiController::class, 'store'])->name('penjualan.api.store');

        // AJAX endpoints
        Route::get('/autocomplete-barang', [PenjualanController::class, 'autocompleteBarang'])->name('penjualan.autocomplete-barang');
        Route::get('/barang/{barangId}/satuan', [PenjualanController::class, 'getSatuanByBarang'])->name('penjualan.barang.satuan');
        Route::get('/barang/{barangId}/harga/{satuanId}', [PenjualanController::class, 'getHargaByBarangSatuan'])->name('penjualan.barang.harga');
        Route::get('/barang/{barangId}/harga/{satuanId}/default', [PenjualanController::class, 'getHargaByBarangSatuanDefault'])->name('penjualan.barang.harga.default');

        Route::get('/{id}', [PenjualanController::class, 'show'])->name('penjualan.show');
        Route::get('/{id}/edit', [PenjualanController::class, 'edit'])->name('penjualan.edit');
        Route::put('/{id}', [PenjualanController::class, 'update'])->name('penjualan.update');
        Route::delete('/{id}', [PenjualanController::class, 'destroy'])->name('penjualan.destroy');
        Route::patch('/{id}/status', [PenjualanController::class, 'updateStatus'])->name('penjualan.update-status');
    });

    // Laporan Routes
    Route::prefix('laporan')->group(function () {
        Route::get('/pembelian', [\App\Http\Controllers\LaporanPembelianController::class, 'index'])->name('laporan.pembelian');
        Route::get('/pembelian/data', [\App\Http\Controllers\LaporanPembelianController::class, 'data'])->name('laporan.pembelian.data');
        Route::get('/pembelian/export-pdf', [\App\Http\Controllers\LaporanPembelianController::class, 'exportPDF'])->name('laporan.pembelian.export_pdf');

        Route::get('/penjualan', [\App\Http\Controllers\LaporanPenjualanController::class, 'index'])->name('laporan.penjualan');
        Route::get('/penjualan/data', [\App\Http\Controllers\LaporanPenjualanController::class, 'data'])->name('laporan.penjualan.data');
        Route::get('/penjualan/export-pdf', [\App\Http\Controllers\LaporanPenjualanController::class, 'exportPDF'])->name('laporan.penjualan.export_pdf');

        Route::get('/laba-rugi', [\App\Http\Controllers\LaporanLabaRugiController::class, 'index'])->name('laporan.laba-rugi');
        Route::get('/laba-rugi/data', [\App\Http\Controllers\LaporanLabaRugiController::class, 'data'])->name('laporan.laba-rugi.data');
        Route::get('/laba-rugi/export-pdf', [\App\Http\Controllers\LaporanLabaRugiController::class, 'exportPDF'])->name('laporan.laba-rugi.export_pdf');

        Route::get('/stok-barang', [\App\Http\Controllers\LaporanStokController::class, 'index'])->name('laporan.stok-barang');
        Route::get('/stok-barang/data', [\App\Http\Controllers\LaporanStokController::class, 'getData'])->name('laporan.stok-barang.data');
        Route::get('/stok-barang/export-pdf', [\App\Http\Controllers\LaporanStokController::class, 'exportPdf'])->name('laporan.stok-barang.export_pdf');
        Route::get('/stok-barang/search', [\App\Http\Controllers\LaporanStokController::class, 'searchBarang'])->name('laporan.stok-barang.search');

        Route::get('/rekap-harian', [\App\Http\Controllers\LaporanRekapHarianController::class, 'index'])->name('laporan.rekap_harian');
        Route::get('/rekap-harian/data', [\App\Http\Controllers\LaporanRekapHarianController::class, 'getData'])->name('laporan.rekap_harian.data');
        Route::get('/rekap-harian/export-pdf', [\App\Http\Controllers\LaporanRekapHarianController::class, 'exportPdf'])->name('laporan.rekap_harian.export_pdf');

        Route::get('/rekap_bulanan', [\App\Http\Controllers\LaporanRekapBulananController::class, 'index'])->name('laporan.rekap_bulanan');
        Route::get('/rekap_bulanan/data', [\App\Http\Controllers\LaporanRekapBulananController::class, 'getData'])->name('laporan.rekap_bulanan.data');
        Route::get('/rekap_bulanan/export-pdf', [\App\Http\Controllers\LaporanRekapBulananController::class, 'exportPdf'])->name('laporan.rekap_bulanan.export_pdf');

        Route::get('/stok-opname', [\App\Http\Controllers\LaporanStokOpnameController::class, 'index'])->name('laporan.stok-opname');
        Route::get('/stok-opname/data', [\App\Http\Controllers\LaporanStokOpnameController::class, 'getData'])->name('laporan.stok-opname.data');
        Route::get('/stok-opname/{id}', [\App\Http\Controllers\LaporanStokOpnameController::class, 'show'])->name('laporan.stok-opname.show');
        Route::get('/stok-opname/{id}/export-pdf', [\App\Http\Controllers\LaporanStokOpnameController::class, 'exportPDF'])->name('laporan.stok-opname.export-pdf');
    });

    // Stok Opname Routes
    Route::resource('stok-opname', \App\Http\Controllers\StokOpnameController::class);
    Route::post('stok-opname/{id}/update-status', [\App\Http\Controllers\StokOpnameController::class, 'updateStatus'])->name('stok-opname.updateStatus');



});
