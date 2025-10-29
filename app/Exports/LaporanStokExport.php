<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;
use DB;

class LaporanStokExport implements FromView
{
    protected $kategoriId;
    protected $urutan;

    public function __construct($kategoriId, $urutan)
    {
        $this->kategoriId = $kategoriId;
        $this->urutan = $urutan;
    }

    public function view(): View
    {
        $query = DB::table('barang as b')
            ->leftJoin('kategori as k', 'b.kategori_id', '=', 'k.id')
            ->leftJoin('satuan as s', 'b.satuan_id', '=', 's.id')
            ->leftJoin('harga_barang as hb', function($join) {
                $join->on('hb.barang_id', '=', 'b.id')
                     ->where('hb.tipe_harga', '=', 'beli')
                     ->where('hb.status', '=', 'aktif');
            })
            ->select([
                'b.kode_barang',
                'b.nama_barang',
                DB::raw('COALESCE(k.nama_kategori, "-") as nama_kategori'),
                DB::raw('COALESCE(s.nama_satuan, "-") as nama_satuan'),
                'b.stok as stok_akhir',
                DB::raw('COALESCE(hb.harga, b.harga_beli, 0) as harga_beli_terakhir'),
                DB::raw('(b.stok * COALESCE(hb.harga, b.harga_beli, 0)) as nilai_total'),
            ])
            ->where('b.status', '=', 'aktif')
            ->when($this->kategoriId, fn($q) => $q->where('b.kategori_id', $this->kategoriId));

        // Urutan
        if ($this->urutan === 'stok_terbanyak') {
            $query->orderBy('b.stok', 'desc');
        } elseif ($this->urutan === 'stok_paling_sedikit') {
            $query->orderBy('b.stok', 'asc');
        } else {
            $query->orderBy('b.nama_barang', 'asc');
        }

        $data = $query->get();

        return view('exports.laporan-stok-akhir', compact('data'));
    }
}
