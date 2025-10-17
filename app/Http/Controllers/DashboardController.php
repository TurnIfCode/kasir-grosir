<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Tanggal hari ini
        $today = Carbon::today();

        // -------------------------
        // 1) RINGKASAN ANGKA UTAMA
        // -------------------------
        $totalPenjualanHariIni = DB::table('penjualan')
            ->whereDate('tanggal_penjualan', $today)
            ->sum('grand_total');

        $jumlahTransaksiHariIni = DB::table('penjualan')
            ->whereDate('tanggal_penjualan', $today)
            ->count();

        $totalPembelianHariIni = DB::table('pembelian')
            ->whereDate('tanggal_pembelian', $today)
            ->sum('total');

        $totalBarangTerjual = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
            ->whereDate('p.tanggal_penjualan', $today)
            ->sum('pd.qty');

        $totalBarangAktif = DB::table('barang')
            ->where('status', 'aktif')
            ->count();

        // Barang hampir habis (pakai ambang batas stok ≤ 5)
        $stokThreshold = 5;
        $barangHampirHabis = DB::table('barang')
            ->where('status', 'aktif')
            ->where('stok', '<=', $stokThreshold)
            ->count();

        // ----------------------------------------
        // 2) DATA GRAFIK PENJUALAN (7 HARI TERAKHIR)
        // ----------------------------------------
        $grafikPenjualan = DB::table('penjualan')
            ->select(
                DB::raw('DATE(tanggal_penjualan) as tanggal'),
                DB::raw('SUM(grand_total) as total')
            )
            ->whereBetween('tanggal_penjualan', [Carbon::now()->subDays(6), Carbon::now()])
            ->groupBy(DB::raw('DATE(tanggal_penjualan)'))
            ->orderBy('tanggal_penjualan', 'asc')
            ->get();

        // ------------------------------
        // 3) TOP 5 BARANG PALING LARIS
        // ------------------------------
        $topBarang = DB::table('penjualan_detail as pd')
            ->join('barang as b', 'b.id', '=', 'pd.barang_id')
            ->select('b.nama_barang', DB::raw('SUM(pd.qty) as total_terjual'))
            ->groupBy('b.nama_barang')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        // ------------------------------
        // 4) BARANG HAMPIR HABIS (TOP 5)
        // ------------------------------
        $stokMenipis = DB::table('barang')
            ->select('kode_barang', 'nama_barang', 'stok')
            ->where('status', 'aktif')
            ->where('stok', '<=', $stokThreshold)
            ->orderBy('stok', 'asc')
            ->limit(5)
            ->get();

        // --------------------------------
        // 5) TRANSAKSI PENJUALAN TERBARU
        // --------------------------------
        $transaksiTerbaru = DB::table('penjualan')
            ->select('kode_penjualan', 'tanggal_penjualan', 'grand_total', 'created_by')
            ->orderByDesc('tanggal_penjualan')
            ->limit(10)
            ->get();

        // ------------------------
        // Kirim data ke view
        // ------------------------
        return view('dashboard', compact(
            'totalPenjualanHariIni',
            'jumlahTransaksiHariIni',
            'totalPembelianHariIni',
            'totalBarangTerjual',
            'totalBarangAktif',
            'barangHampirHabis',
            'grafikPenjualan',
            'topBarang',
            'stokMenipis',
            'transaksiTerbaru'
        ));
    }
}
