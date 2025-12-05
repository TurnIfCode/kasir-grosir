<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanPenjualanExport;
use Carbon\Carbon;
use DB;

class LaporanPenjualanController extends Controller
{
    public function index()
    {
        $pelanggans = Pelanggan::all();
        return view('laporan.penjualan', compact('pelanggans'));
    }

    public function data(Request $request)
    {
        $query = Penjualan::with(['pelanggan', 'details', 'pembayarans', 'creator'])
            ->leftJoin('users', 'penjualan.created_by', '=', 'users.id')
            ->select([
                'penjualan.id',
                'penjualan.kode_penjualan',
                'penjualan.tanggal_penjualan',
                'penjualan.pelanggan_id',
                'penjualan.total',
                'penjualan.diskon',
                'penjualan.ppn',
                'penjualan.pembulatan',
                'penjualan.grand_total',
                'penjualan.jenis_pembayaran',
                'penjualan.dibayar',
                'penjualan.kembalian',
                'penjualan.catatan',
                'penjualan.status',
                'penjualan.created_by',
                'penjualan.updated_by',
                'penjualan.created_at',
                'penjualan.updated_at',
                'users.name as kasir_name',
                DB::raw('ROUND(COALESCE(SUM(penjualan_detail.qty_konversi), 0)) as jumlah_item'),
                DB::raw('COALESCE(SUM(penjualan_detail.qty_konversi * penjualan_detail.harga_beli), 0) as total_hpp'),
                DB::raw('(penjualan.total - COALESCE(SUM(penjualan_detail.qty_konversi * penjualan_detail.harga_beli), 0)) as laba')
            ])
            ->leftJoin('penjualan_detail', 'penjualan.id', '=', 'penjualan_detail.penjualan_id')
            ->groupBy('penjualan.id', 'penjualan.kode_penjualan', 'penjualan.tanggal_penjualan', 'penjualan.pelanggan_id', 'penjualan.total', 'penjualan.diskon', 'penjualan.ppn', 'penjualan.pembulatan', 'penjualan.grand_total', 'penjualan.jenis_pembayaran', 'penjualan.dibayar', 'penjualan.kembalian', 'penjualan.catatan', 'penjualan.status', 'penjualan.created_by', 'penjualan.updated_by', 'penjualan.created_at', 'penjualan.updated_at', 'users.name');

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

            ->addColumn('pembulatan_formatted', function ($row) {
                return 'Rp ' . number_format($row->pembulatan, 0, ',', '.');
            })
            ->addColumn('grand_total_formatted', function ($row) {
                return 'Rp ' . number_format($row->grand_total, 0, ',', '.');
            })
            ->addColumn('dibayar_formatted', function ($row) {
                return 'Rp ' . number_format($row->dibayar, 0, ',', '.');
            })
            ->addColumn('kembalian_formatted', function ($row) {
                return 'Rp ' . number_format($row->kembalian, 0, ',', '.');
            })
            ->addColumn('total_modal_formatted', function ($row) {
                return 'Rp ' . number_format($row->total_hpp, 0, ',', '.');
            })
            ->addColumn('laba_kotor_formatted', function ($row) {
                return 'Rp ' . number_format($row->laba, 0, ',', '.');
            })
            ->addColumn('laba_bersih_formatted', function ($row) {
                return 'Rp ' . number_format($row->laba, 0, ',', '.');
            })
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-sm btn-info detail-btn" data-id="' . $row->id . '">Detail</button>';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function exportPDF(Request $request)
    {
        $query = Penjualan::with(['pelanggan', 'details', 'creator'])
            ->leftJoin('users', 'penjualan.created_by', '=', 'users.id')
            ->select([
                'penjualan.id',
                'penjualan.kode_penjualan',
                'penjualan.tanggal_penjualan',
                'penjualan.pelanggan_id',
                'penjualan.total',
                'penjualan.diskon',
                'penjualan.ppn',
                'penjualan.pembulatan',
                'penjualan.grand_total',
                'penjualan.jenis_pembayaran',
                'penjualan.dibayar',
                'penjualan.kembalian',
                'penjualan.catatan',
                'penjualan.status',
                'penjualan.created_by',
                'penjualan.updated_by',
                'penjualan.created_at',
                'penjualan.updated_at',
                'users.name as kasir_name',
                DB::raw('ROUND(COALESCE(SUM(penjualan_detail.qty_konversi), 0)) as jumlah_item'),
                DB::raw('COALESCE(SUM(penjualan_detail.qty_konversi * penjualan_detail.harga_beli), 0) as total_hpp'),
                DB::raw('(penjualan.total - COALESCE(SUM(penjualan_detail.qty_konversi * penjualan_detail.harga_beli), 0)) as laba')
            ])
            ->leftJoin('penjualan_detail', 'penjualan.id', '=', 'penjualan_detail.penjualan_id')
            ->groupBy('penjualan.id', 'penjualan.kode_penjualan', 'penjualan.tanggal_penjualan', 'penjualan.pelanggan_id', 'penjualan.total', 'penjualan.diskon', 'penjualan.ppn', 'penjualan.pembulatan', 'penjualan.grand_total', 'penjualan.jenis_pembayaran', 'penjualan.dibayar', 'penjualan.kembalian', 'penjualan.catatan', 'penjualan.status', 'penjualan.created_by', 'penjualan.updated_by', 'penjualan.created_at', 'penjualan.updated_at', 'users.name');

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

        // Calculate summaries
        $summary = [
            'total_transaksi' => $data->count(),
            'total_penjualan' => $data->sum('total'),
            'total_pembulatan' => $data->sum('pembulatan'),
            'total_laba_kotor' => $data->sum('grand_total'),
            'total_modal' => $data->sum('total_hpp'),
            'total_laba_bersih' => $data->sum('laba')
        ];

        $tanggalDari = $request->tanggal_dari ? Carbon::parse($request->tanggal_dari)->format('d/m/Y') : '-';
        $tanggalSampai = $request->tanggal_sampai ? Carbon::parse($request->tanggal_sampai)->format('d/m/Y') : '-';

        $pdf = Pdf::loadView('laporan.penjualan-pdf', compact('data', 'summary', 'tanggalDari', 'tanggalSampai'));

        return $pdf->download('laporan-penjualan.pdf');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new LaporanPenjualanExport($request), 'laporan-penjualan.xlsx');
    }

    public function detail($id)
    {
        $penjualan = Penjualan::with(['details.barang', 'pembayarans'])->findOrFail($id);

        $totalModal = $penjualan->details->sum(function ($detail) {
            return $detail->qty_konversi * $detail->harga_beli;
        });

        $totalLaba = $penjualan->total - $totalModal;

        return view('laporan.penjualan-detail', compact('penjualan', 'totalModal', 'totalLaba'));
    }
}
