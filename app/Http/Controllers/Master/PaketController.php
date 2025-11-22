<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paket;
use App\Models\PaketDetail;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class PaketController extends Controller
{
    public function index(Request $request)
    {
        return $this->data($request);
    }

    public function add()
    {
        return view('master.paket.create');
    }

    public function find($id)
    {
        $paket = Paket::with(['details.barang', 'creator', 'updater'])->find($id);
        if (!$paket) {
            return response()->json([
                'status' => false,
                'message' => 'Paket tidak ditemukan'
            ]);
        }

        $data = $paket->toArray();
        $data['created_by'] = $paket->creator ? $paket->creator->name : '-';
        $data['updated_by'] = $paket->updater ? $paket->updater->name : '-';
        $data['harga_per_3'] = number_format($paket->harga_per_3, 0, ',', '.');
        $data['harga_per_unit'] = number_format($paket->harga_per_unit, 0, ',', '.');
        $data['daftar_barang'] = $paket->details->map(function($detail) {
            return [
                'id' => $detail->barang->id,
                'nama_barang' => $detail->barang->nama_barang,
                'jumlah' => $detail->jumlah,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function delete($id)
    {
        $paket = Paket::find($id);
        if (!$paket) {
            return response()->json([
                'status' => false,
                'message' => 'Paket tidak ditemukan'
            ]);
        }

        $namaPaket = $paket->nama_paket;
        $kodePaket = $paket->kode_paket;

        $paket->delete();

        $newLog = new Log();
        $newLog->keterangan = 'Menghapus paket: ' . $namaPaket . ' (Kode Paket: ' . $kodePaket . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'status' => true,
            'message' => 'Paket berhasil dihapus'
        ]);
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search') ? $request->get('search')['value'] : '';

            $query = Paket::with('details.barang');

            if (!empty($search)) {
                $query->where('kode_paket', 'like', '%' . $search . '%')
                      ->orWhere('nama_paket', 'like', '%' . $search . '%');
            }

            $totalRecords = Paket::count();
            $filteredRecords = $query->count();

            $pakets = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($pakets as $paket) {
                // Ambil daftar nama barang saja
                $barangList = $paket->details->map(function($detail) {
                    return $detail->barang->nama_barang;
                })->implode(', ');

                $data[] = [
                    'id' => $paket->id,
                    'kode_paket' => $paket->kode_paket,
                    'nama_paket' => $paket->nama_paket,
                    'harga_per_3' => 'Rp ' . number_format($paket->harga_per_3, 0, ',', '.'),
                    'harga_per_unit' => 'Rp ' . number_format($paket->harga_per_unit, 0, ',', '.'),
                    'keterangan' => $paket->keterangan ?: '-',
                    'daftar_barang' => $barangList ?: '-', // Kolom baru untuk daftar barang
                    'aksi' => '<a href="#" id="btnDetail" data-id="' . $paket->id . '" class="btn btn-sm btn-info me-1"><i class="fas fa-eye"></i></a> <a href="' . route('paket.edit', $paket->id) . '" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i></a> <a href="#" data-id="' . $paket->id . '" id="btnDelete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>'
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('master.paket.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_paket' => 'required|string|max:50|unique:paket,kode_paket',
            'nama_paket' => 'required|string|max:100',
            'harga_per_3' => 'required|integer|min:0',
            'harga_per_unit' => 'required|integer|min:0',
            'daftar_barang' => 'required|array|min:1',
            'daftar_barang.*' => 'required|integer|exists:barang,id',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $paket = Paket::create([
            'kode_paket' => $request->kode_paket,
            'nama_paket' => $request->nama_paket,
            'harga_per_3' => $request->harga_per_3,
            'harga_per_unit' => $request->harga_per_unit,
            'keterangan' => $request->keterangan,
            'created_by' => Auth::id(), // Tambahkan created_by
        ]);

        foreach ($request->daftar_barang as $barang_id) {
            PaketDetail::create([
                'paket_id' => $paket->id,
                'barang_id' => $barang_id,
                'created_by' => Auth::id(),
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Paket berhasil ditambahkan'
        ]);
    }

    public function edit($id)
    {
        $paket = Paket::with('details.barang')->find($id);
        if (!$paket) {
            return redirect()->route('paket.index')->with('error', 'Paket tidak ditemukan');
        }

        return view('master.paket.edit', compact('paket'));
    }

    public function update(Request $request, $id)
    {
        $paket = Paket::find($id);
        if (!$paket) {
            return response()->json([
                'status' => false,
                'message' => 'Paket tidak ditemukan'
            ]);
        }

        $request->validate([
            'kode_paket' => 'required|string|max:50|unique:paket,kode_paket,' . $id,
            'nama_paket' => 'required|string|max:100',
            'harga_per_3' => 'required|integer|min:0',
            'harga_per_unit' => 'required|integer|min:0',
            'daftar_barang' => 'required|array|min:1',
            'daftar_barang.*' => 'required|integer|exists:barang,id',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $paket->update([
            'kode_paket' => $request->kode_paket,
            'nama_paket' => $request->nama_paket,
            'harga_per_3' => $request->harga_per_3,
            'harga_per_unit' => $request->harga_per_unit,
            'keterangan' => $request->keterangan,
            'updated_by' => Auth::id(), // Tambahkan updated_by
        ]);

        // Delete existing details
        $paket->details()->delete();

        // Create new details
        foreach ($request->daftar_barang as $barang_id) {
            PaketDetail::create([
                'paket_id' => $paket->id,
                'barang_id' => $barang_id,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(), // Tambahkan updated_by untuk detail
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Paket berhasil diperbarui'
        ]);
    }
}