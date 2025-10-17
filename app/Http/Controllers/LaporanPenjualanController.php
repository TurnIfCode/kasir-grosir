<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanPenjualanController extends Controller
{
    public function index()
    {
        $pelanggans = Pelanggan::all();
        return view('laporan.penjualan', compact('pelanggans'));
    }

    public function data(Request $request)
    {
        $query = Penjualan::with('pelanggan')
            ->select('penjualan.*');

        // Apply filters
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_penjualan', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_penjualan', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('pelanggan_id') && $request->pelanggan_id != 'all') {
            $query->where('pelanggan_id', $request->pelanggan_id);
        }
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('metode_pembayaran') && $request->metode_pembayaran != 'all') {
            if ($request->metode_pembayaran == 'tunai') {
                $query->where('jenis_pembayaran', 'tunai');
            } else {
                $query->whereIn('jenis_pembayaran', ['non_tunai', 'campuran']);
            }
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nama_pelanggan', function ($row) {
                return $row->pelanggan->nama_pelanggan ?? 'Umum';
            })
            ->addColumn('tanggal_penjualan_formatted', function ($row) {
                return $row->tanggal_penjualan->format('d/m/Y');
            })
            ->addColumn('metode_pembayaran', function ($row) {
                return $row->jenis_pembayaran == 'tunai' ? 'tunai' : 'non-tunai';
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status == 'selesai' ? '<span class="badge bg-success">selesai</span>' : '<span class="badge bg-warning">pending</span>';
            })
            ->addColumn('total_formatted', function ($row) {
                return 'Rp ' . number_format($row->total, 0, ',', '.');
            })
            ->rawColumns(['status_badge'])
            ->make(true);
    }

    public function exportPDF(Request $request)
    {
        $query = Penjualan::with('pelanggan')
            ->select('penjualan.*');

        // Apply filters
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_penjualan', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_penjualan', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('pelanggan_id') && $request->pelanggan_id != 'all') {
            $query->where('pelanggan_id', $request->pelanggan_id);
        }
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('metode_pembayaran') && $request->metode_pembayaran != 'all') {
            if ($request->metode_pembayaran == 'tunai') {
                $query->where('jenis_pembayaran', 'tunai');
            } else {
                $query->whereIn('jenis_pembayaran', ['non_tunai', 'campuran']);
            }
        }

        $data = $query->get();
        $totalAkumulasi = $data->sum('total');

        $tanggalDari = $request->tanggal_dari ? Carbon::parse($request->tanggal_dari)->format('d/m/Y') : '-';
        $tanggalSampai = $request->tanggal_sampai ? Carbon::parse($request->tanggal_sampai)->format('d/m/Y') : '-';

        $pdf = Pdf::loadView('laporan.penjualan-pdf', compact('data', 'totalAkumulasi', 'tanggalDari', 'tanggalSampai'));

        return $pdf->download('laporan-penjualan.pdf');
    }
}
