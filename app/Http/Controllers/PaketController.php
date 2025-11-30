<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\PaketDetail;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaketController extends Controller
{
    public function index()
    {
        $paket = Paket::with('details.barang')->paginate(10);
        return view('paket.index', compact('paket'));
    }

    public function create()
    {
        return view('paket.add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'total_qty' => 'required|integer|min:1',
            'harga' => 'required|integer|min:0',
            'status' => 'required|in:aktif,nonaktif',
            'barang_ids' => 'required|array',
            'barang_ids.*' => 'integer|exists:barang,id',
        ]);

        DB::beginTransaction();
        try {
            $paket = Paket::create([
                'nama' => $request->nama,
                'total_qty' => round($request->total_qty),
                'harga' => $request->harga,
                'status' => $request->status,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            foreach ($request->barang_ids as $barang_id) {
                PaketDetail::create([
                    'paket_id' => $paket->id,
                    'barang_id' => $barang_id,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }
            DB::commit();
            return redirect()->route('paket.index')->with('success', 'Paket created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create paket: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $paket = Paket::with('details.barang')->findOrFail($id);
        return view('paket.edit', compact('paket'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'total_qty' => 'required|integer|min:1',
            'harga' => 'required|integer|min:0',
            'status' => 'required|in:aktif,nonaktif',
            'barang_ids' => 'required|array',
            'barang_ids.*' => 'integer|exists:barang,id',
        ]);

        DB::beginTransaction();
        try {
            $paket = Paket::findOrFail($id);
            $paket->update([
                'nama' => $request->nama,
                'total_qty' => $request->total_qty,
                'harga' => $request->harga,
                'status' => $request->status,
                'updated_by' => Auth::id(),
            ]);

            // Sync paket details
            // Delete details not in submitted barang_ids
            PaketDetail::where('paket_id', $id)
                ->whereNotIn('barang_id', $request->barang_ids)
                ->delete();

            // Add new barang_ids not already in details
            $existingBarangIds = PaketDetail::where('paket_id', $id)->pluck('barang_id')->toArray();

            foreach ($request->barang_ids as $barang_id) {
                if (!in_array($barang_id, $existingBarangIds)) {
                    PaketDetail::create([
                        'paket_id' => $id,
                        'barang_id' => $barang_id,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('paket.index')->with('success', 'Paket updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update paket: ' . $e->getMessage()]);
        }
    }
}
