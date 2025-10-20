<?php

namespace App\Http\Controllers;

use App\Models\HargaBarang;
use App\Models\Barang;
use App\Models\Satuan;
use Illuminate\Http\Request;

class HargaBarangController extends Controller
{
    public function index()
    {
        $barang = Barang::where('status', 'aktif')->get();
        $satuan = Satuan::where('status', 'aktif')->get();
        return view('harga.index', compact('barang', 'satuan'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search') ? $request->get('search')['value'] : '';

            $query = HargaBarang::with('barang', 'satuan');

            if (!empty($search)) {
                $query->whereHas('barang', function($q) use ($search) {
                    $q->where('nama_barang', 'like', '%' . $search . '%')
                      ->orWhere('kode_barang', 'like', '%' . $search . '%');
                })->orWhereHas('satuan', function($q) use ($search) {
                    $q->where('nama_satuan', 'like', '%' . $search . '%');
                })->orWhere('tipe_harga', 'like', '%' . $search . '%')
                  ->orWhere('status', 'like', '%' . $search . '%');
            }

            $totalRecords = HargaBarang::count();
            $filteredRecords = $query->count();

            $hargaBarang = $query->skip($start)->take($length)->get();

            $data = [];
            $no = $start + 1;
            foreach ($hargaBarang as $h) {
                $data[] = [
                    'DT_RowIndex' => $no++,
                    'barang' => $h->barang ? $h->barang->nama_barang : '-',
                    'satuan' => $h->satuan ? $h->satuan->nama_satuan : '-',
                    'tipe_harga' => $h->tipe_harga,
                    'harga' => 'Rp ' . number_format($h->harga, 0, ',', '.'),
                    'status' => $h->status,
                    'aksi' => '<a href="#" id="btnDetail" data-id="' . $h->id . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a> <a href="#" id="btnEdit" data-id="' . $h->id . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a> <a href="#" data-id="' . $h->id . '" id="btnDelete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>'
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('harga.index');
    }

    public function create()
    {
        $barang = Barang::where('status', 'aktif')->get();
        $satuan = Satuan::where('status', 'aktif')->get();
        return view('harga.create', compact('barang', 'satuan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'harga_data' => 'required|array|min:1',
            'harga_data.*.barang_id' => 'required|exists:barang,id',
            'harga_data.*.satuan_id' => 'required|exists:satuan,id',
            'harga_data.*.tipe_harga' => 'required|string',
            'harga_data.*.harga' => 'required|numeric|min:0.01',
            'harga_data.*.status' => 'required|in:aktif,nonaktif'
        ]);

        $savedCount = 0;
        $errors = [];

        foreach ($request->harga_data as $index => $data) {
            // Check for duplicate combination
            $existing = HargaBarang::where('barang_id', $data['barang_id'])
                                   ->where('satuan_id', $data['satuan_id'])
                                   ->where('tipe_harga', $data['tipe_harga'])
                                   ->first();

            if ($existing) {
                $errors[] = "Baris " . ($index + 1) . ": Kombinasi barang, satuan, dan tipe harga sudah ada";
                continue;
            }

            HargaBarang::create([
                'barang_id' => $data['barang_id'],
                'satuan_id' => $data['satuan_id'],
                'tipe_harga' => $data['tipe_harga'],
                'harga' => $data['harga'],
                'status' => $data['status'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $savedCount++;
        }

        if ($savedCount > 0) {
            return redirect()->route('harga-barang.index')->with('success', $savedCount . ' harga barang berhasil ditambahkan');
        } else {
            return redirect()->back()->withErrors($errors)->withInput();
        }
    }

    public function edit($id)
    {
        $hargaBarang = HargaBarang::findOrFail($id);
        $barang = Barang::where('status', 'aktif')->get();
        $satuan = Satuan::where('status', 'aktif')->get();
        return view('harga.edit', compact('hargaBarang', 'barang', 'satuan'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'satuan_id' => 'required|exists:satuan,id',
            'tipe_harga' => 'required|string',
            'harga' => 'required|numeric|min:0.01',
            'status' => 'required|in:aktif,nonaktif'
        ]);

        $hargaBarang = HargaBarang::findOrFail($id);

        // Check for duplicate combination (excluding current record)
        $existing = HargaBarang::where('barang_id', $request->barang_id)
                               ->where('satuan_id', $request->satuan_id)
                               ->where('tipe_harga', $request->tipe_harga)
                               ->where('id', '!=', $id)
                               ->first();

        if ($existing) {
            return redirect()->back()->withErrors(['error' => 'Kombinasi barang, satuan, dan tipe harga sudah ada'])->withInput();
        }

        $hargaBarang->update([
            'barang_id' => $request->barang_id,
            'satuan_id' => $request->satuan_id,
            'tipe_harga' => $request->tipe_harga,
            'harga' => $request->harga,
            'status' => $request->status,
            'updated_at' => now()
        ]);

        return redirect()->route('harga-barang.index')->with('success', 'Harga barang berhasil diperbarui');
    }

    public function destroy($id)
    {
        $hargaBarang = HargaBarang::findOrFail($id);
        $hargaBarang->delete();

        return redirect()->route('harga-barang.index')->with('success', 'Harga barang berhasil dihapus');
    }

    public function getHarga(Request $request)
    {
        $barangId = $request->get('barang_id');
        $satuanId = $request->get('satuan_id');
        $tipeHarga = $request->get('tipe_harga', 'ecer');

        $harga = HargaBarang::where('barang_id', $barangId)
                           ->where('satuan_id', $satuanId)
                           ->where('tipe_harga', $tipeHarga)
                           ->where('status', 'aktif')
                           ->first();

        if ($harga) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'harga' => $harga->harga
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Harga tidak ditemukan'
            ], 404);
        }
    }
}
