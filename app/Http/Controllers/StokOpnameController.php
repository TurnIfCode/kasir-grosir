<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StokOpname;
use App\Models\StokOpnameDetail;
use App\Models\Log;
use App\Models\Barang;
use Carbon\Carbon;

use Yajra\DataTables\Facades\DataTables;

class StokOpnameController extends Controller
{
    public function index()
    {
        $stokOpnames = StokOpname::with('user')->orderBy('tanggal', 'desc')->paginate(10);
        return view('stok-opname.index', compact('stokOpnames'));
    }

    public function create()
    {
        return view('stok-opname.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'catatan' => 'nullable|string',
        ]);

        // Generate kode opname
        $tanggal = Carbon::parse($request->tanggal)->format('Ymd');
        $increment = StokOpname::whereDate('tanggal', $request->tanggal)->count() + 1;
        $kodeOpname = 'OPN-' . $tanggal . '-' . str_pad($increment, 3, '0', STR_PAD_LEFT);

        // Create stok opname
        $stokOpname = StokOpname::create([
            'kode_opname' => $kodeOpname,
            'tanggal' => $request->tanggal,
            'user_id' => auth()->id(),
            'catatan' => $request->catatan,
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $log = new Log();
        $log->keterangan = 'Membuat stok opname dengan kode ' . $kodeOpname.'Tanggal: '.$stokOpname->tanggal.' Catatatan: '.$stokOpname->catatan.' Status: '.$stokOpname->status.'.';
        $log->created_by = auth()->id();
        $log->created_at = now();
        $log->save();

        return response()->json([
            'success' => true,
            'stok_opname_id' => $stokOpname->id,
            'message' => 'Stok opname draft berhasil dibuat.'
        ]);
    }

    public function show($id)
    {
        $stokOpname = StokOpname::with(['user', 'details.barang'])->findOrFail($id);
        return view('stok-opname.show', compact('stokOpname'));
    }

    public function updateStatus(Request $request, $id)
    {
        $stokOpname = StokOpname::findOrFail($id);

        if ($stokOpname->status !== 'draft') {
            return redirect()->back()->with('error', 'Status sudah tidak bisa diubah.');
        }

        DB::transaction(function () use ($stokOpname) {
            // Update status to selesai
            $stokOpname->update(['status' => 'selesai']);

            // Update stok barang berdasarkan stok fisik
            foreach ($stokOpname->details as $detail) {
                $detail->barang->update(['stok' => $detail->stok_fisik]);
            }
        });

        return redirect()->back()->with('success', 'Stok opname telah diselesaikan dan stok barang telah disesuaikan.');
    }

    public function destroy($id)
    {
        $stokOpname = StokOpname::findOrFail($id);

        if ($stokOpname->status === 'selesai') {
            return redirect()->back()->with('error', 'Stok opname yang sudah selesai tidak bisa dihapus.');
        }

        $stokOpname->details()->delete();
        $stokOpname->delete();

        return redirect()->route('stok-opname.index')->with('success', 'Stok opname berhasil dihapus.');
    }

    public function getBarangByKategori(Request $request)
    {
        $kategoriId = $request->kategori_id;

        $barangs = Barang::with(['kategori', 'satuan'])
            ->when($kategoriId, fn($q) => $q->where('kategori_id', $kategoriId))
            ->select([
                'id',
                'kode_barang',
                'nama_barang',
                'stok',
                'kategori_id',
                'satuan_id'
            ])
            ->get();

        return DataTables::of($barangs)
            ->addIndexColumn()
            ->addColumn('nama_kategori', fn($row) => $row->kategori->nama_kategori ?? '-')
            ->addColumn('nama_satuan', fn($row) => $row->satuan->nama_satuan ?? '-')
            ->addColumn('stok_sistem', fn($row) => round($row->stok))
            ->addColumn('stok_fisik_input', function($row) {
                return '<input type="number" class="form-control stok-fisik" name="barang[' . $row->id . '][stok_fisik]" step="1" min="0" required>';
            })
            ->addColumn('selisih_input', function($row) {
                return '<input type="number" class="form-control selisih" readonly step="1">';
            })
            ->addColumn('keterangan_input', function($row) {
                return '<input type="text" class="form-control" name="barang[' . $row->id . '][keterangan]">';
            })
            ->rawColumns(['stok_fisik_input', 'selisih_input', 'keterangan_input'])
            ->make(true);
    }

    public function addDetail(Request $request, $id)
    {
        $request->validate([
            'barang_id' => 'required|integer|exists:barang,id',
            'satuan_id' => 'required|integer|exists:satuan,id',
            'stok_fisik' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $stokOpname = StokOpname::findOrFail($id);

        if ($stokOpname->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Stok opname sudah tidak bisa diubah.'
            ]);
        }

        // Get barang to validate satuan_id
        $barang = Barang::findOrFail($request->barang_id);

        // Ensure satuan_id matches barang's satuan_id (not from konversi_satuan)
        if ($request->satuan_id != $barang->satuan_id) {
            return response()->json([
                'success' => false,
                'message' => 'Satuan harus sesuai dengan satuan dasar barang.'
            ]);
        }

        // Check if detail already exists for this barang
        $existingDetail = $stokOpname->details()->where('barang_id', $request->barang_id)->first();
        if ($existingDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Barang sudah ditambahkan ke opname ini.'
            ]);
        }

        // Get stok sistem from barang
        $stokSistem = $barang->stok;

        // Calculate selisih
        $selisih = round($request->stok_fisik - $stokSistem);

        $detail = StokOpnameDetail::create([
            'stok_opname_id' => $id,
            'barang_id' => $request->barang_id,
            'satuan_id' => $request->satuan_id,
            'stok_sistem' => $stokSistem,
            'stok_fisik' => $request->stok_fisik,
            'selisih' => $selisih,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail barang berhasil ditambahkan.',
            'data' => $detail->load(['barang', 'satuan'])
        ]);
    }

    public function finishOpname(Request $request, $id)
    {
        $stokOpname = StokOpname::findOrFail($id);

        if ($stokOpname->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Stok opname sudah selesai.'
            ]);
        }

        DB::transaction(function () use ($stokOpname) {
            // Update status to selesai
            $stokOpname->update(['status' => 'selesai']);

            // Update stok barang berdasarkan stok fisik
            foreach ($stokOpname->details as $detail) {
                $detail->barang->update(['stok' => $detail->stok_fisik]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Stok opname telah diselesaikan dan stok barang telah disesuaikan.'
        ]);
    }

    public function deleteDetail($detailId)
    {
        $detail = StokOpnameDetail::findOrFail($detailId);
        $stokOpname = $detail->stokOpname;

        if ($stokOpname->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Stok opname sudah selesai, tidak bisa menghapus detail.'
            ]);
        }

        $detail->delete();

        return response()->json([
            'success' => true,
            'message' => 'Detail berhasil dihapus.'
        ]);
    }
}
