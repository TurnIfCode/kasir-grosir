<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Satuan;
use App\Models\StokMinimum;
use Illuminate\Http\Request;

class StokMinimumController extends Controller
{
    public function index()
    {
        return view('stok-minimum.index');
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search') ? $request->get('search')['value'] : '';

            $query = StokMinimum::with('barang', 'satuan')
                ->join('barang', 'stok_minimum.barang_id', '=', 'barang.id')
                ->orderBy('barang.nama_barang', 'asc')
                ->select('stok_minimum.*');

            if (!empty($search)) {
                $query->whereHas('barang', function($q) use ($search) {
                    $q->where('nama_barang', 'like', '%' . $search . '%')
                      ->orWhere('kode_barang', 'like', '%' . $search . '%');
                })
                ->orWhereHas('satuan', function($q) use ($search) {
                    $q->where('nama_satuan', 'like', '%' . $search . '%');
                });
            }

            $totalRecords = StokMinimum::count();
            $filteredRecords = $query->count();

            $stokMinimums = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($stokMinimums as $stokMinimum) {
                $data[] = [
                    'nama_barang' => $stokMinimum->barang ? $stokMinimum->barang->nama_barang : '-',
                    'kode_barang' => $stokMinimum->barang ? $stokMinimum->barang->kode_barang : '-',
                    'jumlah_minimum' => $stokMinimum->jumlah_minimum,
                    'satuan' => $stokMinimum->satuan ? $stokMinimum->satuan->nama_satuan : '-',
                    'jumlah_satuan_terkecil' => $stokMinimum->jumlah_satuan_terkecil,
                    'satuan_terkecil' => $stokMinimum->satuanTerkecil ? $stokMinimum->satuanTerkecil->nama_satuan : '-',
                    'aksi' => '<a href="#" data-id="' . $stokMinimum->id . '" id="btnDelete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>'
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('stok-minimum.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|integer|exists:barang,id',
            'jumlah_minimum' => 'required|integer|min:0',
            'satuan_id' => 'required|integer|exists:satuan,id',
            'jumlah_satuan_terkecil' => 'required|integer|min:0',
            'satuan_terkecil_id' => 'required|integer|exists:satuan,id',
        ]);

        // Check if stok minimum already exists for this barang
        $existing = StokMinimum::where('barang_id', $request->barang_id)->first();
        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'Stok minimum untuk barang ini sudah ada'
            ]);
        }

        StokMinimum::create([
            'barang_id' => $request->barang_id,
            'jumlah_minimum' => $request->jumlah_minimum,
            'satuan_id' => $request->satuan_id,
            'jumlah_satuan_terkecil' => $request->jumlah_satuan_terkecil,
            'satuan_terkecil_id' => $request->satuan_terkecil_id,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Stok minimum berhasil ditambahkan'
        ]);
    }

    public function delete($id)
    {
        $stokMinimum = StokMinimum::find($id);
        if (!$stokMinimum) {
            return response()->json([
                'status' => false,
                'message' => 'Stok minimum tidak ditemukan'
            ]);
        }

        $stokMinimum->delete();

        return response()->json([
            'status' => true,
            'message' => 'Stok minimum berhasil dihapus'
        ]);
    }

    // API untuk mendapatkan stok minimum berdasarkan barang
    public function getByBarang($barangId)
    {
        $stokMinimums = StokMinimum::with('satuan', 'satuanTerkecil')
            ->where('barang_id', $barangId)
            ->get();

        $data = $stokMinimums->map(function($stokMinimum) {
            return [
                'id' => $stokMinimum->id,
                'jumlah_minimum' => $stokMinimum->jumlah_minimum,
                'satuan' => $stokMinimum->satuan ? $stokMinimum->satuan->nama_satuan : '-',
                'jumlah_satuan_terkecil' => $stokMinimum->jumlah_satuan_terkecil,
                'satuan_terkecil' => $stokMinimum->satuanTerkecil ? $stokMinimum->satuanTerkecil->nama_satuan : '-',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
