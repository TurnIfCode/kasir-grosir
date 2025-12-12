<?php

namespace App\Http\Controllers;

use App\Models\JenisBarang;
use App\Models\Kategori;
use App\Models\Barang;
use App\Models\Supplier;
use App\Models\Log;
use Illuminate\Http\Request;

class JenisBarangController extends Controller
{
    public function data(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search') ? $request->get('search')['value'] : '';

            $query = JenisBarang::with(['kategori', 'barang', 'supplier'])->where('status', 'aktif');

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('kode_jenis', 'like', '%' . $search . '%')
                      ->orWhere('nama_jenis', 'like', '%' . $search . '%')
                      ->orWhere('deskripsi', 'like', '%' . $search . '%')
                      ->orWhereHas('kategori', function($q) use ($search) {
                          $q->where('nama_kategori', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('barang', function($q) use ($search) {
                          $q->where('nama_barang', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('supplier', function($q) use ($search) {
                          $q->where('nama_supplier', 'like', '%' . $search . '%');
                      });
                });
            }

            $totalRecords = JenisBarang::where('status', 'aktif')->count();
            $filteredRecords = $query->count();

            $jenisBarangs = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($jenisBarangs as $jenisBarang) {
                $data[] = [
                    'id' => $jenisBarang->id,
                    'kode_jenis' => $jenisBarang->kode_jenis,
                    'nama_jenis' => $jenisBarang->nama_jenis,
                    'kategori' => $jenisBarang->kategori ? $jenisBarang->kategori->nama_kategori : '-',
                    'barang' => $jenisBarang->barang ? $jenisBarang->barang->nama_barang : '-',
                    'supplier' => $jenisBarang->supplier ? $jenisBarang->supplier->nama_supplier : '-',
                    'deskripsi' => $jenisBarang->deskripsi,
                    'status' => $jenisBarang->status,
                    'aksi' => '<a href="#" id="btnDetail" data-id="' . $jenisBarang->id . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a> <a href="#" id="btnEdit" data-id="' . $jenisBarang->id . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a> <a href="#" data-id="' . $jenisBarang->id . '" id="btnDelete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>'
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('jenis_barang.index');
    }

    public function add()
    {
        $kategoris = Kategori::where('status', 'aktif')->get();
        $barangs = Barang::where('status', 'aktif')->get();
        $suppliers = Supplier::where('status', 'aktif')->get();

        return view('jenis_barang.add', compact('kategoris', 'barangs', 'suppliers'));
    }

    public function store(Request $request)
    {
        $kode_jenis  = trim($request->input('kode_jenis'));
        $nama_jenis  = trim($request->input('nama_jenis'));
        $kategori_id = $request->input('kategori_id');
        $barang_id   = $request->input('barang_id');
        $supplier_id = $request->input('supplier_id');
        $deskripsi   = trim($request->input('deskripsi'));
        $status      = trim($request->input('status'));

        if (empty($kode_jenis)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode jenis harus diisi'
            ]);
        }

        if (strlen($kode_jenis) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Kode jenis minimal 3 karakter'
            ]);
        }

        if (empty($nama_jenis)) {
            return response()->json([
                'success' => false,
                'message' => 'Nama jenis harus diisi'
            ]);
        }

        if (strlen($nama_jenis) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Nama jenis minimal 3 karakter'
            ]);
        }

        // cek jenis barang sudah ada atau belum
        $cekJenisBarang = JenisBarang::where('kode_jenis', $kode_jenis)->first();
        if ($cekJenisBarang) {
            return response()->json([
                'success' => false,
                'message' => 'Kode jenis sudah terdaftar'
            ]);
        }

        if (empty($status)) {
            return response()->json([
                'success' => false,
                'message' => 'Status harus diisi'
            ]);
        }

        $jenisBarangModel = new JenisBarang();
        $jenisBarangModel->kode_jenis = $kode_jenis;
        $jenisBarangModel->nama_jenis = $nama_jenis;
        $jenisBarangModel->kategori_id = $kategori_id;
        $jenisBarangModel->barang_id = $barang_id;
        $jenisBarangModel->supplier_id = $supplier_id;
        $jenisBarangModel->deskripsi = $deskripsi;
        $jenisBarangModel->status = $status;
        $jenisBarangModel->created_by = auth()->check() ? auth()->user()->id : null;
        $jenisBarangModel->updated_by = auth()->check() ? auth()->user()->id : null;
        $jenisBarangModel->created_at = now();
        $jenisBarangModel->updated_at = now();
        $jenisBarangModel->save();

