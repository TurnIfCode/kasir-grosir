<?php

namespace App\Exports;

use App\Models\PenjualanDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;
use DB;

class LaporanPenjualanBarangExport implements FromView
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $query = PenjualanDetail::with(['barang.kategori', 'penjualan'])
            ->join('penjualan', 'penjualan_detail.penjualan_id', '=', 'penjualan.id')
            ->join('barang', 'penjualan_detail.barang_id', '=', 'barang.id')
            ->leftJoin('kategori', 'barang.kategori_id', '=', 'kategori.id')
            ->select([
                'barang.kode_barang',
                'barang.nama_barang',
                'kategori.nama_kategori',
                DB::raw('SUM(penjualan_detail.qty_konversi) as jumlah_terjual'),
                DB::raw('SUM(penjualan_detail.qty_konversi * penjualan_detail.harga_beli) as total_modal'),
                DB::raw('SUM(penjualan_detail.subtotal) as total_penjualan'),
                DB::raw('(SUM(penjualan_detail.subtotal) - SUM(penjualan_detail.qty_konversi * penjualan_detail.harga_beli)) as laba_kotor'),
                DB::raw('(SUM(penjualan_detail.subtotal) - SUM(penjualan_detail.qty_konversi * penjualan_detail.harga_beli)) as laba_bersih')
            ])
            ->where('penjualan.status', 'selesai')
            ->groupBy(['penjualan_detail.barang_id', 'barang.kode_barang', 'barang.nama_barang', 'kategori.nama_kategori']);

        // Apply filters
        if ($this->request->filled('tanggal_dari')) {
            $query->where('penjualan.tanggal_penjualan', '>=', $this->request->tanggal_dari);
        }
        if ($this->request->filled('tanggal_sampai')) {
            $query->where('penjualan.tanggal_penjualan', '<=', $this->request->tanggal_sampai);
        }
        if ($this->request->filled('kategori_id') && $this->request->kategori_id != 'all') {
            $query->where('barang.kategori_id', $this->request->kategori_id);
        }

        $data = $query->get();

        return view('exports.laporan-penjualan-barang', compact('data'));
    }
}
