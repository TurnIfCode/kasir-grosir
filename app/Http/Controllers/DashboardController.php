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
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;
        $lastWeek = Carbon::now()->subDays(7);

        // -------------------------
        // 1) RINGKASAN ANGKA UTAMA (6 CARD)
        // -------------------------
        $totalPenjualanHariIni = DB::table('penjualan')
            ->whereDate('tanggal_penjualan', $today)
            ->sum('grand_total');

        $labaKotorHariIni = $totalPenjualanHariIni - DB::table('pembelian')
            ->whereDate('tanggal_pembelian', $today)
            ->sum('total');


        $totalBarangTerjual = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
            ->whereDate('p.tanggal_penjualan', $today)
            ->sum('pd.qty_konversi');

        $totalPembelian = DB::table('pembelian')
            ->whereDate('tanggal_pembelian', $today)
            ->sum('total');

        $kasirAktif = DB::table('users')
            ->where('role', 'KASIR')
            ->where('status', 'aktif')
            ->count();

        $saldoKas = DB::table('kas_saldo_transaksi')
            ->orderBy('created_at', 'desc')
            ->value('saldo_akhir') ?? 0;

        // ----------------------------------------
        // 2) DATA GRAFIK PENJUALAN 7 HARI TERAKHIR (LINE CHART)
        // ----------------------------------------
        $grafikPenjualan7Hari = DB::table('penjualan')
            ->select(
                DB::raw('DATE(tanggal_penjualan) as tanggal'),
                DB::raw('SUM(grand_total) as total')
            )
            ->whereBetween('tanggal_penjualan', [Carbon::now()->subDays(6), Carbon::now()])
            ->groupBy(DB::raw('DATE(tanggal_penjualan)'))
            ->orderBy('tanggal_penjualan', 'asc')
            ->get();

        // ----------------------------------------
        // 3) GRAFIK PERBANDINGAN PENJUALAN VS PEMBELIAN BULAN INI (BAR CHART)
        // ----------------------------------------
        $penjualanBulanIni = DB::table('penjualan')
            ->whereMonth('tanggal_penjualan', $thisMonth)
            ->whereYear('tanggal_penjualan', $thisYear)
            ->sum('grand_total');

        $pembelianBulanIni = DB::table('pembelian')
            ->whereMonth('tanggal_pembelian', $thisMonth)
            ->whereYear('tanggal_pembelian', $thisYear)
            ->sum('total');

        // ----------------------------------------
        // 4) PIE CHART KATEGORI PENJUALAN
        // ----------------------------------------
        $kategoriPenjualan = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
            ->join('barang as b', 'b.id', '=', 'pd.barang_id')
            ->join('kategori as k', 'k.id', '=', 'b.kategori_id')

            ->select('k.nama_kategori', DB::raw('SUM(pd.qty_konversi * pd.harga_jual) as total'))
            ->whereDate('p.tanggal_penjualan', $today)
            ->groupBy('k.nama_kategori')
            ->orderByDesc('total')
            ->get();

        // ------------------------------
        // 5) TOP 5 BARANG PALING LAKU HARI INI
        // ------------------------------
        $topBarangLaku = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
            ->join('barang as b', 'b.id', '=', 'pd.barang_id')

            ->select('b.nama_barang', DB::raw('SUM(pd.qty_konversi) as total_terjual'))
            ->whereDate('p.tanggal_penjualan', $today)
            ->groupBy('b.nama_barang')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        // ------------------------------
        // 6) 5 BARANG TIDAK LAKU MINGGU INI
        // ------------------------------
        $barangTidakLaku = DB::table('barang as b')
            ->leftJoin('penjualan_detail as pd', function($join) use ($lastWeek) {
                $join->on('b.id', '=', 'pd.barang_id')
                     ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
                     ->where('p.tanggal_penjualan', '>=', $lastWeek);
            })

            ->select('b.nama_barang', DB::raw('COALESCE(SUM(pd.qty_konversi), 0) as total_terjual'))
            ->where('b.status', 'aktif')
            ->groupBy('b.nama_barang')
            ->orderBy('total_terjual', 'asc')
            ->limit(5)
            ->get();

        // ------------------------------
        // 7) BARANG HAMPIR HABIS
        // ------------------------------
        $stokThreshold = 5;
        $barangHampirHabis = DB::table('barang')
            ->select('kode_barang', 'nama_barang', 'stok')
            ->where('status', 'aktif')
            ->where('stok', '<=', $stokThreshold)
            ->orderBy('stok', 'asc')
            ->limit(5)
            ->get();

        // ------------------------------
        // 8) PENGELUARAN HARI INI
        // ------------------------------
        $pengeluaranHariIni = DB::table('kas')
            ->where('tipe', 'keluar')
            ->whereDate('tanggal', $today)
            ->select('keterangan', 'nominal')
            ->orderBy('tanggal', 'desc')
            ->get();

        // ------------------------------
        // 9) NOTIFIKASI
        // ------------------------------
        $notifikasi = [];

        // Stok habis
        $stokHabis = DB::table('barang')
            ->where('status', 'aktif')
            ->where('stok', '<=', 0)
            ->count();
        if ($stokHabis > 0) {
            $notifikasi[] = ['type' => 'warning', 'message' => "Ada {$stokHabis} barang stok habis"];
        }

        // Pengeluaran besar (> 1jt)
        $pengeluaranBesar = DB::table('kas')
            ->where('tipe', 'keluar')
            ->whereDate('tanggal', $today)
            ->where('nominal', '>', 1000000)
            ->count();
        if ($pengeluaranBesar > 0) {
            $notifikasi[] = ['type' => 'info', 'message' => "Ada {$pengeluaranBesar} pengeluaran besar hari ini"];
        }

        // ------------------------
        // Kirim data ke view
        // ------------------------
        return view('dashboard', compact(
            'totalPenjualanHariIni',
            'labaKotorHariIni',
            'totalBarangTerjual',
            'totalPembelian',
            'kasirAktif',
            'saldoKas',
            'grafikPenjualan7Hari',
            'penjualanBulanIni',
            'pembelianBulanIni',
            'kategoriPenjualan',
            'topBarangLaku',
            'barangTidakLaku',
            'barangHampirHabis',
            'pengeluaranHariIni',
            'notifikasi'
        ));
    }
}
