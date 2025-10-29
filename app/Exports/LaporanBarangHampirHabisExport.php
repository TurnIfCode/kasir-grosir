<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;
use DB;

class LaporanBarangHampirHabisExport implements FromView
{
    protected $kategoriId;
    protected $batasMinimum;

    public function __construct($kategoriId, $batasMinimum)
    {
        $this->kategoriId = $kategoriId;
        $this->batasMinimum = $batasMinimum ?? 5;
    }

    public function view(): View
    {
        $data = DB::table('barang as b')
            ->leftJoin('kategori as k', 'b.kategori_id', '=', 'k.id')
            ->leftJoin('satuan as s', 'b.satuan_id', '=', 's.id')
            ->leftJoin('stok_minimum as sm', 'sm.barang_id', '=', 'b.id')
            ->select([
                'b.kode_barang',
                'b.nama_barang',
                DB::raw('COALESCE(k.nama_kategori, "-") as nama_kategori'),
                DB::raw('COALESCE(s.nama_satuan, "-") as nama_satuan'),
                'b.stok as stok_sekarang',
                DB::raw('COALESCE(sm.jumlah_minimum, ' . $this->batasMinimum . ') as batas_minimum'),
                DB::raw('CASE
                    WHEN b.stok <= 0 THEN "Habis"
                    WHEN b.stok <= COALESCE(sm.jumlah_minimum, ' . $this->batasMinimum . ') THEN "Hampir Habis"
                    ELSE "Normal"
                END as status')
            ])
            ->where('b.status', '=', 'aktif')
            ->where('b.stok', '<=', DB::raw('COALESCE(sm.jumlah_minimum, ' . $this->batasMinimum . ')'))
            ->when($this->kategoriId, fn($q) => $q->where('b.kategori_id', $this->kategoriId))
            ->orderBy('b.stok', 'asc')
            ->get();

        return view('exports.laporan-barang-hampir-habis', compact('data'));
    }
}
