<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanRekapHarianController extends Controller
{
    public function index()
    {
        return view('laporan.rekap_harian');
    }

    public function getData(Request $request)
    {
        $tanggal = $request->tanggal ?: date('Y-m-d');

        // Ringkasan data
        $ringkasan = $this->getRingkasan($tanggal);

        // Data untuk DataTables Penjualan
        if ($request->has('type') && $request->type == 'penjualan') {
            return $this->getDataPenjualan($tanggal);
        }

        // Data untuk DataTables Pembelian
        if ($request->has('type') && $request->type == 'pembelian') {
            return $this->getDataPembelian($tanggal);
        }

        // Return ringkasan untuk AJAX
        return response()->json($ringkasan);
    }

    private function getRingkasan($tanggal)
    {
        // Total Penjualan
        $totalPenjualan = DB::table('penjualan')
            ->join('penjualan_detail', 'penjualan.id', '=', 'penjualan_detail.penjualan_id')
            ->where('penjualan.status', 'selesai')
            ->whereDate('penjualan.tanggal_penjualan', $tanggal)
            ->sum('penjualan_detail.subtotal');

        // Total Pembelian
        $totalPembelian = DB::table('pembelian')
            ->join('pembelian_detail', 'pembelian.id', '=', 'pembelian_detail.pembelian_id')
            ->where('pembelian.status', 'selesai')
            ->whereDate('pembelian.tanggal_pembelian', $tanggal)
            ->sum('pembelian_detail.subtotal');

        // Total Barang Terjual
        $totalBarangTerjual = DB::table('penjualan')
            ->join('penjualan_detail', 'penjualan.id', '=', 'penjualan_detail.penjualan_id')
            ->where('penjualan.status', 'selesai')
            ->whereDate('penjualan.tanggal_penjualan', $tanggal)
            ->sum('penjualan_detail.qty');

        // Total Barang Dibeli
        $totalBarangDibeli = DB::table('pembelian')
            ->join('pembelian_detail', 'pembelian.id', '=', 'pembelian_detail.pembelian_id')
            ->where('pembelian.status', 'selesai')
            ->whereDate('pembelian.tanggal_pembelian', $tanggal)
            ->sum('pembelian_detail.qty');

        // Laba Kotor
        $labaKotor = $totalPenjualan - $totalPembelian;

        // Jumlah Transaksi Penjualan
        $jumlahTransaksiPenjualan = DB::table('penjualan')
            ->where('status', 'selesai')
            ->whereDate('tanggal_penjualan', $tanggal)
            ->count();

        // Jumlah Transaksi Pembelian
        $jumlahTransaksiPembelian = DB::table('pembelian')
            ->where('status', 'selesai')
            ->whereDate('tanggal_pembelian', $tanggal)
            ->count();

        // Barang Terlaris
        $barangTerlaris = DB::table('penjualan_detail')
            ->join('penjualan', 'penjualan_detail.penjualan_id', '=', 'penjualan.id')
            ->join('barang', 'penjualan_detail.barang_id', '=', 'barang.id')
            ->join('satuan', 'penjualan_detail.satuan_id', '=', 'satuan.id')
            ->select('barang.nama_barang', 'satuan.nama_satuan', DB::raw('SUM(penjualan_detail.qty) as total_qty'))
            ->where('penjualan.status', 'selesai')
            ->whereDate('penjualan.tanggal_penjualan', $tanggal)
            ->groupBy('penjualan_detail.barang_id', 'penjualan_detail.satuan_id', 'barang.nama_barang', 'satuan.nama_satuan')
            ->orderBy('total_qty', 'desc')
            ->first();

        $barangTerlarisText = $barangTerlaris ? $barangTerlaris->nama_barang . ' (' . number_format($barangTerlaris->total_qty, 2, ',', '.') . ' ' . $barangTerlaris->nama_satuan . ')' : '-';

        return [
            'total_penjualan' => 'Rp ' . number_format($totalPenjualan, 0, ',', '.'),
            'total_pembelian' => 'Rp ' . number_format($totalPembelian, 0, ',', '.'),
            'total_barang_terjual' => number_format($totalBarangTerjual, 2, ',', '.'),
            'total_barang_dibeli' => number_format($totalBarangDibeli, 2, ',', '.'),
            'laba_kotor' => 'Rp ' . number_format($labaKotor, 0, ',', '.'),
            'barang_terlaris' => $barangTerlarisText,
            'jumlah_transaksi_penjualan' => $jumlahTransaksiPenjualan,
            'jumlah_transaksi_pembelian' => $jumlahTransaksiPembelian,
        ];
    }

    private function getDataPenjualan($tanggal)
    {
        $query = DB::table('penjualan_detail')
            ->join('penjualan', 'penjualan_detail.penjualan_id', '=', 'penjualan.id')
            ->join('barang', 'penjualan_detail.barang_id', '=', 'barang.id')
            ->join('satuan', 'penjualan_detail.satuan_id', '=', 'satuan.id')
            ->select(
                'barang.kode_barang',
                'barang.nama_barang',
                'satuan.nama_satuan',
                DB::raw('SUM(penjualan_detail.qty) as jumlah_terjual'),
                DB::raw('AVG(penjualan_detail.harga_jual) as harga_rata_rata'),
                DB::raw('SUM(penjualan_detail.subtotal) as total')
            )
            ->where('penjualan.status', 'selesai')
            ->whereDate('penjualan.tanggal_penjualan', $tanggal)
            ->groupBy('penjualan_detail.barang_id', 'penjualan_detail.satuan_id', 'barang.kode_barang', 'barang.nama_barang', 'satuan.nama_satuan');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('harga_formatted', function ($row) {
                return 'Rp ' . number_format($row->harga_rata_rata, 0, ',', '.');
            })
            ->addColumn('total_formatted', function ($row) {
                return 'Rp ' . number_format($row->total, 0, ',', '.');
            })
            ->addColumn('jumlah_terjual_formatted', function ($row) {
                return number_format($row->jumlah_terjual, 2, ',', '.');
            })
            ->rawColumns([])
            ->make(true);
    }

    private function getDataPembelian($tanggal)
    {
        $query = DB::table('pembelian_detail')
            ->join('pembelian', 'pembelian_detail.pembelian_id', '=', 'pembelian.id')
            ->join('barang', 'pembelian_detail.barang_id', '=', 'barang.id')
            ->join('satuan', 'pembelian_detail.satuan_id', '=', 'satuan.id')
            ->select(
                'barang.kode_barang',
                'barang.nama_barang',
                'satuan.nama_satuan',
                DB::raw('SUM(pembelian_detail.qty) as jumlah_dibeli'),
                DB::raw('AVG(pembelian_detail.harga_beli) as harga_rata_rata'),
                DB::raw('SUM(pembelian_detail.subtotal) as total')
            )
            ->where('pembelian.status', 'selesai')
            ->whereDate('pembelian.tanggal_pembelian', $tanggal)
            ->groupBy('pembelian_detail.barang_id', 'pembelian_detail.satuan_id', 'barang.kode_barang', 'barang.nama_barang', 'satuan.nama_satuan');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('harga_formatted', function ($row) {
                return 'Rp ' . number_format($row->harga_rata_rata, 0, ',', '.');
            })
            ->addColumn('total_formatted', function ($row) {
                return 'Rp ' . number_format($row->total, 0, ',', '.');
            })
            ->addColumn('jumlah_dibeli_formatted', function ($row) {
                return number_format($row->jumlah_dibeli, 2, ',', '.');
            })
            ->rawColumns([])
            ->make(true);
    }

    public function exportPdf(Request $request)
    {
        $tanggal = $request->tanggal ?: date('Y-m-d');
        $ringkasan = $this->getRingkasan($tanggal);

        // Data penjualan untuk PDF
        $dataPenjualan = DB::table('penjualan_detail')
            ->join('penjualan', 'penjualan_detail.penjualan_id', '=', 'penjualan.id')
            ->join('barang', 'penjualan_detail.barang_id', '=', 'barang.id')
            ->join('satuan', 'penjualan_detail.satuan_id', '=', 'satuan.id')
            ->select(
                'barang.kode_barang',
                'barang.nama_barang',
                'satuan.nama_satuan',
                DB::raw('SUM(penjualan_detail.qty) as jumlah_terjual'),
                DB::raw('AVG(penjualan_detail.harga_jual) as harga'),
                DB::raw('SUM(penjualan_detail.subtotal) as total')
            )
            ->where('penjualan.status', 'selesai')
            ->whereDate('penjualan.tanggal_penjualan', $tanggal)
            ->groupBy('penjualan_detail.barang_id', 'penjualan_detail.satuan_id', 'barang.kode_barang', 'barang.nama_barang', 'satuan.nama_satuan')
            ->get();

        // Data pembelian untuk PDF
        $dataPembelian = DB::table('pembelian_detail')
            ->join('pembelian', 'pembelian_detail.pembelian_id', '=', 'pembelian.id')
            ->join('barang', 'pembelian_detail.barang_id', '=', 'barang.id')
            ->join('satuan', 'pembelian_detail.satuan_id', '=', 'satuan.id')
            ->select(
                'barang.kode_barang',
                'barang.nama_barang',
                'satuan.nama_satuan',
                DB::raw('SUM(pembelian_detail.qty) as jumlah_dibeli'),
                DB::raw('AVG(pembelian_detail.harga_beli) as harga'),
                DB::raw('SUM(pembelian_detail.subtotal) as total')
            )
            ->where('pembelian.status', 'selesai')
            ->whereDate('pembelian.tanggal_pembelian', $tanggal)
            ->groupBy('pembelian_detail.barang_id', 'pembelian_detail.satuan_id', 'barang.kode_barang', 'barang.nama_barang', 'satuan.nama_satuan')
            ->get();

        $tanggalFormatted = Carbon::parse($tanggal)->format('d/m/Y');

        $pdf = Pdf::loadView('laporan.rekap_harian-pdf', compact('ringkasan', 'dataPenjualan', 'dataPembelian', 'tanggalFormatted'));

        return $pdf->download('laporan-rekap-harian-' . $tanggal . '.pdf');
    }
}