        $newLog = new Log();
        $newLog->keterangan = 'Menambahkan jenis barang baru: ' . $jenisBarangModel->nama_jenis . ' (Kode Jenis: ' . $jenisBarangModel->kode_jenis . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Jenis barang berhasil ditambahkan'
        ]);
    }

    public function find($id)
    {
        $jenisBarang = JenisBarang::with(['creator', 'updater', 'kategori', 'barang', 'supplier'])->find($id);
        if (!$jenisBarang) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis barang tidak ditemukan'
            ]);
        }

        $data = $jenisBarang->toArray();
        $data['created_by'] = $jenisBarang->creator ? $jenisBarang->creator->name : '-';
        $data['updated_by'] = $jenisBarang->updater ? $jenisBarang->updater->name : '-';
        $data['kategori_nama'] = $jenisBarang->kategori ? $jenisBarang->kategori->nama_kategori : '-';
        $data['barang_nama'] = $jenisBarang->barang ? $jenisBarang->barang->nama_barang : '-';
        $data['supplier_nama'] = $jenisBarang->supplier ? $jenisBarang->supplier->nama_supplier : '-';

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $jenisBarang = JenisBarang::find($id);
        if (!$jenisBarang) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis barang tidak ditemukan'
            ]);
        }

        $kodeJenis = trim($request->input('kode_jenis'));
        $namaJenis = trim($request->input('nama_jenis'));
        $kategoriId = $request->input('kategori_id');
        $barangId = $request->input('barang_id');
        $supplierId = $request->input('supplier_id');
        $deskripsi = trim($request->input('deskripsi'));
        $status = trim($request->input('status'));

        if (empty($kodeJenis)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode Jenis harus diisi'
            ]);
        }

        if (strlen($kodeJenis) < 3) {
            return response()->json([
                'status' => false,
                'message' => 'Kode Jenis minimal 3 karakter'
            ]);
        }

        if (empty($namaJenis)) {
            return response()->json([
                'success' => false,
                'message' => 'Nama Jenis harus diisi'
            ]);
        }

        if (strlen($namaJenis) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Nama Jenis minimal 3 karakter'
            ]);
        }

        // cek kode jenis sudah ada atau belum, kecuali dirinya sendiri
        $cekJenisBarang = JenisBarang::where('kode_jenis', $kodeJenis)->where('id', '!=', $id)->first();
        if ($cekJenisBarang) {
            return response()->json([
                'success' => false,
                'message' => 'Kode Jenis sudah terdaftar'
            ]);
        }

        if (empty($status)) {
            return response()->json([
                'success' => false,
                'message' => 'Status harus diisi'
            ]);
        }

        $jenisBarang->kode_jenis = $kodeJenis;
        $jenisBarang->nama_jenis = $namaJenis;
        $jenisBarang->kategori_id = $kategoriId;
        $jenisBarang->barang_id = $barangId;
        $jenisBarang->supplier_id = $supplierId;
        $jenisBarang->deskripsi = $deskripsi;
        $jenisBarang->status = $status;
        $jenisBarang->updated_by = auth()->check() ? auth()->user()->id : null;
        $jenisBarang->updated_at = now();
        $jenisBarang->save();

        $newLog = new Log();
        $newLog->keterangan = 'Memperbarui jenis barang: ' . $jenisBarang->nama_jenis . ' (Kode Jenis: ' . $jenisBarang->kode_jenis . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Jenis barang berhasil diperbarui'
        ]);
    }

    public function delete($id)
    {
        $jenisBarang = JenisBarang::find($id);
        if (!$jenisBarang) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis barang tidak ditemukan'
            ]);
        }

        $namaJenis = $jenisBarang->nama_jenis;
        $kodeJenis = $jenisBarang->kode_jenis;

        $jenisBarang->delete();

        $newLog = new Log();
        $newLog->keterangan = 'Menghapus jenis barang: ' . $namaJenis . ' (Kode Jenis: ' . $kodeJenis . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Jenis barang berhasil dihapus'
        ]);
    }

    // API untuk autocomplete kategori
    public function searchKategori(Request $request)
    {
        $q = $request->get('q');
        $kategoris = Kategori::where('nama_kategori', 'like', '%' . $q . '%')
                            ->orWhere('kode_kategori', 'like', '%' . $q . '%')
                            ->where('status', 'AKTIF')
                            ->limit(10)
                            ->get();

        return response()->json([
            'success' => true,
            'data' => $kategoris
        ]);
    }

    // API untuk autocomplete barang
    public function searchBarang(Request $request)
    {
        $q = $request->get('q');
        $barangs = Barang::where('nama_barang', 'like', '%' . $q . '%')
                        ->orWhere('kode_barang', 'like', '%' . $q . '%')
                        ->where('status', 'aktif')
                        ->limit(10)
                        ->get();

        return response()->json([
            'success' => true,
            'data' => $barangs
        ]);
    }

    // API untuk autocomplete supplier
    public function searchSupplier(Request $request)
    {
        $q = $request->get('q');
        $suppliers = Supplier::where('nama_supplier', 'like', '%' . $q . '%')
                            ->orWhere('kode_supplier', 'like', '%' . $q . '%')
                            ->where('status', 'aktif')
                            ->limit(10)
                            ->get();

        return response()->json([
            'success' => true,
            'data' => $suppliers
        ]);
    }
}
