<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangBarcode;
use App\Models\Kategori;
use App\Models\Satuan;
use App\Models\Log;
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

            $query = Barang::with('kategori', 'satuan', 'barcodes')
                ->leftJoin('kategori', 'barang.kategori_id', '=', 'kategori.id')
                ->orderBy('kategori.nama_kategori', 'asc')
                ->select('barang.*');

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
                $data[] = [
                    'kategori' => $barang->kategori ? $barang->kategori->nama_kategori : '-',
                    'satuan' => $barang->satuan ? $barang->satuan->nama_satuan : '-',
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang,
                    'stok' => $barang->stok,
                    'harga_beli' => 'Rp ' . number_format($barang->harga_beli, 0, ',', '.'),
                    'harga_jual' => 'Rp ' . number_format($barang->harga_jual, 0, ',', '.'),
                    'deskripsi' => $barang->deskripsi ?: '-',
                    'aksi' => '<a href="#" id="btnDetail" data-id="' . $barang->id . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a> <a href="#" id="btnEdit" data-id="' . $barang->id . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a> <a href="#" id="btnTambahBarcode" data-id="' . $barang->id . '" class="btn btn-sm btn-success"><i class="fas fa-barcode"></i></a> <a href="#" id="btnStokMinimum" data-id="' . $barang->id . '" class="btn btn-sm btn-secondary"><i class="fas fa-exclamation-triangle"></i></a> <a href="#" data-id="' . $barang->id . '" id="btnDelete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>'
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
        $kategori = Kategori::where('status', 'AKTIF')->orderBy('nama_kategori', 'asc')->get();
        $categories = $kategori;
        $satuan = Satuan::where('status', 'AKTIF')->orderBy('nama_satuan', 'asc')->get();
        $satuans = $satuan;
        return view('barang.add', compact('kategori', 'categories', 'satuan', 'satuans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_barang' => 'required|string|max:50|unique:barang,kode_barang',
            'nama_barang' => 'required|string|max:150',
            'kategori_id' => 'required|integer',
            'satuan_id' => 'required|integer',
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
            'created_at' => now(),
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

        $newLog = new Log();
        $newLog->keterangan = 'Menambahkan barang baru: ' . $barang->nama_barang . ' (Kode Barang: ' . $barang->kode_barang . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

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
        $data['stok'] = round($barang->stok, 2);
        $data['harga_beli'] = round($barang->harga_beli, 2);
        $data['harga_jual'] = round($barang->harga_jual, 2);

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
            'satuan_id' => 'required|integer',
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
            'updated_at' => now(),
        ]);

        $newLog = new Log();
        $newLog->keterangan = 'Memperbarui barang: ' . $barang->nama_barang . ' (Kode Barang: ' . $barang->kode_barang . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

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

        $namaBarang = $barang->nama_barang;
        $kodeBarang = $barang->kode_barang;

        $barang->delete(); // Barcodes akan terhapus otomatis karena CASCADE

        $newLog = new Log();
        $newLog->keterangan = 'Menghapus barang: ' . $namaBarang . ' (Kode Barang: ' . $kodeBarang . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'status' => true,
            'message' => 'Barang berhasil dihapus'
        ]);
    }

    // API untuk autocomplete barang
    public function search(Request $request)
    {
        $term = $request->get('q', $request->get('term', ''));
        $barangs = Barang::with('barcodes', 'satuan')
            ->where(function($q) use ($term) {
                $q->where('nama_barang', 'LIKE', "%{$term}%")
                  ->orWhere('kode_barang', 'LIKE', "%{$term}%")
                  ->orWhereHas('barcodes', function($qb) use ($term) {
                      $qb->where('barcode', 'LIKE', "%{$term}%");
                  });
            })
            ->where('status', 'aktif')
            ->limit(20)
            ->get(['id', 'nama_barang', 'kode_barang', 'satuan_id']);

        // Format data untuk autocomplete
        $results = $barangs->map(function($barang) {
            $barcode = $barang->barcodes->first() ? $barang->barcodes->first()->barcode : null;
            return [
                'id' => $barang->id,
                'text' => $barang->nama_barang . ' (' . $barang->kode_barang . ')',
                'nama_barang' => $barang->nama_barang,
                'kode_barang' => $barang->kode_barang,
                'barcode' => $barcode,
                'satuan_id' => $barang->satuan_id,
                'nama_satuan' => $barang->satuan ? $barang->satuan->nama_satuan : null
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $results
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
            'harga_beli' => round($barang->harga_beli,2) ?? 0
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
                    'harga_beli' => round($konversi->harga_beli,2) ?? 0
                ];
            })
            ->toArray();

        $satuans = array_merge([$satuanDasar], $konversiSatuans);

        return response()->json([
            'status' => 'success',
            'data' => $satuans
        ]);
    }

    // API untuk mendapatkan info barang (kategori dan paket)
    public function getInfo($id)
    {
        $barang = Barang::with(['kategori', 'paketDetails.paket'])->find($id);

        if (!$barang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        $isPaket = $barang->paketDetails->isNotEmpty();
        $paketInfo = null;
        if ($isPaket) {
            $paketDetail = $barang->paketDetails->first();
            $paket = $paketDetail->paket;
            if ($paket) {
                $paketInfo = [
                    'nama_paket' => $paket->nama_paket,
                    'harga_per_3' => $paket->harga_per_3,
                    'harga_per_unit' => $paket->harga_per_unit
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $barang->id,
                'nama_barang' => $barang->nama_barang,
                'kategori' => $barang->kategori ? $barang->kategori->nama_kategori : null,
                'harga' => round($barang->harga_jual, 0),
                'is_paket' => $isPaket,
                'nama_paket' => $paketInfo ? $paketInfo['nama_paket'] : null,
                'harga_per_3' => $paketInfo ? $paketInfo['harga_per_3'] : null,
                'paket' => $paketInfo ? [$paketInfo] : []
            ]
        ]);
    }

    // API untuk mendapatkan harga berdasarkan barang, satuan, dan tipe harga
    public function getHarga(Request $request, $barangId, $satuanId)
    {
        $tipe = $request->get('tipe', 'ecer');

        try {
            $hargaService = app(\App\Services\HargaService::class);
            $hargaData = $hargaService->lookupHarga($barangId, $satuanId, $tipe);
            return response()->json([
                'status' => 'success',
                'data' => [
                    'harga' => $hargaData['harga']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Store barcode
    public function storeBarcode(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|integer|exists:barang,id',
            'barcode' => 'required|string|max:100|unique:barang_barcodes,barcode',
        ]);

        $barcode = BarangBarcode::create([
            'barang_id' => $request->barang_id,
            'barcode' => $request->barcode,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Barcode berhasil ditambahkan',
            'data' => $barcode
        ]);
    }

    // Delete barcode
    public function deleteBarcode($id)
    {
        $barcode = BarangBarcode::find($id);
        if (!$barcode) {
            return response()->json([
                'status' => false,
                'message' => 'Barcode tidak ditemukan'
            ]);
        }

        $barcode->delete();

        return response()->json([
            'status' => true,
            'message' => 'Barcode berhasil dihapus'
        ]);
    }
}
