<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StokOpname;
use App\Models\StokOpnameDetail;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanStokOpnameController extends Controller
{
    public function index()
    {
        return view('laporan.stok-opname.index');
    }

    public function getData(Request $request)
    {
        $query = StokOpname::with('user')
            ->select('stok_opname.*');

        // Filter berdasarkan tanggal jika ada
        if ($request->has('tanggal_dari') && $request->tanggal_dari) {
            $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->has('tanggal_sampai') && $request->tanggal_sampai) {
            $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
        }

        // Filter berdasarkan status
        if ($request->has('status') && $request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('tanggal_opname_formatted', function($row) {
                return \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y');
            })
            ->addColumn('petugas', function($row) {
                return $row->user ? $row->user->name : '-';
            })
            ->addColumn('status_badge', function($row) {
                $badgeClass = match($row->status) {
                    'draft' => 'secondary',
                    'selesai' => 'success',
                    'batal' => 'danger',
                    default => 'secondary'
                };
                return '<span class="badge bg-' . $badgeClass . '">' . ucfirst($row->status) . '</span>';
            })
            ->addColumn('action', function($row) {
                $btn = '<a href="' . route('laporan.stok-opname.show', $row->id) . '" class="btn btn-sm btn-info">Detail</a>';
                $btn .= ' <a href="' . route('laporan.stok-opname.export-pdf', $row->id) . '" target="_blank" class="btn btn-sm btn-danger">PDF</a>';
                return $btn;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $stokOpname = StokOpname::with('user', 'details.barang.kategori', 'details.barang.satuan')->findOrFail($id);
        return view('laporan.stok-opname.show', compact('stokOpname'));
    }

    public function exportPDF($id)
    {
        $stokOpname = StokOpname::with('user', 'details.barang.kategori', 'details.barang.satuan')->findOrFail($id);

        $pdf = Pdf::loadView('laporan.stok-opname.pdf', compact('stokOpname'));
        return $pdf->download('laporan-stok-opname-' . $stokOpname->kode_opname . '.pdf');
    }
}
