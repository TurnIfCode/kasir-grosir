<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanStokExport;
use App\Exports\LaporanBarangHampirHabisExport;
use App\Exports\LaporanBarangTidakLakuExport;

class LaporanStokController extends Controller
{
    /**
     * Tampilkan halaman menu laporan stok
     */
    public function index()
    {
        return view('laporan.stok');
    }

    /**
     * Tampilkan halaman laporan stok akhir
     */
    public function stokAkhir()
    {
        $kategoris = DB::table('kategori')->where('status', 'aktif')->get();
        return view('laporan.stok-akhir', compact('kategoris'));
    }

    /**
     * Tampilkan halaman laporan stok masuk & keluar
     */
    public function stokMasukKeluar()
    {
        $barangs = DB::table('barang')->where('status', 'aktif')->get();
        return view('laporan.stok-masuk-keluar', compact('barangs'));
    }

    /**
     * Tampilkan halaman laporan barang hampir habis
     */
    public function barangHampirHabis()
    {
        $kategoris = DB::table('kategori')->where('status', 'aktif')->get();
        return view('laporan.barang-hampir-habis', compact('kategoris'));
    }

    /**
     * Tampilkan halaman laporan barang tidak laku
     */
    public function barangTidakLaku()
    {
        $kategoris = DB::table('kategori')->where('status', 'aktif')->get();
        return view('laporan.barang-tidak-laku', compact('kategoris'));
    }

    /**
     * Tampilkan halaman laporan koreksi stok
     */
    public function koreksiStok()
    {
        $kategoris = DB::table('kategori')->where('status', 'aktif')->get();
        return view('laporan.koreksi-stok', compact('kategoris'));
    }

