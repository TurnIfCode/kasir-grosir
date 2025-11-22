<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;

use App\Http\Controllers\JenisBarangController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\KonversiSatuanController;
use App\Http\Controllers\HargaBarangController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\KasSaldoController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanStokController;
use App\Http\Controllers\Master\PaketController;

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

    Route::prefix('paket')->group(function () {
        Route::get('/', [PaketController::class, 'index'])->name('paket.index');
        Route::get('/add', [PaketController::class, 'add'])->name('paket.create');
        Route::get('/data', [PaketController::class, 'data'])->name('paket.data');
        Route::post('/store', [PaketController::class, 'store'])->name('paket.store');
        Route::get('/{id}/find', [PaketController::class, 'find'])->name('paket.find');
        Route::get('/{id}/edit', [PaketController::class, 'edit'])->name('paket.edit');
        Route::put('/{id}/update', [PaketController::class, 'update'])->name('paket.update');
        Route::delete('/{id}/delete', [PaketController::class, 'delete'])->name('paket.delete');
    });
    
    Route::get('/barang/search', [App\Http\Controllers\BarangController::class, 'search'])->name('barang.search');



    Route::prefix('jenis-barang')->group(function () {
        Route::get('/add', [JenisBarangController::class, 'add'])->name('jenis_barang.add');
        Route::get('/data', [JenisBarangController::class, 'data'])->name('jenis_barang.data');
        Route::post('/store', [JenisBarangController::class, 'store'])->name('jenis_barang.store');
        Route::get('/{id}/find', [JenisBarangController::class, 'find'])->name('jenis_barang.find');
        Route::put('/{id}/update', [JenisBarangController::class, 'update'])->name('jenis_barang.update');
        Route::delete('/{id}/delete', [JenisBarangController::class, 'delete'])->name('jenis_barang.delete');
        Route::get('/search/kategori', [JenisBarangController::class, 'searchKategori'])->name('jenis_barang.search.kategori');
        Route::get('/search/barang', [JenisBarangController::class, 'searchBarang'])->name('jenis_barang.search.barang');
        Route::get('/search/supplier', [JenisBarangController::class, 'searchSupplier'])->name('jenis_barang.search.supplier');
    });

    Route::prefix('barang')->group(function () {
        Route::get('/', [BarangController::class, 'index'])->name('barang.index');
        Route::get('/add', [BarangController::class, 'add'])->name('barang.add');
        Route::get('/data', [BarangController::class, 'data'])->name('barang.data');
        Route::post('/store', [BarangController::class, 'store'])->name('barang.store');
        Route::get('/{id}/find', [BarangController::class, 'find'])->name('barang.find');
        Route::put('/{id}/update', [BarangController::class, 'update'])->name('barang.update');
        Route::delete('/{id}/delete', [BarangController::class, 'delete'])->name('barang.delete');

        // API endpoints untuk pembelian dan paket
        Route::get('/search', [BarangController::class, 'search'])->name('barang.search');
        Route::get('/{id}/satuan', [BarangController::class, 'getSatuan'])->name('barang.satuan');
        Route::get('/{barangId}/harga/{satuanId}', [BarangController::class, 'getHarga'])->name('barang.harga');

        // Stok Minimum
        Route::get('/stok-minimum', [\App\Http\Controllers\StokMinimumController::class, 'index'])->name('barang.stok-minimum.index');
        Route::get('/stok-minimum/data', [\App\Http\Controllers\StokMinimumController::class, 'data'])->name('barang.stok-minimum.data');
        Route::post('/stok-minimum/store', [\App\Http\Controllers\StokMinimumController::class, 'store'])->name('barang.stok-minimum.store');
        Route::delete('/stok-minimum/{id}/delete', [\App\Http\Controllers\StokMinimumController::class, 'delete'])->name('barang.stok-minimum.delete');
        Route::get('/{barangId}/stok-minimum', [\App\Http\Controllers\StokMinimumController::class, 'getByBarang'])->name('barang.stok-minimum.get');
    });

    Route::prefix('satuan')->group(function () {
        Route::get('/add', [SatuanController::class, 'add'])->name('satuan.add');
        Route::get('/data', [SatuanController::class, 'data'])->name('satuan.data');
        Route::post('/store', [SatuanController::class, 'store'])->name('satuan.store');
        Route::get('/{id}/find', [SatuanController::class, 'find'])->name('satuan.find');
        Route::put('/{id}/update', [SatuanController::class, 'update'])->name('satuan.update');
        Route::delete('/{id}/delete', [SatuanController::class, 'delete'])->name('satuan.delete');
        Route::get('/search', [SatuanController::class, 'search'])->name('satuan.search');
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
        Route::get('/get-tipe-harga', [HargaBarangController::class, 'getTipeHarga'])->name('harga-barang.get-tipe-harga');
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
        Route::get('/search', [SupplierController::class, 'search'])->name('supplier.search');
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
        Route::get('/search', [PelangganController::class, 'search'])->name('pelanggan.search');
    });

    Route::prefix('pembelian')->group(function () {
        Route::get('/', [\App\Http\Controllers\Transaksi\PembelianController::class, 'index'])->name('pembelian.index');
        Route::get('/data', [\App\Http\Controllers\Transaksi\PembelianController::class, 'data'])->name('pembelian.data');
        Route::get('/create', [\App\Http\Controllers\Transaksi\PembelianController::class, 'create'])->name('pembelian.create');
        Route::post('/', [\App\Http\Controllers\Transaksi\PembelianController::class, 'store'])->name('pembelian.store');

        // AJAX endpoints menggunakan API dari master-barang
        Route::get('/autocomplete-barang', [\App\Http\Controllers\Transaksi\PembelianController::class, 'autocompleteBarang'])->name('pembelian.autocomplete-barang');

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
        Route::get('/barang/{barangId}/satuan/{satuanId}/tipe-harga', [PenjualanController::class, 'getTipeHargaByBarangSatuan'])->name('penjualan.barang.tipe-harga');
        Route::get('/barang/{barangId}/harga/{satuanId}', [PenjualanController::class, 'getHargaByBarangSatuan'])->name('penjualan.barang.harga');
        Route::get('/barang/{barangId}/harga/{satuanId}/default', [PenjualanController::class, 'getHargaByBarangSatuanDefault'])->name('penjualan.barang.harga.default');
        Route::get('/barang/{barangId}/harga-barang-info', [PenjualanController::class, 'getHargaBarangInfo'])->name('penjualan.barang.harga-barang-info');
        Route::get('/get-paket-barang/{barangId}', [PenjualanController::class, 'getPaketBarang'])->name('penjualan.get-paket-barang');
        Route::post('/calculate-subtotal', [PenjualanController::class, 'calculateSubtotal'])->name('penjualan.calculate-subtotal');

        Route::get('/{id}', [PenjualanController::class, 'show'])->name('penjualan.show');
        Route::get('/{id}/print', [PenjualanController::class, 'print'])->name('penjualan.print');
        Route::get('/{id}/edit', [PenjualanController::class, 'edit'])->name('penjualan.edit');
        Route::put('/{id}', [PenjualanController::class, 'update'])->name('penjualan.update');
        Route::delete('/{id}', [PenjualanController::class, 'destroy'])->name('penjualan.destroy');
        Route::patch('/{id}/status', [PenjualanController::class, 'updateStatus'])->name('penjualan.update-status');
    });

    // Laporan Routes
    Route::prefix('laporan')->group(function () {
        Route::get('/pembelian', [\App\Http\Controllers\LaporanPembelianController::class, 'index'])->name('laporan.pembelian');
        Route::get('/pembelian/data', [\App\Http\Controllers\LaporanPembelianController::class, 'data'])->name('laporan.pembelian.data');
        Route::get('/pembelian/ringkasan', [\App\Http\Controllers\LaporanPembelianController::class, 'getRingkasan'])->name('laporan.pembelian.ringkasan');
        Route::get('/pembelian/chart', [\App\Http\Controllers\LaporanPembelianController::class, 'getChartData'])->name('laporan.pembelian.chart');
        Route::get('/pembelian/export-pdf', [\App\Http\Controllers\LaporanPembelianController::class, 'exportPDF'])->name('laporan.pembelian.export_pdf');
        Route::get('/pembelian-per-supplier', [\App\Http\Controllers\LaporanPembelianController::class, 'indexPerSupplier'])->name('laporan.pembelian-per-supplier');
        Route::get('/pembelian-per-supplier/data', [\App\Http\Controllers\LaporanPembelianController::class, 'dataPerSupplier'])->name('laporan.pembelian-per-supplier.data');
        Route::get('/pembelian-per-supplier/autocomplete-supplier', [\App\Http\Controllers\LaporanPembelianController::class, 'autocompleteSupplier'])->name('laporan.pembelian-per-supplier.autocomplete-supplier');
        Route::get('/pembelian-per-supplier/export-pdf', [\App\Http\Controllers\LaporanPembelianController::class, 'exportPDFPerSupplier'])->name('laporan.pembelian-per-supplier.export_pdf');

        Route::get('/penjualan', [\App\Http\Controllers\LaporanPenjualanController::class, 'index'])->name('laporan.penjualan');
        Route::get('/penjualan/data', [\App\Http\Controllers\LaporanPenjualanController::class, 'data'])->name('laporan.penjualan.data');
        Route::get('/penjualan/export-pdf', [\App\Http\Controllers\LaporanPenjualanController::class, 'exportPDF'])->name('laporan.penjualan.export_pdf');

        Route::get('/laba-rugi', [\App\Http\Controllers\LaporanLabaRugiController::class, 'index'])->name('laporan.laba-rugi');
        Route::get('/laba-rugi/data', [\App\Http\Controllers\LaporanLabaRugiController::class, 'data'])->name('laporan.laba-rugi.data');
        Route::get('/laba-rugi/export-pdf', [\App\Http\Controllers\LaporanLabaRugiController::class, 'exportPDF'])->name('laporan.laba-rugi.export_pdf');

        Route::get('/stok', [\App\Http\Controllers\LaporanStokController::class, 'index'])->name('laporan.stok');
        Route::get('/stok/stok-akhir', [\App\Http\Controllers\LaporanStokController::class, 'stokAkhir'])->name('laporan.stok_akhir');
        Route::get('/stok/stok-akhir/data', [\App\Http\Controllers\LaporanStokController::class, 'getDataStokAkhir'])->name('laporan.stok_akhir.data');
        Route::get('/stok/stok-akhir/ringkasan', [\App\Http\Controllers\LaporanStokController::class, 'getRingkasanStokAkhir'])->name('laporan.stok_akhir.ringkasan');
        Route::get('/stok/stok-akhir/export-pdf', [\App\Http\Controllers\LaporanStokController::class, 'exportStokAkhirPdf'])->name('laporan.stok_akhir.export_pdf');
        Route::get('/stok/stok-akhir/export-excel', [\App\Http\Controllers\LaporanStokController::class, 'exportStokAkhirExcel'])->name('laporan.stok_akhir.export_excel');

        Route::get('/stok/stok-masuk-keluar', [\App\Http\Controllers\LaporanStokController::class, 'stokMasukKeluar'])->name('laporan.stok-masuk-keluar');
        Route::get('/stok/stok-masuk-keluar/data', [\App\Http\Controllers\LaporanStokController::class, 'getDataStokMasukKeluar'])->name('laporan.stok-masuk-keluar.data');
        Route::get('/stok/stok-masuk-keluar/export-pdf', [\App\Http\Controllers\LaporanStokController::class, 'exportStokMasukKeluarPdf'])->name('laporan.stok-masuk-keluar.export_pdf');

        Route::get('/stok/barang-hampir-habis', [\App\Http\Controllers\LaporanStokController::class, 'barangHampirHabis'])->name('laporan.barang-hampir-habis');
        Route::get('/stok/barang-hampir-habis/data', [\App\Http\Controllers\LaporanStokController::class, 'getDataBarangHampirHabis'])->name('laporan.barang-hampir-habis.data');
        Route::get('/stok/barang-hampir-habis/export-pdf', [\App\Http\Controllers\LaporanStokController::class, 'exportBarangHampirHabisPdf'])->name('laporan.barang-hampir-habis.export_pdf');
        Route::get('/stok/barang-hampir-habis/export-excel', [\App\Http\Controllers\LaporanStokController::class, 'exportBarangHampirHabisExcel'])->name('laporan.barang-hampir-habis.export_excel');

        Route::get('/stok/barang-tidak-laku', [\App\Http\Controllers\LaporanStokController::class, 'barangTidakLaku'])->name('laporan.barang-tidak-laku');
        Route::get('/stok/barang-tidak-laku/data', [\App\Http\Controllers\LaporanStokController::class, 'getDataBarangTidakLaku'])->name('laporan.barang-tidak-laku.data');
        Route::get('/stok/barang-tidak-laku/ringkasan', [\App\Http\Controllers\LaporanStokController::class, 'getRingkasanBarangTidakLaku'])->name('laporan.barang-tidak-laku.ringkasan');
        Route::get('/stok/barang-tidak-laku/export-pdf', [\App\Http\Controllers\LaporanStokController::class, 'exportBarangTidakLakuPdf'])->name('laporan.barang-tidak-laku.export_pdf');
        Route::get('/stok/barang-tidak-laku/export-excel', [\App\Http\Controllers\LaporanStokController::class, 'exportBarangTidakLakuExcel'])->name('laporan.barang-tidak-laku.export_excel');

        Route::get('/stok/koreksi-stok', [\App\Http\Controllers\LaporanStokController::class, 'koreksiStok'])->name('laporan.koreksi-stok');
        Route::get('/stok/koreksi-stok/data', [\App\Http\Controllers\LaporanStokController::class, 'getDataKoreksiStok'])->name('laporan.koreksi-stok.data');
        Route::get('/stok/koreksi-stok/ringkasan', [\App\Http\Controllers\LaporanStokController::class, 'getRingkasanKoreksiStok'])->name('laporan.koreksi-stok.ringkasan');
        Route::get('/stok/koreksi-stok/export-pdf', [\App\Http\Controllers\LaporanStokController::class, 'exportKoreksiStokPdf'])->name('laporan.koreksi-stok.export_pdf');

        Route::get('/stok/search-barang', [\App\Http\Controllers\LaporanStokController::class, 'searchBarang'])->name('laporan.stok.search-barang');

        Route::get('/rekap-harian', [\App\Http\Controllers\LaporanRekapHarianController::class, 'index'])->name('laporan.rekap_harian');
        Route::get('/rekap-harian/data', [\App\Http\Controllers\LaporanRekapHarianController::class, 'getData'])->name('laporan.rekap_harian.data');
        Route::get('/rekap-harian/export-pdf', [\App\Http\Controllers\LaporanRekapHarianController::class, 'exportPdf'])->name('laporan.rekap_harian.export_pdf');

        Route::get('/penjualan-harian', [\App\Http\Controllers\LaporanPenjualanHarianController::class, 'index'])->name('laporan.penjualan-harian');
        Route::get('/penjualan-harian/data', [\App\Http\Controllers\LaporanPenjualanHarianController::class, 'data'])->name('laporan.penjualan-harian.data');
        Route::get('/penjualan-harian/ringkasan', [\App\Http\Controllers\LaporanPenjualanHarianController::class, 'getRingkasan'])->name('laporan.penjualan-harian.ringkasan');
        Route::get('/penjualan-harian/chart', [\App\Http\Controllers\LaporanPenjualanHarianController::class, 'getChartData'])->name('laporan.penjualan-harian.chart');
        Route::get('/penjualan-harian/export-pdf', [\App\Http\Controllers\LaporanPenjualanHarianController::class, 'exportPDF'])->name('laporan.penjualan-harian.export_pdf');

        Route::get('/rekap_bulanan', [\App\Http\Controllers\LaporanRekapBulananController::class, 'index'])->name('laporan.rekap_bulanan');
        Route::get('/rekap_bulanan/data', [\App\Http\Controllers\LaporanRekapBulananController::class, 'getData'])->name('laporan.rekap_bulanan.data');
        Route::get('/rekap_bulanan/export-pdf', [\App\Http\Controllers\LaporanRekapBulananController::class, 'exportPdf'])->name('laporan.rekap_bulanan.export_pdf');

        Route::get('/stok-opname', [\App\Http\Controllers\LaporanStokOpnameController::class, 'index'])->name('laporan.stok-opname');
        Route::get('/stok-opname/data', [\App\Http\Controllers\LaporanStokOpnameController::class, 'getData'])->name('laporan.stok-opname.data');
        Route::get('/stok-opname/{id}', [\App\Http\Controllers\LaporanStokOpnameController::class, 'show'])->name('laporan.stok-opname.show');
        Route::get('/stok-opname/{id}/export-pdf', [\App\Http\Controllers\LaporanStokOpnameController::class, 'exportPDF'])->name('laporan.stok-opname.export-pdf');
    });

    // Stok Opname Routes
    Route::get('stok-opname/get-barang-by-kategori', [\App\Http\Controllers\StokOpnameController::class, 'getBarangByKategori'])->name('stok-opname.get-barang-by-kategori');
    Route::resource('stok-opname', \App\Http\Controllers\StokOpnameController::class);
    Route::post('stok-opname/{id}/update-status', [\App\Http\Controllers\StokOpnameController::class, 'updateStatus'])->name('stok-opname.updateStatus');

    // Kas Routes
    Route::prefix('kas')->group(function () {
        Route::get('/', [KasController::class, 'index'])->name('kas.index');
        Route::get('/data', [KasController::class, 'data'])->name('kas.data');
        Route::get('/create', [KasController::class, 'create'])->name('kas.create');
        Route::post('/store', [KasController::class, 'store'])->name('kas.store');
        Route::get('/{id}/edit', [KasController::class, 'edit'])->name('kas.edit');
        Route::put('/{id}/update', [KasController::class, 'update'])->name('kas.update');
        Route::delete('/{id}/delete', [KasController::class, 'destroy'])->name('kas.delete');
        Route::get('/masuk', function () {
            return redirect()->route('kas.create', ['tipe' => 'masuk']);
        })->name('kas.masuk');
        Route::get('/keluar', function () {
            return redirect()->route('kas.create', ['tipe' => 'keluar']);
        })->name('kas.keluar');
    });

    // Kas Saldo Routes
    Route::prefix('kas-saldo')->group(function () {
        Route::get('/', [KasSaldoController::class, 'index'])->name('kas-saldo.index');
        Route::get('/data', [KasSaldoController::class, 'data'])->name('kas-saldo.data');
        Route::get('/create', [KasSaldoController::class, 'create'])->name('kas-saldo.create');
        Route::post('/store', [KasSaldoController::class, 'store'])->name('kas-saldo.store');
        Route::get('/{id}/edit', [KasSaldoController::class, 'edit'])->name('kas-saldo.edit');
        Route::put('/{id}/update', [KasSaldoController::class, 'update'])->name('kas-saldo.update');
        Route::delete('/{id}/delete', [KasSaldoController::class, 'destroy'])->name('kas-saldo.delete');
    });

    // Profil Toko Routes
    Route::resource('profil-toko', \App\Http\Controllers\ProfilTokoController::class)->only(['index', 'update']);

});
Route::get('/penjualan-barang', [\App\Http\Controllers\LaporanPenjualanBarangController::class, 'index'])->name('laporan.penjualan-barang');
Route::get('/penjualan-barang/data', [\App\Http\Controllers\LaporanPenjualanBarangController::class, 'data'])->name('laporan.penjualan-barang.data');
Route::get('/penjualan-barang/ringkasan', [\App\Http\Controllers\LaporanPenjualanBarangController::class, 'getRingkasan'])->name('laporan.penjualan-barang.ringkasan');
Route::get('/penjualan-barang/chart', [\App\Http\Controllers\LaporanPenjualanBarangController::class, 'getChartData'])->name('laporan.penjualan-barang.chart');
Route::get('/penjualan-barang/export-pdf', [\App\Http\Controllers\LaporanPenjualanBarangController::class, 'exportPDF'])->name('laporan.penjualan-barang.export_pdf');
Route::get('/penjualan-barang/export-excel', [\App\Http\Controllers\LaporanPenjualanBarangController::class, 'exportExcel'])->name('laporan.penjualan-barang.export_excel');

// Barang info API
Route::get('/barang/{id}/info', [BarangController::class, 'getInfo'])->name('barang.info');
