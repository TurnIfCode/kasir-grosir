<?php

namespace App\Http\Controllers;

use App\Models\PenjualanDetail;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanPenjualanBarangExport;
use Carbon\Carbon;
use DB;

class LaporanPenjualanBarangController extends Controller
{
    public function index()
    {
        $kategoris = Kategori::all();
        return view('laporan.penjualan-barang', compact('kategoris'));
    }

    public function data(Request $request)
    {
        $query = PenjualanDetail::with(['barang.kategori', 'penjualan'])
            ->join('penjualan', 'penjualan_detail.penjualan_id', '=', 'penjualan.id')
            ->join('barang', 'penjualan_detail.barang_id', '=', 'barang.id')
            ->leftJoin('kategori', 'barang.kategori_id', '=', 'kategori.id')
            ->select([
                'barang.kode_barang',
                'barang.nama_barang',
                'kategori.nama_kategori',
                DB::raw('SUM(penjualan_detail.qty) as jumlah_terjual'),
                DB::raw('SUM(penjualan_detail.subtotal) as total_nilai_penjualan'),
                'barang.harga_beli',
                'barang.harga_jual',
                DB::raw('(SUM(penjualan_detail.subtotal) - (SUM(penjualan_detail.qty) * barang.harga_beli)) as margin_keuntungan')
            ])
            ->where('penjualan.status', 'selesai')
            ->groupBy('barang.id', 'barang.kode_barang', 'barang.nama_barang', 'kategori.nama_kategori', 'barang.harga_beli', 'barang.harga_jual');

        // Filter tanggal
        if ($request->filled('tanggal_dari')) {
            $query->where('penjualan.tanggal_penjualan', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('penjualan.tanggal_penjualan', '<=', $request->tanggal_sampai);
        }

        // Filter kategori
        if ($request->filled('kategori_id') && $request->kategori_id != 'all') {
            $query->where('barang.kategori_id', $request->kategori_id);
        }

        // Urutan
        $orderColumn = $request->get('order_column', 'jumlah_terjual');
        $orderDirection = $request->get('order_direction', 'desc');

        if ($orderColumn == 'jumlah_terjual') {
            $query->orderBy('jumlah_terjual', $orderDirection);
        } elseif ($orderColumn == 'margin_keuntungan') {
            $query->orderBy('margin_keuntungan', $orderDirection);
        } else {
            $query->orderBy('jumlah_terjual', 'desc');
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('jumlah_terjual_formatted', function ($row) {
                return number_format($row->jumlah_terjual, 2, ',', '.');
            })
            ->addColumn('total_nilai_penjualan_formatted', function ($row) {
                return 'Rp ' . number_format($row->total_nilai_penjualan, 0, ',', '.');
            })
            ->addColumn('harga_beli_formatted', function ($row) {
                return 'Rp ' . number_format($row->harga_beli, 0, ',', '.');
            })
            ->addColumn('harga_jual_formatted', function ($row) {
                return 'Rp ' . number_format($row->harga_jual, 0, ',', '.');
            })
            ->addColumn('margin_keuntungan_formatted', function ($row) {
                return 'Rp ' . number_format($row->margin_keuntungan, 0, ',', '.');
            })
            ->rawColumns(['margin_keuntungan_formatted'])
            ->make(true);
    }

    public function getRingkasan(Request $request)
    {
        $query = PenjualanDetail::join('penjualan', 'penjualan_detail.penjualan_id', '=', 'penjualan.id')
            ->join('barang', 'penjualan_detail.barang_id', '=', 'barang.id')
            ->where('penjualan.status', 'selesai');

        // Filter tanggal
        if ($request->filled('tanggal_dari')) {
            $query->where('penjualan.tanggal_penjualan', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('penjualan.tanggal_penjualan', '<=', $request->tanggal_sampai);
        }

        // Filter kategori
        if ($request->filled('kategori_id') && $request->kategori_id != 'all') {
            $query->where('barang.kategori_id', $request->kategori_id);
        }

        $totalProdukTerjual = $query->sum('penjualan_detail.qty');
        $totalNilaiPenjualan = $query->sum('penjualan_detail.subtotal');
        $totalLabaKotor = $query->selectRaw('(SUM(penjualan_detail.subtotal) - SUM(penjualan_detail.qty * barang.harga_beli)) as laba_kotor')->first()->laba_kotor ?? 0;

        $rataRataMargin = $totalNilaiPenjualan > 0 ? ($totalLabaKotor / $totalNilaiPenjualan) * 100 : 0;

        return response()->json([
            'total_produk_terjual' => number_format($totalProdukTerjual, 2),
            'total_nilai_penjualan' => 'Rp ' . number_format($totalNilaiPenjualan, 0, ',', '.'),
            'total_laba_kotor' => 'Rp ' . number_format($totalLabaKotor, 0, ',', '.'),
            'rata_rata_margin' => number_format($rataRataMargin, 2) . '%'
        ]);
    }

    public function getChartData(Request $request)
    {
        $query = PenjualanDetail::with('barang')
            ->join('penjualan', 'penjualan_detail.penjualan_id', '=', 'penjualan.id')
            ->join('barang', 'penjualan_detail.barang_id', '=', 'barang.id')
            ->select([
                'barang.nama_barang',
                DB::raw('SUM(penjualan_detail.qty) as jumlah_terjual'),
                DB::raw('SUM(penjualan_detail.subtotal) as total_nilai')
            ])
            ->where('penjualan.status', 'selesai')
            ->groupBy('barang.id', 'barang.nama_barang')
            ->orderBy('jumlah_terjual', 'desc')
            ->limit(10);

        // Filter tanggal
        if ($request->filled('tanggal_dari')) {
            $query->where('penjualan.tanggal_penjualan', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('penjualan.tanggal_penjualan', '<=', $request->tanggal_sampai);
        }

        // Filter kategori
        if ($request->filled('kategori_id') && $request->kategori_id != 'all') {
            $query->where('barang.kategori_id', $request->kategori_id);
        }

        $data = $query->get();

        return response()->json([
            'labels' => $data->pluck('nama_barang'),
            'jumlah_terjual' => $data->pluck('jumlah_terjual'),
            'total_nilai' => $data->pluck('total_nilai')
        ]);
    }

    public function exportPDF(Request $request)
    {
        $query = PenjualanDetail::with(['barang.kategori', 'penjualan'])
            ->join('penjualan', 'penjualan_detail.penjualan_id', '=', 'penjualan.id')
            ->join('barang', 'penjualan_detail.barang_id', '=', 'barang.id')
            ->leftJoin('kategori', 'barang.kategori_id', '=', 'kategori.id')
            ->select([
                'barang.kode_barang',
                'barang.nama_barang',
                'kategori.nama_kategori',
                DB::raw('SUM(penjualan_detail.qty) as jumlah_terjual'),
                DB::raw('SUM(penjualan_detail.subtotal) as total_nilai_penjualan'),
                'barang.harga_beli',
                'barang.harga_jual',
                DB::raw('(SUM(penjualan_detail.subtotal) - (SUM(penjualan_detail.qty) * barang.harga_beli)) as margin_keuntungan')
            ])
            ->where('penjualan.status', 'selesai')
            ->groupBy('barang.id', 'barang.kode_barang', 'barang.nama_barang', 'kategori.nama_kategori', 'barang.harga_beli', 'barang.harga_jual');

        // Apply filters
        if ($request->filled('tanggal_dari')) {
            $query->where('penjualan.tanggal_penjualan', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('penjualan.tanggal_penjualan', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('kategori_id') && $request->kategori_id != 'all') {
            $query->where('barang.kategori_id', $request->kategori_id);
        }

        $data = $query->get();

        $tanggalDari = $request->tanggal_dari ? Carbon::parse($request->tanggal_dari)->format('d/m/Y') : '-';
        $tanggalSampai = $request->tanggal_sampai ? Carbon::parse($request->tanggal_sampai)->format('d/m/Y') : '-';

        $pdf = Pdf::loadView('laporan.penjualan-barang-pdf', compact('data', 'tanggalDari', 'tanggalSampai'));

        return $pdf->download('laporan-penjualan-barang.pdf');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new LaporanPenjualanBarangExport($request), 'laporan-penjualan-barang.xlsx');
    }
}
