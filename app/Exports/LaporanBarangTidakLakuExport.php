<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;
use DB;

class LaporanBarangTidakLakuExport implements FromView
{
    protected $tanggalAwal;
    protected $tanggalAkhir;
    protected $kategoriId;

    public function __construct($tanggalAwal, $tanggalAkhir, $kategoriId)
    {
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
        $this->kategoriId = $kategoriId;
    }

    public function view(): View
    {
        $subQuery = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'pd.penjualan_id', '=', 'p.id')
            ->select('pd.barang_id', DB::raw('MAX(p.tanggal_penjualan) as terakhir_terjual'))
            ->where('p.status', 'selesai')
            ->when($this->tanggalAwal && $this->tanggalAkhir, function($q) {
                $q->whereBetween('p.tanggal_penjualan', [$this->tanggalAwal, $this->tanggalAkhir]);
            })
            ->groupBy('pd.barang_id');

        $data = DB::table('barang as b')
            ->leftJoin('kategori as k', 'b.kategori_id', '=', 'k.id')
            ->leftJoin('satuan as s', 'b.satuan_id', '=', 's.id')
            ->leftJoin('harga_barang as hb', function($join) {
                $join->on('hb.barang_id', '=', 'b.id')
                     ->where('hb.tipe_harga', '=', 'beli')
                     ->where('hb.status', '=', 'aktif');
            })
            ->leftJoinSub($subQuery, 'last_sale', 'last_sale.barang_id', '=', 'b.id')
            ->select([
                'b.kode_barang',
                'b.nama_barang',
                DB::raw('COALESCE(k.nama_kategori, "-") as nama_kategori'),
                DB::raw('COALESCE(s.nama_satuan, "-") as nama_satuan'),
                'last_sale.terakhir_terjual',
                'b.stok as stok_sekarang',
                DB::raw('COALESCE(hb.harga, b.harga_beli, 0) as harga_beli'),
                DB::raw('(b.stok * COALESCE(hb.harga, b.harga_beli, 0)) as nilai_stok')
            ])
            ->where('b.status', '=', 'aktif')
            ->whereNull('last_sale.terakhir_terjual')
            ->when($this->kategoriId, fn($q) => $q->where('b.kategori_id', $this->kategoriId))
            ->orderBy('b.nama_barang', 'asc')
            ->get();

        return view('exports.laporan-barang-tidak-laku', compact('data'));
    }
}
