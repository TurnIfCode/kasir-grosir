<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Satuan;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index()
    {
        $kategori = Kategori::where('status', 'AKTIF')->get();
        $categories = $kategori;
        $satuans = Satuan::where('status', 'AKTIF')->get();
        return view('barang.index', compact('kategori', 'categories', 'satuans'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search') ? $request->get('search')['value'] : '';

            $query = Barang::with('kategori', 'satuan');

            if (!empty($search)) {
                $query->where('kode_barang', 'like', '%' . $search . '%')
                      ->orWhere('nama_barang', 'like', '%' . $search . '%')
                      ->orWhere('satuan_dasar', 'like', '%' . $search . '%')
                      ->orWhere('barcode', 'like', '%' . $search . '%')
                      ->orWhere('status', 'like', '%' . $search . '%')
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
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang,
                    'kategori' => $barang->kategori ? $barang->kategori->nama_kategori : '-',
                    'satuan_dasar' => $barang->satuan ? $barang->satuan->nama_satuan : '-',
                    'stok' => round($barang->stok),
                    'harga_beli' => 'Rp ' . number_format($barang->harga_beli, 0, ',', '.'),
                    'harga_jual' => 'Rp ' . number_format($barang->harga_jual, 0, ',', '.'),
                    'multi_satuan' => $barang->multi_satuan,
                    'aksi' => '<a href="#" id="btnEdit" data-id="' . $barang->id . '" class="btn btn-sm btn-warning">Edit</a> <a href="#" data-id="' . $barang->id . '" id="btnDelete" class="btn btn-sm btn-danger">Hapus</a>'
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
        $satuans = Satuan::where('status', 'AKTIF')->get();
        return view('barang.add', compact('kategori', 'categories', 'satuans'));
    }

    public function store(Request $request)
    {
        $kodeBarang = trim($request->input('kode_barang'));
        $namaBarang = trim($request->input('nama_barang'));
        $kategoriId = $request->input('kategori_id');
        $satuanId = $request->input('satuan_id');
        $stok = $request->input('stok', 0);
        $hargaBeli = $request->input('harga_beli', 0);
        $hargaJual = $request->input('harga_jual', 0);
        $multiSatuan = $request->input('multi_satuan', 0);
        $deskripsi = trim($request->input('deskripsi'));
        $status = trim($request->input('status'));

        if (empty($kodeBarang)) {
            return response()->json([
                'status' => false,
                'message' => 'Kode Barang harus diisi'
            ]);
        }

        if (strlen($kodeBarang) < 3) {
            return response()->json([
                'status' => false,
                'message' => 'Kode Barang minimal 3 karakter'
            ]);
        }

        if (empty($namaBarang)) {
            return response()->json([
                'status' => false,
                'message' => 'Nama Barang harus diisi'
            ]);
        }

        if (strlen($namaBarang) < 3) {
            return response()->json([
                'status' => false,
                'message' => 'Nama Barang minimal 3 karakter'
            ]);
        }

        if (!is_numeric($hargaBeli) || $hargaBeli < 0) {
            return response()->json([
                'status' => false,
                'message' => 'Harga Beli harus berupa angka positif'
            ]);
        }

        if (!is_numeric($hargaJual) || $hargaJual < 0) {
            return response()->json([
                'status' => false,
                'message' => 'Harga Jual harus berupa angka positif'
            ]);
        }

        // cek kode barang sudah ada atau belum
        $cekBarang = Barang::where('kode_barang', $kodeBarang)->first();
        if ($cekBarang) {
            return response()->json([
                'status' => false,
                'message' => 'Kode Barang sudah terdaftar'
            ]);
        }

        if (empty($status)) {
            return response()->json([
                'status' => false,
                'message' => 'Status harus diisi'
            ]);
        }

        $barangModel = new Barang();
        $barangModel->kode_barang = $kodeBarang;
        $barangModel->nama_barang = $namaBarang;
        $barangModel->kategori_id = $kategoriId ?: null;
        $barangModel->satuan_id = $satuanId ?: null;
        $barangModel->stok = $stok;
        $barangModel->harga_beli = $hargaBeli;
        $barangModel->harga_jual = $hargaJual;
        $barangModel->multi_satuan = $multiSatuan;
        $barangModel->deskripsi = $deskripsi ?: null;
        $barangModel->status = $status;
        $barangModel->created_by = auth()->check() ? auth()->user()->id : null;
        $barangModel->updated_by = auth()->check() ? auth()->user()->id : null;
        $barangModel->save();

        return response()->json([
            'status' => true,
            'message' => 'Barang berhasil ditambahkan'
        ]);
    }

    public function find($id)
    {
        $barang = Barang::with('kategori', 'satuan')->find($id);
        if (!$barang) {
            return response()->json([
                'status' => false,
                'message' => 'Barang tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $barang
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

        $kodeBarang = trim($request->input('kode_barang'));
        $namaBarang = trim($request->input('nama_barang'));
        $kategoriId = $request->input('kategori_id');
        $satuanId = $request->input('satuan_id');
        $stok = $request->input('stok', 0);
        $hargaBeli = $request->input('harga_beli', 0);
        $hargaJual = $request->input('harga_jual', 0);
        $multiSatuan = $request->input('multi_satuan', 0);
        $deskripsi = trim($request->input('deskripsi'));
        $status = trim($request->input('status'));

        if (empty($kodeBarang)) {
            return response()->json([
                'status' => false,
                'message' => 'Kode Barang harus diisi'
            ]);
        }

        if (strlen($kodeBarang) < 3) {
            return response()->json([
                'status' => false,
                'message' => 'Kode Barang minimal 3 karakter'
            ]);
        }

        if (empty($namaBarang)) {
            return response()->json([
                'status' => false,
                'message' => 'Nama Barang harus diisi'
            ]);
        }

        if (strlen($namaBarang) < 3) {
            return response()->json([
                'status' => false,
                'message' => 'Nama Barang minimal 3 karakter'
            ]);
        }

        if (!is_numeric($hargaBeli) || $hargaBeli < 0) {
            return response()->json([
                'status' => false,
                'message' => 'Harga Beli harus berupa angka positif'
            ]);
        }

        if (!is_numeric($hargaJual) || $hargaJual < 0) {
            return response()->json([
                'status' => false,
                'message' => 'Harga Jual harus berupa angka positif'
            ]);
        }

        // cek kode barang sudah ada atau belum, kecuali dirinya sendiri
        $cekBarang = Barang::where('kode_barang', $kodeBarang)->where('id', '!=', $id)->first();
        if ($cekBarang) {
            return response()->json([
                'status' => false,
                'message' => 'Kode Barang sudah terdaftar'
            ]);
        }

        if (empty($status)) {
            return response()->json([
                'status' => false,
                'message' => 'Status harus diisi'
            ]);
        }

        $barang->kode_barang = $kodeBarang;
        $barang->nama_barang = $namaBarang;
        $barang->kategori_id = $kategoriId ?: null;
        $barang->satuan_id = $satuanId ?: null;
        $barang->stok = $stok;
        $barang->harga_beli = $hargaBeli;
        $barang->harga_jual = $hargaJual;
        $barang->multi_satuan = $multiSatuan;
        $barang->deskripsi = $deskripsi ?: null;
        $barang->status = $status;
        $barang->updated_by = auth()->check() ? auth()->user()->id : null;
        $barang->save();

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

        $barang->delete();

        return response()->json([
            'status' => true,
            'message' => 'Barang berhasil dihapus'
        ]);
    }

    // API untuk autocomplete barang
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $barangs = Barang::where('nama_barang', 'LIKE', "%{$query}%")
            ->orWhere('kode_barang', 'LIKE', "%{$query}%")
            ->where('status', 'aktif')
            ->limit(10)
            ->get(['id', 'kode_barang', 'nama_barang']);

        return response()->json([
            'status' => 'success',
            'data' => $barangs
        ]);
    }

    // API untuk mendapatkan satuan dan harga berdasarkan barang
    public function getSatuan($id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang tidak ditemukan'
            ]);
        }

        // Ambil semua konversi satuan untuk barang ini
        $konversiSatuan = \App\Models\KonversiSatuan::where('barang_id', $id)
            ->where('status', 'aktif')
            ->with(['satuanKonversi'])
            ->get();

        $data = [];

        // Jika ada konversi satuan, ambil satuan konversi dengan harga beli dari konversi
        if ($konversiSatuan->isNotEmpty()) {
            foreach ($konversiSatuan as $konversi) {
                $data[] = [
                    'satuan_id' => $konversi->satuan_konversi_id,
                    'nama_satuan' => $konversi->satuanKonversi->nama_satuan,
                    'nilai_konversi' => $konversi->nilai_konversi,
                    'harga_beli' => round($konversi->harga_beli)
                ];
            }
        }

        // Selalu sertakan satuan dasar dengan harga beli dari barang
        $satuanDasar = $barang->satuan;
        if ($satuanDasar) {
            // Cek apakah satuan dasar sudah ada di data konversi
            $satuanDasarExists = collect($data)->contains('satuan_id', $satuanDasar->id);

            if (!$satuanDasarExists) {
                $data[] = [
                    'satuan_id' => $satuanDasar->id,
                    'nama_satuan' => $satuanDasar->nama_satuan,
                    'nilai_konversi' => 1,
                    'harga_beli' => round($barang->harga_beli)
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
