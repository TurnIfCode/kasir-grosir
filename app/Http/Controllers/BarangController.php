<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangBarcode;
use App\Models\Kategori;
use App\Models\Satuan;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index()
    {
        $kategori = Kategori::where('status', 'AKTIF')->get();
        $categories = $kategori;
        return view('barang.index', compact('kategori', 'categories'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search') ? $request->get('search')['value'] : '';

            $query = Barang::with('kategori', 'satuan', 'barcodes');

            if (!empty($search)) {
                $query->where('kode_barang', 'like', '%' . $search . '%')
                      ->orWhere('nama_barang', 'like', '%' . $search . '%')
                      ->orWhereHas('satuan', function($q) use ($search) {
                          $q->where('nama_satuan', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('barcodes', function($q) use ($search) {
                          $q->where('barcode', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('kategori', function($q) use ($search) {
                          $q->where('nama_kategori', 'like', '%' . $search . '%');
                      });
            }

            $totalRecords = Barang::count();
            $filteredRecords = $query->count();

            $barangs = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($barangs as $barang) {
                $barcodes = $barang->barcodes->pluck('barcode')->join(', ');
                $data[] = [
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang,
                    'kategori' => $barang->kategori ? $barang->kategori->nama_kategori : '-',
                    'satuan' => $barang->satuan ? $barang->satuan->nama_satuan : '-',
                    'stok' => $barang->stok,
                    'harga_beli' => 'Rp ' . number_format($barang->harga_beli, 0, ',', '.'),
                    'harga_jual' => 'Rp ' . number_format($barang->harga_jual, 0, ',', '.'),
                    'barcodes' => $barcodes ?: '-',
                    'aksi' => '<a href="#" id="btnDetail" data-id="' . $barang->id . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a> <a href="#" id="btnEdit" data-id="' . $barang->id . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a> <a href="#" data-id="' . $barang->id . '" id="btnDelete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>'
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('barang.index');
    }

    public function add()
    {
        $kategori = Kategori::where('status', 'AKTIF')->get();
        $categories = $kategori;
        $satuan = Satuan::where('status', 'AKTIF')->get();
        $satuans = $satuan;
        return view('barang.add', compact('kategori', 'categories', 'satuan', 'satuans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_barang' => 'required|string|max:50|unique:barang,kode_barang',
            'nama_barang' => 'required|string|max:150',
            'kategori_id' => 'required|integer',
            'satuan_id' => 'nullable|integer',
            'stok' => 'required|integer|min:0',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'multi_satuan' => 'nullable|boolean',
            'deskripsi' => 'nullable|string',
            'status' => 'nullable|in:aktif,nonaktif',
            'barcodes.*' => 'nullable|string|max:100|distinct',
        ]);

        $barang = Barang::create([
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'kategori_id' => $request->kategori_id,
            'satuan_id' => $request->satuan_id,
            'stok' => $request->stok ?: 0,
            'harga_beli' => $request->harga_beli ?: 0,
            'harga_jual' => $request->harga_jual ?: 0,
            'multi_satuan' => $request->multi_satuan ?? 0,
            'deskripsi' => $request->deskripsi,
            'status' => $request->status ?? 'aktif',
            'created_by' => auth()->id(),
        ]);

        if ($request->has('barcodes')) {
            foreach ($request->barcodes as $barcode) {
                if (!empty($barcode)) {
                    BarangBarcode::create([
                        'barang_id' => $barang->id,
                        'barcode' => $barcode,
                        'created_by' => auth()->id(),
                    ]);
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Barang berhasil ditambahkan'
        ]);
    }

    public function find($id)
    {
        $barang = Barang::with('kategori', 'satuan', 'barcodes', 'creator', 'updater')->find($id);
        if (!$barang) {
            return response()->json([
                'status' => false,
                'message' => 'Barang tidak ditemukan'
            ]);
        }

        $data = $barang->toArray();
        $data['created_by'] = $barang->creator ? $barang->creator->name : '-';
        $data['updated_by'] = $barang->updater ? $barang->updater->name : '-';

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json([
                'status' => false,
                'message' => 'Barang tidak ditemukan'
            ]);
        }

        $request->validate([
            'kode_barang' => 'nullable|string|max:50|unique:barang,kode_barang,' . $id,
            'nama_barang' => 'required|string|max:150',
            'kategori_id' => 'nullable|integer',
            'satuan_id' => 'nullable|integer',
            'stok' => 'nullable|integer|min:0',
            'harga_beli' => 'nullable|numeric|min:0',
            'harga_jual' => 'nullable|numeric|min:0',
            'multi_satuan' => 'nullable|boolean',
            'deskripsi' => 'nullable|string',
            'status' => 'nullable|in:aktif,nonaktif',
            'barcodes.*' => 'nullable|string|max:100|distinct',
        ]);

        $barang->update([
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'kategori_id' => $request->kategori_id,
            'satuan_id' => $request->satuan_id,
            'stok' => $request->stok ?: 0,
            'harga_beli' => $request->harga_beli ?: 0,
            'harga_jual' => $request->harga_jual ?: 0,
            'multi_satuan' => $request->multi_satuan ?? 0,
            'deskripsi' => $request->deskripsi,
            'status' => $request->status ?? 'aktif',
            'updated_by' => auth()->id(),
        ]);

        // Update barcode (hapus yang lama lalu tambah baru)
        $barang->barcodes()->delete();
        if ($request->has('barcodes')) {
            foreach ($request->barcodes as $barcode) {
                if (!empty($barcode)) {
                    BarangBarcode::create([
                        'barang_id' => $barang->id,
                        'barcode' => $barcode,
                        'created_by' => auth()->id(),
                    ]);
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Barang berhasil diperbarui'
        ]);
    }

    public function delete($id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json([
                'status' => false,
                'message' => 'Barang tidak ditemukan'
            ]);
        }

        $barang->delete(); // Barcodes akan terhapus otomatis karena CASCADE

        return response()->json([
            'status' => true,
            'message' => 'Barang berhasil dihapus'
        ]);
    }

    // API untuk autocomplete barang
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $barangs = Barang::with('barcodes')
            ->where(function($q) use ($query) {
                $q->where('nama_barang', 'LIKE', "%{$query}%")
                  ->orWhere('kode_barang', 'LIKE', "%{$query}%")
                  ->orWhereHas('barcodes', function($qb) use ($query) {
                      $qb->where('barcode', 'LIKE', "%{$query}%");
                  });
            })
            ->where('status', 'aktif')
            ->limit(10)
            ->get(['id', 'kode_barang', 'nama_barang']);

        // Add barcode to each item
        $barangs->transform(function($barang) {
            $barang->barcode = $barang->barcodes->first()?->barcode;
            unset($barang->barcodes);
            return $barang;
        });

        return response()->json([
            'status' => 'success',
            'data' => $barangs
        ]);
    }

    // API untuk mendapatkan satuan berdasarkan barang
    public function getSatuan($id)
    {
        $barang = Barang::findOrFail($id);

        // Get satuan dasar
        $satuanDasar = [
            'satuan_id' => $barang->satuan_id,
            'nama_satuan' => $barang->satuan->nama_satuan ?? 'Satuan Dasar',
            'nilai_konversi' => 1,
            'harga_beli' => $barang->harga_beli ?? 0
        ];

        // Get konversi satuan
        $konversiSatuans = \App\Models\KonversiSatuan::where('barang_id', $id)
            ->where('status', 'aktif')
            ->with('satuanKonversi')
            ->get()
            ->map(function ($konversi) {
                return [
                    'satuan_id' => $konversi->satuan_konversi_id,
                    'nama_satuan' => $konversi->satuanKonversi->nama_satuan,
                    'nilai_konversi' => $konversi->nilai_konversi,
                    'harga_beli' => $konversi->harga_beli ?? 0
                ];
            })
            ->toArray();

        $satuans = array_merge([$satuanDasar], $konversiSatuans);

        return response()->json([
            'status' => 'success',
            'data' => $satuans
        ]);
    }
}
