<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanPembelianController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        return view('laporan.pembelian', compact('suppliers'));
    }

    public function data(Request $request)
    {
        $query = Pembelian::with('supplier')
            ->select('pembelian.*');

        // Apply filters
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_pembelian', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_pembelian', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('supplier_id') && $request->supplier_id != 'all') {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('supplier_nama', function ($row) {
                return $row->supplier->nama_supplier ?? '-';
            })
            ->addColumn('tanggal_pembelian_formatted', function ($row) {
                return $row->tanggal_pembelian->format('d/m/Y');
            })
            ->addColumn('jumlah_item', function ($row) {
                return $row->details->sum('qty');
            })
            ->addColumn('jenis_pembayaran', function ($row) {
                return 'Tunai'; // Hardcode karena belum ada transfer
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status == 'selesai' ? '<span class="badge bg-success">Selesai</span>' : '<span class="badge bg-warning">Pending</span>';
            })
            ->addColumn('total_formatted', function ($row) {
                return 'Rp ' . number_format($row->total, 0, ',', '.');
            })
            ->rawColumns(['status_badge'])
            ->make(true);
    }

    public function getRingkasan(Request $request)
    {
        $query = Pembelian::where('status', 'selesai');

        // Filter rentang waktu
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_pembelian', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_pembelian', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('supplier_id') && $request->supplier_id != 'all') {
            $query->where('supplier_id', $request->supplier_id);
        }

        $totalTransaksi = $query->count();
        $totalNilai = $query->sum('total');
        $totalBarangMasuk = $query->with('details')->get()->sum(function($pembelian) {
            return $pembelian->details->sum('qty');
        });

        return response()->json([
            'total_transaksi' => $totalTransaksi,
            'total_nilai' => 'Rp ' . number_format($totalNilai, 0, ',', '.'),
            'total_barang_masuk' => number_format($totalBarangMasuk, 2, ',', '.'),
        ]);
    }

    public function getChartData(Request $request)
    {
        $tanggalDari = $request->tanggal_dari ?: date('Y-m-d', strtotime('-30 days'));
        $tanggalSampai = $request->tanggal_sampai ?: date('Y-m-d');

        // Data untuk line chart per hari
        $pembelianPerHari = Pembelian::select(
                DB::raw('DATE(tanggal_pembelian) as tanggal'),
                DB::raw('SUM(total) as total_nilai'),
                DB::raw('COUNT(*) as jumlah_transaksi')
            )
            ->where('status', 'selesai')
            ->whereBetween('tanggal_pembelian', [$tanggalDari, $tanggalSampai])
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        $labels = $pembelianPerHari->pluck('tanggal')->map(function($date) {
            return Carbon::parse($date)->format('d/m');
        });
        $nilaiData = $pembelianPerHari->pluck('total_nilai');

        // Data untuk bar chart per supplier
        $pembelianPerSupplier = \DB::table('pembelian')
            ->join('supplier', 'pembelian.supplier_id', '=', 'supplier.id')
            ->select('supplier.nama_supplier', \DB::raw('SUM(pembelian.total) as total_nilai'))
            ->where('pembelian.status', 'selesai')
            ->whereBetween('pembelian.tanggal_pembelian', [$tanggalDari, $tanggalSampai])
            ->groupBy('supplier.id', 'supplier.nama_supplier')
            ->orderBy('total_nilai', 'desc')
            ->limit(10)
            ->get();

        $supplierLabels = $pembelianPerSupplier->pluck('nama_supplier');
        $supplierData = $pembelianPerSupplier->pluck('total_nilai');

        return response()->json([
            'line_chart' => [
                'labels' => $labels,
                'nilai' => $nilaiData,
            ],
            'bar_chart' => [
                'labels' => $supplierLabels,
                'data' => $supplierData,
            ]
        ]);
    }

    public function indexPerSupplier()
    {
        $suppliers = Supplier::all();
        return view('laporan.pembelian-per-supplier', compact('suppliers'));
    }

    public function autocompleteSupplier(Request $request)
    {
        $term = $request->get('term');
        $suppliers = Supplier::where('nama_supplier', 'LIKE', '%' . $term . '%')
            ->select('id', 'nama_supplier')
            ->limit(10)
            ->get();

        return response()->json($suppliers);
    }

    public function dataPerSupplier(Request $request)
    {
        $supplierId = $request->supplier_id;
        if (!$supplierId) {
            return DataTables::of(collect())->make(true);
        }

        $query = \App\Models\PembelianDetail::with(['pembelian.supplier', 'barang', 'satuan'])
            ->join('pembelian', 'pembelian_detail.pembelian_id', '=', 'pembelian.id')
            ->where('pembelian.supplier_id', $supplierId)
            ->where('pembelian.status', 'selesai')
            ->select('pembelian_detail.*', 'pembelian.tanggal_pembelian', 'pembelian.kode_pembelian');

        // Filter rentang waktu
        if ($request->filled('tanggal_dari')) {
            $query->where('pembelian.tanggal_pembelian', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('pembelian.tanggal_pembelian', '<=', $request->tanggal_sampai);
        }

        return DataTables::of($query)
            ->addColumn('tanggal_pembelian_formatted', function ($row) {
                return \Carbon\Carbon::parse($row->tanggal_pembelian)->format('d/m/Y');
            })
            ->addColumn('nama_barang', function ($row) {
                return $row->barang->nama_barang ?? '-';
            })
            ->addColumn('qty_formatted', function ($row) {
                return number_format($row->qty, 2, ',', '.');
            })
            ->addColumn('satuan', function ($row) {
                return $row->satuan->nama_satuan ?? '-';
            })
            ->addColumn('harga_formatted', function ($row) {
                return 'Rp ' . number_format($row->harga_beli, 0, ',', '.');
            })
            ->addColumn('total_formatted', function ($row) {
                return 'Rp ' . number_format($row->subtotal, 0, ',', '.');
            })
            ->rawColumns([])
            ->make(true);
    }

    public function exportPDF(Request $request)
    {
        $query = Pembelian::with('supplier')
            ->select('pembelian.*');

        // Apply filters
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_pembelian', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_pembelian', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('supplier_id') && $request->supplier_id != 'all') {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $data = $query->get();
        $totalAkumulasi = $data->sum('total');

        // Calculate ringkasan
        $ringkasanQuery = Pembelian::where('status', 'selesai');
        if ($request->filled('tanggal_dari')) {
            $ringkasanQuery->where('tanggal_pembelian', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $ringkasanQuery->where('tanggal_pembelian', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('supplier_id') && $request->supplier_id != 'all') {
            $ringkasanQuery->where('supplier_id', $request->supplier_id);
        }

        $totalTransaksi = $ringkasanQuery->count();
        $totalNilai = $ringkasanQuery->sum('total');
        $totalBarangMasuk = $ringkasanQuery->with('details')->get()->sum(function($pembelian) {
            return $pembelian->details->sum('qty');
        });

        $ringkasan = [
            'total_transaksi' => $totalTransaksi,
            'total_nilai' => 'Rp ' . number_format($totalNilai, 0, ',', '.'),
            'total_barang_masuk' => number_format($totalBarangMasuk, 2, ',', '.'),
        ];

        $tanggalDari = $request->tanggal_dari ? Carbon::parse($request->tanggal_dari)->format('d/m/Y') : '-';
        $tanggalSampai = $request->tanggal_sampai ? Carbon::parse($request->tanggal_sampai)->format('d/m/Y') : '-';

        $pdf = Pdf::loadView('laporan.pembelian-pdf', compact('data', 'totalAkumulasi', 'ringkasan', 'tanggalDari', 'tanggalSampai'));

        return $pdf->download('laporan-pembelian.pdf');
    }

    public function exportPDFPerSupplier(Request $request)
    {
        $supplierId = $request->supplier_id;
        if (!$supplierId) {
            return redirect()->back()->with('error', 'Supplier harus dipilih untuk export PDF.');
        }

        $supplier = Supplier::find($supplierId);

        $query = \App\Models\PembelianDetail::with(['pembelian.supplier', 'barang', 'satuan'])
            ->join('pembelian', 'pembelian_detail.pembelian_id', '=', 'pembelian.id')
            ->where('pembelian.supplier_id', $supplierId)
            ->where('pembelian.status', 'selesai')
            ->select('pembelian_detail.*', 'pembelian.tanggal_pembelian', 'pembelian.kode_pembelian');

        // Filter rentang waktu
        if ($request->filled('tanggal_dari')) {
            $query->where('pembelian.tanggal_pembelian', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('pembelian.tanggal_pembelian', '<=', $request->tanggal_sampai);
        }

        $data = $query->get();
        $totalAkumulasi = $data->sum('subtotal');

        // Calculate ringkasan
        $ringkasanQuery = Pembelian::where('supplier_id', $supplierId)->where('status', 'selesai');
        if ($request->filled('tanggal_dari')) {
            $ringkasanQuery->where('tanggal_pembelian', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $ringkasanQuery->where('tanggal_pembelian', '<=', $request->tanggal_sampai);
        }

        $totalTransaksi = $ringkasanQuery->count();
        $totalNilai = $ringkasanQuery->sum('total');
        $totalBarangMasuk = $ringkasanQuery->with('details')->get()->sum(function($pembelian) {
            return $pembelian->details->sum('qty');
        });

        $ringkasan = [
            'total_transaksi' => $totalTransaksi,
            'total_nilai' => 'Rp ' . number_format($totalNilai, 0, ',', '.'),
            'total_barang_masuk' => number_format($totalBarangMasuk, 2, ',', '.'),
        ];

        $tanggalDari = $request->tanggal_dari ? Carbon::parse($request->tanggal_dari)->format('d/m/Y') : '-';
        $tanggalSampai = $request->tanggal_sampai ? Carbon::parse($request->tanggal_sampai)->format('d/m/Y') : '-';

        $pdf = Pdf::loadView('laporan.pembelian-per-supplier-pdf', compact('data', 'totalAkumulasi', 'ringkasan', 'tanggalDari', 'tanggalSampai', 'supplier'));

        return $pdf->download('laporan-pembelian-per-supplier.pdf');
    }
}
