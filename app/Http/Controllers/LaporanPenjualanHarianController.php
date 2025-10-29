<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\User;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanPenjualanHarianController extends Controller
{
    public function index()
    {
        $kasirs = User::where('role', 'KASIR')->where('status', 'AKTIF')->get();
        $kategoris = Kategori::where('status', 'AKTIF')->get();
        return view('laporan.penjualan-harian', compact('kasirs', 'kategoris'));
    }

    public function data(Request $request)
    {
        $query = Penjualan::with(['details.barang.kategori', 'creator'])
            ->select('penjualan.*')
            ->where('status', 'selesai');

        // Filter rentang waktu
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_penjualan', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_penjualan', '<=', $request->tanggal_sampai);
        }

        // Filter kasir
        if ($request->filled('kasir_id') && $request->kasir_id != 'all') {
            $query->where('created_by', $request->kasir_id);
        }

        // Filter kategori
        if ($request->filled('kategori_id') && $request->kategori_id != 'all') {
            $query->whereHas('details.barang', function($q) use ($request) {
                $q->where('kategori_id', $request->kategori_id);
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nama_kasir', function ($row) {
                return $row->creator->name ?? '-';
            })
            ->addColumn('tanggal_penjualan_formatted', function ($row) {
                return $row->tanggal_penjualan->format('d/m/Y');
            })
            ->addColumn('kategori', function ($row) {
                $kategoris = $row->details->pluck('barang.kategori.nama_kategori')->unique()->implode(', ');
                return $kategoris ?: '-';
            })
            ->addColumn('jumlah_transaksi', function ($row) {
                return 1; // Setiap row adalah 1 transaksi
            })
            ->addColumn('total_barang', function ($row) {
                return $row->details->sum('qty');
            })
            ->addColumn('total_omzet', function ($row) {
                return $row->total;
            })
            ->addColumn('diskon', function ($row) {
                return $row->diskon;
            })
            ->addColumn('total_barang_formatted', function ($row) {
                return number_format($row->details->sum('qty'), 2, ',', '.');
            })
            ->addColumn('total_omzet_formatted', function ($row) {
                return 'Rp ' . number_format($row->total, 0, ',', '.');
            })
            ->addColumn('diskon_formatted', function ($row) {
                return 'Rp ' . number_format($row->diskon, 0, ',', '.');
            })
            ->rawColumns([])
            ->make(true);
    }

    public function getRingkasan(Request $request)
    {
        $query = Penjualan::where('status', 'selesai');

        // Filter rentang waktu
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_penjualan', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_penjualan', '<=', $request->tanggal_sampai);
        }

        // Filter kasir
        if ($request->filled('kasir_id') && $request->kasir_id != 'all') {
            $query->where('created_by', $request->kasir_id);
        }

        // Filter kategori
        if ($request->filled('kategori_id') && $request->kategori_id != 'all') {
            $query->whereHas('details.barang', function($q) use ($request) {
                $q->where('kategori_id', $request->kategori_id);
            });
        }

        $totalTransaksi = $query->count();
        $totalOmzet = $query->sum('total');
        $totalBarangTerjual = $query->with('details')->get()->sum(function($penjualan) {
            return $penjualan->details->sum('qty');
        });
        $totalDiskon = $query->sum('diskon');

        return response()->json([
            'total_transaksi' => $totalTransaksi,
            'total_omzet' => 'Rp ' . number_format($totalOmzet, 0, ',', '.'),
            'total_barang_terjual' => number_format($totalBarangTerjual, 2, ',', '.'),
            'total_diskon' => 'Rp ' . number_format($totalDiskon, 0, ',', '.'),
        ]);
    }

    public function getChartData(Request $request)
    {
        $tanggalDari = $request->tanggal_dari ?: date('Y-m-d', strtotime('-30 days'));
        $tanggalSampai = $request->tanggal_sampai ?: date('Y-m-d');

        // Data untuk line chart per hari
        $penjualanPerHari = Penjualan::select(
                DB::raw('DATE(tanggal_penjualan) as tanggal'),
                DB::raw('SUM(total) as omzet'),
                DB::raw('SUM(diskon) as diskon')
            )
            ->where('status', 'selesai')
            ->whereBetween('tanggal_penjualan', [$tanggalDari, $tanggalSampai])
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        $labels = $penjualanPerHari->pluck('tanggal')->map(function($date) {
            return Carbon::parse($date)->format('d/m');
        });
        $omzetData = $penjualanPerHari->pluck('omzet');
        $diskonData = $penjualanPerHari->pluck('diskon');

        // Data untuk bar chart per kategori
        $penjualanPerKategori = DB::table('penjualan_detail')
            ->join('penjualan', 'penjualan_detail.penjualan_id', '=', 'penjualan.id')
            ->join('barang', 'penjualan_detail.barang_id', '=', 'barang.id')
            ->join('kategori', 'barang.kategori_id', '=', 'kategori.id')
            ->select('kategori.nama_kategori', DB::raw('SUM(penjualan_detail.subtotal) as omzet'))
            ->where('penjualan.status', 'selesai')
            ->whereBetween('penjualan.tanggal_penjualan', [$tanggalDari, $tanggalSampai])
            ->groupBy('kategori.id', 'kategori.nama_kategori')
            ->orderBy('omzet', 'desc')
            ->limit(10)
            ->get();

        $kategoriLabels = $penjualanPerKategori->pluck('nama_kategori');
        $kategoriData = $penjualanPerKategori->pluck('omzet');

        return response()->json([
            'line_chart' => [
                'labels' => $labels,
                'omzet' => $omzetData,
                'diskon' => $diskonData,
            ],
            'bar_chart' => [
                'labels' => $kategoriLabels,
                'data' => $kategoriData,
            ]
        ]);
    }

    public function exportPDF(Request $request)
    {
        $query = Penjualan::with(['details.barang.kategori', 'creator'])
            ->select('penjualan.*')
            ->where('status', 'selesai');

        // Apply filters
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_penjualan', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_penjualan', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('kasir_id') && $request->kasir_id != 'all') {
            $query->where('created_by', $request->kasir_id);
        }
        if ($request->filled('kategori_id') && $request->kategori_id != 'all') {
            $query->whereHas('details.barang', function($q) use ($request) {
                $q->where('kategori_id', $request->kategori_id);
            });
        }

        $data = $query->get();

        // Calculate ringkasan manually instead of calling getRingkasan
        $ringkasanQuery = Penjualan::where('status', 'selesai');
        if ($request->filled('tanggal_dari')) {
            $ringkasanQuery->where('tanggal_penjualan', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $ringkasanQuery->where('tanggal_penjualan', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('kasir_id') && $request->kasir_id != 'all') {
            $ringkasanQuery->where('created_by', $request->kasir_id);
        }
        if ($request->filled('kategori_id') && $request->kategori_id != 'all') {
            $ringkasanQuery->whereHas('details.barang', function($q) use ($request) {
                $q->where('kategori_id', $request->kategori_id);
            });
        }

        $totalTransaksi = $ringkasanQuery->count();
        $totalOmzet = $ringkasanQuery->sum('total');
        $totalBarangTerjual = $ringkasanQuery->with('details')->get()->sum(function($penjualan) {
            return $penjualan->details->sum('qty');
        });
        $totalDiskon = $ringkasanQuery->sum('diskon');

        $ringkasan = [
            'total_transaksi' => $totalTransaksi,
            'total_omzet' => 'Rp ' . number_format($totalOmzet, 0, ',', '.'),
            'total_barang_terjual' => number_format($totalBarangTerjual, 2, ',', '.'),
            'total_diskon' => 'Rp ' . number_format($totalDiskon, 0, ',', '.'),
        ];

        $tanggalDari = $request->tanggal_dari ? Carbon::parse($request->tanggal_dari)->format('d/m/Y') : '-';
        $tanggalSampai = $request->tanggal_sampai ? Carbon::parse($request->tanggal_sampai)->format('d/m/Y') : '-';

        $pdf = Pdf::loadView('laporan.penjualan-harian-pdf', compact('data', 'ringkasan', 'tanggalDari', 'tanggalSampai'));

        return $pdf->download('laporan-penjualan-harian.pdf');
    }
}
