<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StokOpname;
use App\Models\StokOpnameDetail;
use App\Models\Barang;
use Carbon\Carbon;

class StokOpnameController extends Controller
{
    public function index()
    {
        $stokOpnames = StokOpname::with('user')->orderBy('tanggal', 'desc')->paginate(10);
        return view('stok-opname.index', compact('stokOpnames'));
    }

    public function create()
    {
        $barangs = Barang::where('status', 'aktif')->get();
        return view('stok-opname.create', compact('barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'catatan' => 'nullable|string',
            'barang' => 'required|array',
            'barang.*.stok_fisik' => 'required|numeric|min:0',
            'barang.*.keterangan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
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
            ]);

            // Create details
            foreach ($request->barang as $barangId => $data) {
                $barang = Barang::find($barangId);
                $stokSistem = $barang->stok;
                $stokFisik = $data['stok_fisik'];
                $selisih = $stokFisik - $stokSistem;

                StokOpnameDetail::create([
                    'stok_opname_id' => $stokOpname->id,
                    'barang_id' => $barangId,
                    'stok_sistem' => $stokSistem,
                    'stok_fisik' => $stokFisik,
                    'selisih' => $selisih,
                    'keterangan' => $data['keterangan'] ?? null,
                ]);
            }
        });

        return redirect()->route('stok-opname.index')->with('success', 'Stok opname berhasil dibuat.');
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
}
