<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Supplier;
use Illuminate\Http\Request;
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
            ->addColumn('status_badge', function ($row) {
                return $row->status == 'selesai' ? '<span class="badge bg-success">Selesai</span>' : '<span class="badge bg-warning">Pending</span>';
            })
            ->addColumn('total_formatted', function ($row) {
                return 'Rp ' . number_format($row->total, 0, ',', '.');
            })
            ->rawColumns(['status_badge'])
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

        $tanggalDari = $request->tanggal_dari ? Carbon::parse($request->tanggal_dari)->format('d/m/Y') : '-';
        $tanggalSampai = $request->tanggal_sampai ? Carbon::parse($request->tanggal_sampai)->format('d/m/Y') : '-';

        $pdf = Pdf::loadView('laporan.pembelian-pdf', compact('data', 'totalAkumulasi', 'tanggalDari', 'tanggalSampai'));

        return $pdf->download('laporan-pembelian.pdf');
    }
}