    /**
     * Ambil data stok akhir (untuk DataTables server-side)
     */
    public function getDataStokAkhir(Request $request)
    {
        $kategoriId = $request->kategori_id;
        $urutan = $request->urutan ?? 'nama_barang';

        $query = DB::table('barang as b')
            ->leftJoin('kategori as k', 'b.kategori_id', '=', 'k.id')
            ->leftJoin('satuan as s', 'b.satuan_id', '=', 's.id')
            ->leftJoin('harga_barang as hb', function($join) {
                $join->on('hb.barang_id', '=', 'b.id')
                     ->where('hb.tipe_harga', '=', 'beli')
                     ->where('hb.status', '=', 'aktif');
            })
            ->select([
                'b.id',
                'b.kode_barang',
                'b.nama_barang',
                DB::raw('COALESCE(k.nama_kategori, "-") as nama_kategori'),
                DB::raw('COALESCE(s.nama_satuan, "-") as nama_satuan'),
                'b.stok as stok_akhir',
                DB::raw('COALESCE(hb.harga, b.harga_beli, 0) as harga_beli_terakhir'),
                DB::raw('(b.stok * COALESCE(hb.harga, b.harga_beli, 0)) as nilai_total'),
            ])
            ->where('b.status', '=', 'aktif')
            ->when($kategoriId, fn($q) => $q->where('b.kategori_id', $kategoriId));

        // Urutan
        if ($urutan === 'stok_terbanyak') {
            $query->orderBy('b.stok', 'desc');
        } elseif ($urutan === 'stok_paling_sedikit') {
            $query->orderBy('b.stok', 'asc');
        } else {
            $query->orderBy('b.nama_barang', 'asc');
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('stok_akhir', fn($row) => round($row->stok_akhir))
            ->editColumn('harga_beli_terakhir', fn($row) => 'Rp ' . number_format($row->harga_beli_terakhir, 0, ',', '.'))
            ->editColumn('nilai_total', fn($row) => 'Rp ' . number_format($row->nilai_total, 0, ',', '.'))
            ->make(true);
    }

    /**
     * Ambil data stok masuk & keluar
     */
    public function getDataStokMasukKeluar(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $barangId = $request->barang_id;
        $tipe = $request->tipe; // 'masuk' atau 'keluar'

        if ($tipe === 'masuk') {
            // Stok Masuk dari Pembelian
            $query = DB::table('pembelian_detail as pd')
                ->join('pembelian as p', 'pd.pembelian_id', '=', 'p.id')
                ->join('barang as b', 'pd.barang_id', '=', 'b.id')
                ->join('satuan as s', 'pd.satuan_id', '=', 's.id')
                ->leftJoin('supplier as sup', 'p.supplier_id', '=', 'sup.id')
                ->select([
                    'p.tanggal_pembelian as tanggal',
                    DB::raw("'Pembelian' as jenis_transaksi"),

                    'p.kode_pembelian as nomor_transaksi',
                    'pd.qty_konversi as jumlah',
                    DB::raw('0 as stok_akhir'),
                    'b.nama_barang',
                    's.nama_satuan',
                    'pd.harga_beli',
                    'pd.subtotal',
                    DB::raw('COALESCE(sup.nama_supplier, "-") as supplier')
                ])
                ->where('p.status', 'selesai')
                ->when($tanggalAwal && $tanggalAkhir, function($q) use ($tanggalAwal, $tanggalAkhir) {
                    $q->whereBetween('p.tanggal_pembelian', [$tanggalAwal, $tanggalAkhir]);
                })
                ->when($barangId, fn($q) => $q->where('pd.barang_id', $barangId))
                ->orderBy('p.tanggal_pembelian', 'desc');
        } else {

            // Stok Keluar dari Penjualan
            $query = DB::table('penjualan_detail as pd')
                ->join('penjualan as p', 'pd.penjualan_id', '=', 'p.id')
                ->join('barang as b', 'pd.barang_id', '=', 'b.id')
                ->join('satuan as s', 'pd.satuan_id', '=', 's.id')
                ->leftJoin('pelanggan as pel', 'p.pelanggan_id', '=', 'pel.id')
                ->select([
                    'p.tanggal_penjualan as tanggal',
                    DB::raw("'Penjualan' as jenis_transaksi"),
                    'p.kode_penjualan as nomor_transaksi',
                    'pd.qty_konversi as jumlah',
                    DB::raw('0 as stok_akhir'),
                    'b.nama_barang',
                    's.nama_satuan',
                    'pd.harga_jual',
                    'pd.subtotal',
                    DB::raw('COALESCE(pel.nama_pelanggan, "-") as pelanggan')
                ])
                ->where('p.status', 'selesai')
                ->when($tanggalAwal && $tanggalAkhir, function($q) use ($tanggalAwal, $tanggalAkhir) {
                    $q->whereBetween('p.tanggal_penjualan', [$tanggalAwal, $tanggalAkhir]);
                })
                ->when($barangId, fn($q) => $q->where('pd.barang_id', $barangId))
                ->orderBy('p.tanggal_penjualan', 'desc');
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('tanggal', fn($row) => \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y'))
            ->editColumn('harga_beli', fn($row) => $tipe === 'masuk' ? 'Rp ' . number_format($row->harga_beli, 0, ',', '.') : '-')
            ->editColumn('harga_jual', fn($row) => $tipe === 'keluar' ? 'Rp ' . number_format($row->harga_jual, 0, ',', '.') : '-')
            ->editColumn('subtotal', fn($row) => 'Rp ' . number_format($row->subtotal, 0, ',', '.'))
            ->make(true);
    }

    /**
     * Ambil data barang hampir habis
     */
    public function getDataBarangHampirHabis(Request $request)
    {
        $kategoriId = $request->kategori_id;
        $batasMinimum = $request->batas_minimum ?? 5;

        $query = DB::table('barang as b')
            ->leftJoin('kategori as k', 'b.kategori_id', '=', 'k.id')
            ->leftJoin('satuan as s', 'b.satuan_id', '=', 's.id')
            ->leftJoin('stok_minimum as sm', 'sm.barang_id', '=', 'b.id')
            ->select([
                'b.id',
                'b.kode_barang',
                'b.nama_barang',
                DB::raw('COALESCE(k.nama_kategori, "-") as nama_kategori'),
                DB::raw('COALESCE(s.nama_satuan, "-") as nama_satuan'),
                'b.stok as stok_sekarang',
                DB::raw('COALESCE(sm.jumlah_minimum, ' . $batasMinimum . ') as batas_minimum'),
                DB::raw('CASE
                    WHEN b.stok <= 0 THEN "Habis"
                    WHEN b.stok <= COALESCE(sm.jumlah_minimum, ' . $batasMinimum . ') THEN "Hampir Habis"
                    ELSE "Normal"
                END as status')
            ])
            ->where('b.status', '=', 'aktif')
            ->where('b.stok', '<=', DB::raw('COALESCE(sm.jumlah_minimum, ' . $batasMinimum . ')'))
            ->when($kategoriId, fn($q) => $q->where('b.kategori_id', $kategoriId))
            ->orderBy('b.stok', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('badge_status', function($row) {
                $badgeClass = match($row->status) {
                    'Habis' => 'badge bg-danger',
                    'Hampir Habis' => 'badge bg-warning text-dark',
                    default => 'badge bg-success'
                };
                return '<span class="' . $badgeClass . '">' . $row->status . '</span>';
            })
            ->rawColumns(['badge_status'])
            ->make(true);
    }

    /**
     * Ambil data barang tidak laku
     */
    public function getDataBarangTidakLaku(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $kategoriId = $request->kategori_id;

        $subQuery = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'pd.penjualan_id', '=', 'p.id')
            ->select('pd.barang_id', DB::raw('MAX(p.tanggal_penjualan) as terakhir_terjual'))
            ->where('p.status', 'selesai')
            ->when($tanggalAwal && $tanggalAkhir, function($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->whereBetween('p.tanggal_penjualan', [$tanggalAwal, $tanggalAkhir]);
            })
            ->groupBy('pd.barang_id');

        $query = DB::table('barang as b')
            ->leftJoin('kategori as k', 'b.kategori_id', '=', 'k.id')
            ->leftJoin('satuan as s', 'b.satuan_id', '=', 's.id')
            ->leftJoin('harga_barang as hb', function($join) {
                $join->on('hb.barang_id', '=', 'b.id')
                     ->where('hb.tipe_harga', '=', 'beli')
                     ->where('hb.status', '=', 'aktif');
            })
            ->leftJoinSub($subQuery, 'last_sale', 'last_sale.barang_id', '=', 'b.id')
            ->select([
                'b.id',
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
            ->whereNull('last_sale.terakhir_terjual') // Belum pernah terjual
            ->when($kategoriId, fn($q) => $q->where('b.kategori_id', $kategoriId))
            ->orderBy('b.nama_barang', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('terakhir_terjual', fn($row) => $row->terakhir_terjual ? \Carbon\Carbon::parse($row->terakhir_terjual)->format('d/m/Y') : 'Belum pernah')
            ->editColumn('harga_beli', fn($row) => 'Rp ' . number_format($row->harga_beli, 0, ',', '.'))
            ->editColumn('nilai_stok', fn($row) => 'Rp ' . number_format($row->nilai_stok, 0, ',', '.'))
            ->make(true);
    }

    /**
     * Ambil data koreksi stok
     */
    public function getDataKoreksiStok(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $kategoriId = $request->kategori_id;

        $query = DB::table('stok_opname_detail as sod')
            ->join('stok_opname as so', 'sod.stok_opname_id', '=', 'so.id')
            ->join('barang as b', 'sod.barang_id', '=', 'b.id')
            ->leftJoin('kategori as k', 'b.kategori_id', '=', 'k.id')
            ->leftJoin('satuan as s', 'b.satuan_id', '=', 's.id')
            ->select([
                'b.kode_barang',
                'b.nama_barang',
                DB::raw('COALESCE(k.nama_kategori, "-") as nama_kategori'),
                DB::raw('COALESCE(s.nama_satuan, "-") as nama_satuan'),
                'sod.stok_sistem',
                'sod.stok_fisik as stok_real',
                DB::raw('(sod.stok_fisik - sod.stok_sistem) as selisih'),
                'sod.keterangan',
                'so.tanggal'
            ])
            ->when($tanggalAwal && $tanggalAkhir, function($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->whereBetween('so.tanggal', [$tanggalAwal, $tanggalAkhir]);
            })
            ->when($kategoriId, fn($q) => $q->where('b.kategori_id', $kategoriId))
            ->orderBy('so.tanggal', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('tanggal', fn($row) => \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y'))
            ->addColumn('selisih_formatted', function($row) {
                $selisih = $row->selisih;
                $color = $selisih > 0 ? 'text-success' : ($selisih < 0 ? 'text-danger' : 'text-muted');
                $prefix = $selisih > 0 ? '+' : '';
                return '<span class="' . $color . '">' . $prefix . $selisih . '</span>';
            })
            ->rawColumns(['selisih_formatted'])
            ->make(true);
    }

    /**
     * Export laporan stok akhir ke PDF
     */
    public function exportStokAkhirPdf(Request $request)
    {
        $kategoriId = $request->kategori_id;
        $urutan = $request->urutan ?? 'nama_barang';

        $data = DB::table('barang as b')
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
            ->when($kategoriId, fn($q) => $q->where('b.kategori_id', $kategoriId));

        if ($urutan === 'stok_terbanyak') {
            $data->orderBy('b.stok', 'desc');
        } elseif ($urutan === 'stok_paling_sedikit') {
            $data->orderBy('b.stok', 'asc');
        } else {
            $data->orderBy('b.nama_barang', 'asc');
        }

        $data = $data->get();
        $totalNilaiStok = $data->sum('nilai_total');
        $totalItem = $data->count();

        $pdf = Pdf::loadView('laporan.stok-akhir-pdf', compact('data', 'totalNilaiStok', 'totalItem'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-stok-akhir.pdf');
    }

    /**
     * Export laporan stok akhir ke Excel
     */
    public function exportStokAkhirExcel(Request $request)
    {
        $kategoriId = $request->kategori_id;
        $urutan = $request->urutan ?? 'nama_barang';

        return Excel::download(new LaporanStokExport($kategoriId, $urutan), 'laporan-stok-akhir.xlsx');
    }

    /**
     * Get ringkasan stok akhir
     */
    public function getRingkasanStokAkhir(Request $request)
    {
        $kategoriId = $request->kategori_id;

        $data = DB::table('barang as b')
            ->leftJoin('harga_barang as hb', function($join) {
                $join->on('hb.barang_id', '=', 'b.id')
                     ->where('hb.tipe_harga', '=', 'beli')
                     ->where('hb.status', '=', 'aktif');
            })
            ->select([
                DB::raw('COUNT(*) as total_item'),
                DB::raw('SUM(b.stok) as total_stok'),
                DB::raw('SUM(b.stok * COALESCE(hb.harga, b.harga_beli, 0)) as total_nilai_stok')
            ])
            ->where('b.status', '=', 'aktif')
            ->when($kategoriId, fn($q) => $q->where('b.kategori_id', $kategoriId))
            ->first();

        return response()->json([
            'total_item' => $data->total_item ?? 0,
            'total_stok' => round($data->total_stok ?? 0),
            'total_nilai_stok' => 'Rp ' . number_format($data->total_nilai_stok ?? 0, 0, ',', '.')
        ]);
    }

    /**
     * Get ringkasan barang tidak laku
     */
    public function getRingkasanBarangTidakLaku(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $kategoriId = $request->kategori_id;

        $data = DB::table('barang as b')
            ->leftJoin('harga_barang as hb', function($join) {
                $join->on('hb.barang_id', '=', 'b.id')
                     ->where('hb.tipe_harga', '=', 'beli')
                     ->where('hb.status', '=', 'aktif');
            })
            ->select([
                DB::raw('COUNT(*) as jumlah_barang'),
                DB::raw('SUM(b.stok) as total_stok'),
                DB::raw('SUM(b.stok * COALESCE(hb.harga, b.harga_beli, 0)) as total_nilai_stok')
            ])
            ->where('b.status', '=', 'aktif')
            ->whereNotExists(function($query) use ($tanggalAwal, $tanggalAkhir) {
                $query->select(DB::raw(1))
                      ->from('penjualan_detail as pd')
                      ->join('penjualan as p', 'pd.penjualan_id', '=', 'p.id')
                      ->whereRaw('pd.barang_id = b.id')
                      ->where('p.status', 'selesai')
                      ->when($tanggalAwal && $tanggalAkhir, function($q) use ($tanggalAwal, $tanggalAkhir) {
                          $q->whereBetween('p.tanggal_penjualan', [$tanggalAwal, $tanggalAkhir]);
                      });
            })
            ->when($kategoriId, fn($q) => $q->where('b.kategori_id', $kategoriId))
            ->first();

        return response()->json([
            'jumlah_barang' => $data->jumlah_barang ?? 0,
            'total_stok' => round($data->total_stok ?? 0),
            'total_nilai_stok' => 'Rp ' . number_format($data->total_nilai_stok ?? 0, 0, ',', '.')
        ]);
    }

    /**
     * Get ringkasan koreksi stok
     */
    public function getRingkasanKoreksiStok(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $kategoriId = $request->kategori_id;

        $data = DB::table('stok_opname_detail as sod')
            ->join('stok_opname as so', 'sod.stok_opname_id', '=', 'so.id')
            ->join('barang as b', 'sod.barang_id', '=', 'b.id')
            ->select([
                DB::raw('SUM(CASE WHEN sod.stok_fisik > sod.stok_sistem THEN (sod.stok_fisik - sod.stok_sistem) ELSE 0 END) as selisih_positif'),
                DB::raw('SUM(CASE WHEN sod.stok_fisik < sod.stok_sistem THEN (sod.stok_sistem - sod.stok_fisik) ELSE 0 END) as selisih_negatif')
            ])
            ->when($tanggalAwal && $tanggalAkhir, function($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->whereBetween('so.tanggal', [$tanggalAwal, $tanggalAkhir]);
            })
            ->when($kategoriId, fn($q) => $q->where('b.kategori_id', $kategoriId))
            ->first();

        return response()->json([
            'selisih_positif' => $data->selisih_positif ?? 0,
            'selisih_negatif' => $data->selisih_negatif ?? 0,
            'selisih_positif_formatted' => '+Rp ' . number_format($data->selisih_positif ?? 0, 0, ',', '.'),
            'selisih_negatif_formatted' => '-Rp ' . number_format($data->selisih_negatif ?? 0, 0, ',', '.')
        ]);
    }

    /**
     * Export laporan stok masuk keluar ke PDF
     */
    public function exportStokMasukKeluarPdf(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $barangId = $request->barang_id;
        $tipe = $request->tipe ?? 'masuk';

        if ($tipe === 'masuk') {
            $data = DB::table('pembelian_detail as pd')
                ->join('pembelian as p', 'pd.pembelian_id', '=', 'p.id')
                ->join('barang as b', 'pd.barang_id', '=', 'b.id')
                ->join('satuan as s', 'pd.satuan_id', '=', 's.id')
                ->leftJoin('supplier as sup', 'p.supplier_id', '=', 'sup.id')
                ->select([
                    'p.tanggal_pembelian as tanggal',
                    DB::raw("'Pembelian' as jenis_transaksi"),
                    'p.kode_pembelian as nomor_transaksi',

                    'pd.qty_konversi as jumlah',
                    'b.nama_barang',
                    's.nama_satuan',
                    'pd.harga_beli',
                    'pd.subtotal',
                    DB::raw('COALESCE(sup.nama_supplier, "-") as supplier')
                ])
                ->where('p.status', 'selesai')
                ->when($tanggalAwal && $tanggalAkhir, function($q) use ($tanggalAwal, $tanggalAkhir) {
                    $q->whereBetween('p.tanggal_pembelian', [$tanggalAwal, $tanggalAkhir]);
                })
                ->when($barangId, fn($q) => $q->where('pd.barang_id', $barangId))
                ->orderBy('p.tanggal_pembelian', 'desc')
                ->get();

        } else {
            $data = DB::table('penjualan_detail as pd')
                ->join('penjualan as p', 'pd.penjualan_id', '=', 'p.id')
                ->join('barang as b', 'pd.barang_id', '=', 'b.id')
                ->join('satuan as s', 'pd.satuan_id', '=', 's.id')
                ->leftJoin('pelanggan as pel', 'p.pelanggan_id', '=', 'pel.id')
                ->select([
                    'p.tanggal_penjualan as tanggal',
                    DB::raw("'Penjualan' as jenis_transaksi"),
                    'p.kode_penjualan as nomor_transaksi',
                    'pd.qty_konversi as jumlah',
                    'b.nama_barang',
                    's.nama_satuan',
                    'pd.harga_jual',
                    'pd.subtotal',
                    DB::raw('COALESCE(pel.nama_pelanggan, "-") as pelanggan')
                ])
                ->where('p.status', 'selesai')
                ->when($tanggalAwal && $tanggalAkhir, function($q) use ($tanggalAwal, $tanggalAkhir) {
                    $q->whereBetween('p.tanggal_penjualan', [$tanggalAwal, $tanggalAkhir]);
                })
                ->when($barangId, fn($q) => $q->where('pd.barang_id', $barangId))
                ->orderBy('p.tanggal_penjualan', 'desc')
                ->get();
        }

        $pdf = Pdf::loadView('laporan.stok-masuk-keluar-pdf', compact('data', 'tipe'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-stok-' . $tipe . '.pdf');
    }

    /**
     * Export laporan barang hampir habis ke PDF
     */
    public function exportBarangHampirHabisPdf(Request $request)
    {
        $kategoriId = $request->kategori_id;
        $batasMinimum = $request->batas_minimum ?? 5;

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
                DB::raw('COALESCE(sm.jumlah_minimum, ' . $batasMinimum . ') as batas_minimum'),
                DB::raw('CASE
                    WHEN b.stok <= 0 THEN "Habis"
                    WHEN b.stok <= COALESCE(sm.jumlah_minimum, ' . $batasMinimum . ') THEN "Hampir Habis"
                    ELSE "Normal"
                END as status')
            ])
            ->where('b.status', '=', 'aktif')
            ->where('b.stok', '<=', DB::raw('COALESCE(sm.jumlah_minimum, ' . $batasMinimum . ')'))
            ->when($kategoriId, fn($q) => $q->where('b.kategori_id', $kategoriId))
            ->orderBy('b.stok', 'asc')
            ->get();

        $pdf = Pdf::loadView('laporan.barang-hampir-habis-pdf', compact('data'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-barang-hampir-habis.pdf');
    }

    /**
     * Export laporan barang tidak laku ke PDF
     */
    public function exportBarangTidakLakuPdf(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $kategoriId = $request->kategori_id;

        $subQuery = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'pd.penjualan_id', '=', 'p.id')
            ->select('pd.barang_id', DB::raw('MAX(p.tanggal_penjualan) as terakhir_terjual'))
            ->where('p.status', 'selesai')
            ->when($tanggalAwal && $tanggalAkhir, function($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->whereBetween('p.tanggal_penjualan', [$tanggalAwal, $tanggalAkhir]);
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
            ->when($kategoriId, fn($q) => $q->where('b.kategori_id', $kategoriId))
            ->orderBy('b.nama_barang', 'asc')
            ->get();

        $pdf = Pdf::loadView('laporan.barang-tidak-laku-pdf', compact('data'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-barang-tidak-laku.pdf');
    }

    /**
     * Export laporan koreksi stok ke PDF
     */
    public function exportKoreksiStokPdf(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $kategoriId = $request->kategori_id;

        $data = DB::table('stok_opname_detail as sod')
            ->join('stok_opname as so', 'sod.stok_opname_id', '=', 'so.id')
            ->join('barang as b', 'sod.barang_id', '=', 'b.id')
            ->leftJoin('kategori as k', 'b.kategori_id', '=', 'k.id')
            ->leftJoin('satuan as s', 'b.satuan_id', '=', 's.id')
            ->select([
                'b.kode_barang',
                'b.nama_barang',
                DB::raw('COALESCE(k.nama_kategori, "-") as nama_kategori'),
                DB::raw('COALESCE(s.nama_satuan, "-") as nama_satuan'),
                'sod.stok_sistem',
                'sod.stok_fisik as stok_real',
                DB::raw('(sod.stok_fisik - sod.stok_sistem) as selisih'),
                'sod.keterangan',
                'so.tanggal'
            ])
            ->when($tanggalAwal && $tanggalAkhir, function($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->whereBetween('so.tanggal', [$tanggalAwal, $tanggalAkhir]);
            })
            ->when($kategoriId, fn($q) => $q->where('b.kategori_id', $kategoriId))
            ->orderBy('so.tanggal', 'desc')
            ->get();

        $pdf = Pdf::loadView('laporan.koreksi-stok-pdf', compact('data'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-koreksi-stok.pdf');
    }

    /**
     * Export laporan barang hampir habis ke Excel
     */
    public function exportBarangHampirHabisExcel(Request $request)
    {
        $kategoriId = $request->kategori_id;
        $batasMinimum = $request->batas_minimum ?? 5;

        return Excel::download(new LaporanBarangHampirHabisExport($kategoriId, $batasMinimum), 'laporan-barang-hampir-habis.xlsx');
    }

    /**
     * Export laporan barang tidak laku ke Excel
     */
    public function exportBarangTidakLakuExcel(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $kategoriId = $request->kategori_id;

        return Excel::download(new LaporanBarangTidakLakuExport($tanggalAwal, $tanggalAkhir, $kategoriId), 'laporan-barang-tidak-laku.xlsx');
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
