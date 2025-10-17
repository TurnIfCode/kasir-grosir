<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanStokController extends Controller
{
    /**
     * Tampilkan halaman laporan stok barang
     */
    public function index()
    {
        return view('laporan.stok');
    }

    /**
     * Ambil data stok barang (untuk DataTables server-side)
     */
    public function getData(Request $request)
    {
        $barangId = $request->barang_id;

        $query = DB::table('barang as b')
            ->leftJoin('kategori as k', 'b.kategori_id', '=', 'k.id')
            ->leftJoin('satuan as s', 'b.satuan_id', '=', 's.id')
            ->select([
                'b.id',
                'b.kode_barang',
                'b.nama_barang',
                DB::raw('COALESCE(k.nama_kategori, "-") as nama_kategori'),
                DB::raw('COALESCE(s.nama_satuan, "-") as nama_satuan'),
                'b.stok as stok_akhir',
            ])
            ->where('b.status', '=', 'aktif')
            ->when($barangId, fn($q) => $q->where('b.id', $barangId))
            ->orderBy('b.nama_barang', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('stok_akhir', fn($row) => number_format($row->stok_akhir, 0, ',', '.'))
            ->make(true);
    }

    /**
     * Export laporan stok barang ke PDF
     */
    public function exportPdf(Request $request)
    {
        $barangId = $request->barang_id;

        $data = DB::table('barang as b')
            ->leftJoin('kategori as k', 'b.kategori_id', '=', 'k.id')
            ->leftJoin('satuan as s', 'b.satuan_id', '=', 's.id')
            ->select([
                'b.kode_barang',
                'b.nama_barang',
                DB::raw('COALESCE(k.nama_kategori, "-") as nama_kategori'),
                DB::raw('COALESCE(s.nama_satuan, "-") as nama_satuan'),
                'b.stok as stok_akhir',
            ])
            ->where('b.status', '=', 'aktif')
            ->when($barangId, fn($q) => $q->where('b.id', $barangId))
            ->orderBy('b.nama_barang', 'asc')
            ->get();

        $totalStokAkhir = $data->sum('stok_akhir');

        $pdf = Pdf::loadView('laporan.stok-pdf', compact('data', 'totalStokAkhir'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan-stok-barang.pdf');
    }

    /**
     * Autocomplete untuk pencarian barang
     */
    public function searchBarang(Request $request)
    {
        $term = $request->get('term', '');

        $barangs = DB::table('barang')
            ->where('status', '=', 'aktif')
            ->where(function ($q) use ($term) {
                $q->where('nama_barang', 'LIKE', "%{$term}%")
                  ->orWhere('kode_barang', 'LIKE', "%{$term}%");
            })
            ->select('id', 'nama_barang', 'kode_barang')
            ->limit(10)
            ->get();

        return response()->json($barangs);
    }
}
