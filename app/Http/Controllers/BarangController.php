<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangBarcode;
use App\Models\Kategori;
use App\Models\PembelianDetail;
use App\Models\PenjualanDetail;
use App\Models\Satuan;
use App\Models\Log;
use App\Models\StokOpnameDetail;
use GrahamCampbell\ResultType\Success;
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

            $draw   = $request->get('draw');
            $start  = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search')['value'] ?? '';

            // =============================
            // 🔹 Base Query + JOIN
            // =============================
            $query = Barang::with('kategori', 'satuan', 'barcodes')
                ->leftJoin('kategori', 'barang.kategori_id', '=', 'kategori.id')
                ->leftJoin('satuan', 'barang.satuan_id', '=', 'satuan.id')
                ->select('barang.*');

            // =============================
            // 🔍 Searching
            // =============================
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('barang.kode_barang', 'like', "%$search%")
                    ->orWhere('barang.nama_barang', 'like', "%$search%")
                    ->orWhere('barang.deskripsi', 'like', "%$search%")
                    ->orWhere('kategori.nama_kategori', 'like', "%$search%")
                    ->orWhere('satuan.nama_satuan', 'like', "%$search%")
                    ->orWhereHas('barcodes', function ($b) use ($search) {
                        $b->where('barcode', 'like', "%$search%");
                    });
                });
            }

            // =============================
            // 🔽 Sorting / Ordering
            // =============================
            $columns = [
                0 => 'kategori.nama_kategori',
                1 => 'satuan.nama_satuan',
                2 => 'barang.kode_barang',
                3 => 'barang.nama_barang',
                4 => 'barang.stok',
                5 => 'barang.harga_beli',
                6 => 'barang.harga_jual',
                7 => 'barang.deskripsi',
                // 8 aksi → tidak perlu
            ];

            $orderColIndex = $request->order[0]['column'] ?? 0;
            $orderDir      = $request->order[0]['dir'] ?? 'asc';

            $orderColumn = $columns[$orderColIndex] ?? 'barang.nama_barang';

            $query->orderBy($orderColumn, $orderDir);

            // =============================
            // 📌 Count Data
            // =============================
            $totalRecords = Barang::count();
            $filteredRecords = $query->count();

            // =============================
            // 📌 Pagination
            // =============================
            $barangs = $query->skip($start)->take($length)->get();

            // =============================
            // 📌 Format DataTables
            // =============================
            $data = [];
            foreach ($barangs as $barang) {
                $data[] = [
                    'kategori'     => $barang->kategori->nama_kategori ?? '-',
                    'satuan'       => $barang->satuan->nama_satuan ?? '-',
                    'kode_barang'  => $barang->kode_barang,
                    'nama_barang'  => $barang->nama_barang,
                    'stok'         => $barang->stok,
                    'harga_beli'   => 'Rp ' . number_format($barang->harga_beli, 0, ',', '.'),
                    'harga_jual'   => 'Rp ' . number_format($barang->harga_jual, 0, ',', '.'),
                    'deskripsi'    => $barang->deskripsi ?: '-',
                    'aksi'         => '
                        <a href="#" id="btnDetail" data-id="'.$barang->id.'" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                        <a href="#" id="btnEdit" data-id="'.$barang->id.'" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <a href="#" id="btnTambahBarcode" data-id="'.$barang->id.'" class="btn btn-sm btn-success"><i class="fas fa-barcode"></i></a>
                        <a href="#" id="btnStokMinimum" data-id="'.$barang->id.'" class="btn btn-sm btn-secondary"><i class="fas fa-exclamation-triangle"></i></a>
                        ' . (auth()->user()->role == 'ADMIN' ? '
                            <a href="#" id="btnDelete" data-id="'.$barang->id.'" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                        ' : '') . '
                    '
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
        $kategori_id    = trim($request->kategori_id);
        $satuan_id      = trim($request->satuan_id);
        $kode_barang    = trim($request->kode_barang);
        $nama_barang    = trim($request->nama_barang);
        $stok           = trim($request->stok);
        $harga_beli     = trim($request->harga_beli);
        $harga_jual     = trim($request->harga_jual);
        $deskripsi      = trim($request->deskripsi);
        $multi_satuan   = trim($request->multi_satuan);
        $status         = trim($request->status);

        //disni cek kategori
        $cekKategori = Kategori::where('id',$kategori_id)->where('status', 'aktif')->count();
        if ($cekKategori == 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Kategori tidak terdaftar',
                'form'      => 'kategori_id'
            ]);
        }

        //disini cek satuan
        $cekSatuan = Satuan::where('id', $satuan_id)->where('status', 'aktif')->count();
        if ($cekSatuan == 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Satuan tidak terdaftar',
                'form'      => 'satuan_id'
            ]);
        }

        // validasi semua
        if (empty($kode_barang)) {
            return response()->json([
                'success'   => false,
                'message'   => 'Kode barang harus diisi',
                'form'      => 'kode_barang'
            ]);
        }

        if (strlen($kode_barang) < 3) {
            return response()->json([
                'success'   => false,
                'message'   => 'Kode barang minimal 3 karakter',
                'form'      => 'kode_barang'
            ]);
        }

        $cekKodeBarang = Barang::where('kode_barang', $kode_barang)->count();
        if ($cekKodeBarang > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Kode barang sudah terdaftar',
                'form'      => 'kode_barang'
            ]);
        }

        if (empty($nama_barang)) {
            return response()->json([
                'success'   => false,
                'message'   => 'Nama barang harus diisi',
                'form'      => 'nama_barang'
            ]);
        }

        if (strlen($nama_barang) < 3) {
            return response()->json([
                'success'   => false,
                'message'   => 'Nama barang minimal 3 karakter',
                'form'      => 'nama_barang'
            ]);
        }

        $cekNamaBarang = Barang::where('nama_barang',$nama_barang)->count();
        if ($cekNamaBarang > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Nama barang harus diisi',
                'form'      => 'nama_barang'
            ]);
        }

        $stok = round($stok);
        $harga_beli = round($harga_beli);
        $harga_jual = round($harga_jual);

        if ($stok < 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Stok tidak boleh kurang dari 0',
                'form'      => 'stok'
            ]);
        }

        if ($harga_beli < 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Harga beli tidak boleh kurang dari 0',
                'form'      => 'harga_beli'
            ]);
        }

        if ($harga_jual < 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Harga jual tidak boleh kurang dari 0',
                'form'      => 'harga_jual'
            ]);
        }

        $barang = new Barang();
        $barang->kategori_id = $kategori_id;
        $barang->satuan_id  = $satuan_id;
        $barang->kode_barang = $kode_barang;
        $barang->nama_barang = $nama_barang;
        $barang->stok = $stok;
        $barang->harga_beli = $harga_beli;
        $barang->harga_jual = $harga_jual;
        $barang->multi_satuan = $multi_satuan;
        $barang->deskripsi = $deskripsi;
        $barang->status = $status;
        $barang->created_by = auth()->id();
        $barang->created_at = now();
        $barang->updated_by = auth()->id();
        $barang->updated_at = now();
        $barang->save();

        $newLog = new Log();
        $newLog->keterangan = 'Menambahkan barang baru: ' . $barang->nama_barang . ' (Kode Barang: ' . $barang->kode_barang . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan'
        ]);
    }


    public function find($id)
    {
        $barang = Barang::with('kategori', 'satuan', 'barcodes', 'creator', 'updater')->find($id);
        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan'
            ]);
        }

        // Format response sesuai dengan struktur yang diinginkan
        $data = [
            'id' => $barang->id,
            'kode_barang' => $barang->kode_barang,
            'nama_barang' => $barang->nama_barang,
            'kategori_id' => $barang->kategori_id,
            'satuan_id' => $barang->satuan_id,
            'stok' => $barang->stok,
            'harga_beli' => $barang->harga_beli,
            'harga_jual' => $barang->harga_jual,
            'multi_satuan' => $barang->multi_satuan,
            'deskripsi' => $barang->deskripsi,
            'status' => $barang->status,
            'jenis' => $barang->jenis,
            'created_by' => $barang->creator ? $barang->creator->name : 'ADMINISTRATOR',
            'updated_by' => $barang->updater ? $barang->updater->name : 'ADMINISTRATOR',
            'created_at' => $barang->created_at,
            'updated_at' => $barang->updated_at,
            'kategori' => $barang->kategori ? [
                'id' => $barang->kategori->id,
                'kode_kategori' => $barang->kategori->kode_kategori,
                'nama_kategori' => $barang->kategori->nama_kategori,
                'deskripsi' => $barang->kategori->deskripsi,
                'status' => $barang->kategori->status,
                'created_by' => $barang->kategori->created_by,
                'updated_by' => $barang->kategori->updated_by,
                'created_at' => $barang->kategori->created_at,
                'updated_at' => $barang->kategori->updated_at
            ] : null,
            'satuan' => $barang->satuan ? [
                'id' => $barang->satuan->id,
                'kode_satuan' => $barang->satuan->kode_satuan,
                'nama_satuan' => $barang->satuan->nama_satuan,
                'deskripsi' => $barang->satuan->deskripsi,
                'status' => $barang->satuan->status,
                'created_by' => $barang->satuan->created_by,
                'updated_by' => $barang->satuan->updated_by,
                'created_at' => $barang->satuan->created_at,
                'updated_at' => $barang->satuan->updated_at
            ] : null,
            'barcodes' => $barang->barcodes ? $barang->barcodes->map(function($barcode) {
                return [
                    'id' => $barcode->id,
                    'barcode' => $barcode->barcode
                ];
            }) : [],
            'creator' => $barang->creator ? [
                'id' => $barang->creator->id,
                'username' => $barang->creator->username,
                'name' => $barang->creator->name,
                'role' => $barang->creator->role,
                'status' => $barang->creator->status,
                'created_by' => $barang->creator->created_by,
                'created_at' => $barang->creator->created_at,
                'updated_by' => $barang->creator->updated_by,
                'updated_at' => $barang->creator->updated_at
            ] : null,
            'updater' => $barang->updater ? [
                'id' => $barang->updater->id,
                'username' => $barang->updater->username,
                'name' => $barang->updater->name,
                'role' => $barang->updater->role,
                'status' => $barang->updater->status,
                'created_by' => $barang->updater->created_by,
                'created_at' => $barang->updater->created_at,
                'updated_by' => $barang->updater->updated_by,
                'updated_at' => $barang->updater->updated_at
            ] : null
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $kategori_id    = trim($request->kategori_id);
        $satuan_id      = trim($request->satuan_id);
        $kode_barang    = trim($request->kode_barang);
        $nama_barang    = trim($request->nama_barang);
        $deskripsi      = trim($request->deskripsi);
        $multi_satuan   = trim($request->multi_satuan);
        $status         = trim($request->status);

        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan'
            ]);
        }

        //disni cek kategori
        $cekKategori = Kategori::where('id',$kategori_id)->where('status', 'aktif')->count();
        if ($cekKategori == 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Kategori tidak terdaftar',
                'form'      => 'kategori_id'
            ]);
        }

        //disini cek satuan
        $cekSatuan = Satuan::where('id', $satuan_id)->where('status', 'aktif')->count();
        if ($cekSatuan == 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Satuan tidak terdaftar',
                'form'      => 'satuan_id'
            ]);
        }

        // validasi semua
        if (empty($kode_barang)) {
            return response()->json([
                'success'   => false,
                'message'   => 'Kode barang harus diisi',
                'form'      => 'kode_barang'
            ]);
        }

        if (strlen($kode_barang) < 3) {
            return response()->json([
                'success'   => false,
                'message'   => 'Kode barang minimal 3 karakter',
                'form'      => 'kode_barang'
            ]);
        }

        $cekKodeBarang = Barang::where('kode_barang', $kode_barang)->where('id', '!=', $id)->count();
        if ($cekKodeBarang > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Kode barang sudah terdaftar',
                'form'      => 'kode_barang'
            ]);
        }

        if (empty($nama_barang)) {
            return response()->json([
                'success'   => false,
                'message'   => 'Nama barang harus diisi',
                'form'      => 'nama_barang'
            ]);
        }

        if (strlen($nama_barang) < 3) {
            return response()->json([
                'success'   => false,
                'message'   => 'Nama barang minimal 3 karakter',
                'form'      => 'nama_barang'
            ]);
        }

        $cekNamaBarang = Barang::where('nama_barang',$nama_barang)->where('id', '!=', $id)->count();
        if ($cekNamaBarang > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Nama barang harus diisi',
                'form'      => 'nama_barang'
            ]);
        }

        $barang->kategori_id = $kategori_id;
        $barang->satuan_id  = $satuan_id;
        $barang->kode_barang = $kode_barang;
        $barang->nama_barang = $nama_barang;
        $barang->multi_satuan = $multi_satuan;
        $barang->deskripsi = $deskripsi;
        $barang->status = $status;
        $barang->updated_by = auth()->id();
        $barang->updated_at = now();
        $barang->save();

        $newLog = new Log();
        $newLog->keterangan = 'Memperbarui barang: ' . $barang->nama_barang . ' (Kode Barang: ' . $barang->kode_barang . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diperbarui'
        ]);
    }

    public function delete($id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan'
            ]);
        }

        $namaBarang = $barang->nama_barang;
        $kodeBarang = $barang->kode_barang;

        //cek sudah ada di pembelian atau belum
        $cekPembelian = PembelianDetail::where('barang_id', $id)->count();
        if ($cekPembelian > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Sudah ada transaksi pembelian. Tidak dapat dihapus'
            ]);
        }

        //cek penjualan
        $cekPenjualan = PenjualanDetail::where('barang_id',$id)->count();
        if ($cekPenjualan > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Sudah ada transaksi penjualan. Tidak dapat dihapus'
            ]);
        } 

        //cek opaname
        $cekOpname = StokOpnameDetail::where('barang_id', $id)->count();
        if ($cekOpname > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Sudah ada di opname. Tidak dapat dihapus'
            ]);
        }

        $barang->delete(); // Barcodes akan terhapus otomatis karena CASCADE

        $newLog = new Log();
        $newLog->keterangan = 'Menghapus barang: ' . $namaBarang . ' (Kode Barang: ' . $kodeBarang . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dihapus'
        ]);
    }

    // API untuk autocomplete barang
    public function search(Request $request)
    {
        $term = $request->get('q', $request->get('term', ''));
        $pelangganId = $request->get('pelanggan_id');

        $query = Barang::with('barcodes', 'satuan', 'kategori')
            ->where('status', 'aktif');

        // Check if customer is Hubuan and term is 'Rokok & Tembakau'
        if ($pelangganId) {
            $pelanggan = \App\Models\Pelanggan::find($pelangganId);
            if ($pelanggan && strtolower($pelanggan->nama_pelanggan) === 'hubuan' && strtolower($term) === 'rokok & tembakau') {
                // Filter to only show Rokok & Tembakau items with 'slop' unit
                $query->whereHas('kategori', function($q) {
                    $q->where('nama_kategori', 'Rokok & Tembakau');
                })->whereHas('hargaBarang', function($q) {
                    $q->whereHas('satuan', function($sq) {
                        $sq->where('nama_satuan', 'slop');
                    })->where('status', 'aktif');
                });
            } else {
                // Normal search for other customers
                $query->where(function($q) use ($term) {
                    $q->where('nama_barang', 'LIKE', "%{$term}%")
                      ->orWhere('kode_barang', 'LIKE', "%{$term}%")
                      ->orWhereHas('barcodes', function($qb) use ($term) {
                          $qb->where('barcode', 'LIKE', "%{$term}%");
                      });
                });
            }
        } else {
            // Normal search if no pelanggan_id
            $query->where(function($q) use ($term) {
                $q->where('nama_barang', 'LIKE', "%{$term}%")
                  ->orWhere('kode_barang', 'LIKE', "%{$term}%")
                  ->orWhereHas('barcodes', function($qb) use ($term) {
                      $qb->where('barcode', 'LIKE', "%{$term}%");
                  });
            });
        }

        $barangs = $query->limit(20)->get(['id', 'nama_barang', 'kode_barang', 'satuan_id']);

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
            'success' => true,
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
            'success' => true,
            'data' => $satuans
        ]);
    }


    // API untuk mendapatkan info barang (kategori dan paket)
    public function getInfo($id)
    {
        $barang = Barang::with(['kategori', 'paketDetails.paket'])->find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan',
                'form' => 'barang-autocomplete'
            ], 404);
        }

        $isPaket = $barang->paketDetails->isNotEmpty();
        $paketInfo = null;
        if ($isPaket) {
            $paketDetail = $barang->paketDetails->first();
            $paket = $paketDetail->paket;
            if ($paket) {
                $paketInfo = [
                    'nama_paket' => $paket->nama
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $barang->id,
                'nama_barang' => $barang->nama_barang,
                'kode_barang' => $barang->kode_barang,
                'kategori' => $barang->kategori ? $barang->kategori->nama_kategori : null,
                'jenis' => $barang->jenis,
                'harga' => round($barang->harga_jual, 0),
                'satuan_id' => $barang->satuan_id,
                'is_paket' => $isPaket,
                'nama_paket' => $paketInfo ? $paketInfo['nama_paket'] : null,
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
                'success' => true,
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
        $barangId  = trim($request->barang_id);
        $barcodeStr = trim($request->barcode);

        // Ambil data barang
        $barang = Barang::find($barangId);

        // Cek barcode sudah ada atau belum
        $cekBarcode = BarangBarcode::where('barcode', $barcodeStr)->first();

        if ($cekBarcode) {
            $barangBarcode = Barang::find($cekBarcode->barang_id);

            return response()->json([
                'success' => false,
                'message' => 'Barcode sudah terdaftar untuk barang : ' . $barangBarcode->nama_barang,
                'form' => 'barcode'
            ]);
        }

        // Simpan barcode baru
        $newBarcode = new BarangBarcode();
        $newBarcode->barang_id = $barangId;
        $newBarcode->barcode = $barcodeStr;
        $newBarcode->created_by = auth()->id();
        $newBarcode->updated_by = auth()->id();
        $newBarcode->save();

        // Log (jangan pernah lempar full object!)
        $newLog = new Log();
        $newLog->keterangan = 'Tambah Barcode Barang: ' . $barang->nama_barang . ' | Barcode: ' . $barcodeStr;
        $newLog->created_by = auth()->id();
        $newLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Barcode berhasil ditambahkan',
            'data' => $newBarcode
        ]);
    }


    // Delete barcode
    public function deleteBarcode($id)
    {
        $barcode = BarangBarcode::find($id);
        if (!$barcode) {
            return response()->json([
                'success' => false,
                'message' => 'Barcode tidak ditemukan'
            ]);
        }

        $barcode->delete();

        return response()->json([
            'success' => true,
            'message' => 'Barcode berhasil dihapus'
        ]);
    }
}
