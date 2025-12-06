<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Log;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function data(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search') ? $request->get('search')['value'] : '';

            $query = Kategori::where('status', 'AKTIF')->orderBy('nama_kategori', 'asc');

            if (!empty($search)) {
                $query->where('kode_kategori', 'like', '%' . $search . '%')
                      ->orWhere('nama_kategori', 'like', '%' . $search . '%')
                      ->orWhere('deskripsi', 'like', '%' . $search . '%');
            }

            $totalRecords = Kategori::where('status', 'AKTIF')->count();
            $filteredRecords = $query->count();

            $kategoris = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($kategoris as $kategori) {
                $deleteBtn = auth()->user()->role == 'ADMIN' ? ' <a href="#" data-id="' . $kategori->id . '" id="btnDelete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>' : '';
                $data[] = [
                    'id' => $kategori->id,
                    'kode_kategori' => $kategori->kode_kategori,
                    'nama_kategori' => $kategori->nama_kategori,
                    'deskripsi' => $kategori->deskripsi,
                    'status' => $kategori->status,
                    'aksi' => '<a href="#" id="btnDetail" data-id="' . $kategori->id . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a> <a href="#" id="btnEdit" data-id="' . $kategori->id . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>' . $deleteBtn
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('kategori.index');
    }

    public function add()
    {
        
        return view('kategori.add');
    }

    public function store(Request $request)
    {
        $kode_kategori  = trim($request->input('kode_kategori'));
        $nama_kategori  = trim($request->input('nama_kategori'));
        $deskripsi      = trim($request->input('deskripsi'));
        $status         = trim($request->input('status'));

        if (empty($kode_kategori)) {
            return response()->json([
                'status' => false,
                'message' => 'Kode kategori harus diisi'
            ]);
        }

        if (strlen($kode_kategori) < 3) {
            return response()->json([
                'status' => false,
                'message' => 'Kode kategori minimal 3 karakter'
            ]);
        }

        if (empty($nama_kategori)) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori harus diisi'
            ]);
        }

        if (strlen($nama_kategori) < 3) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori minimal 3 karakter'
            ]);
        }

        // cek kategori sudah ada atau belum
        $cekKategori = Kategori::where('kode_kategori', $kode_kategori)->first();
        if ($cekKategori) {
            return response()->json([
                'status' => false,
                'message' => 'Kode kategori sudah terdaftar'
            ]);
        }

        if (empty($status)) {
            return response()->json([
                'status' => false,
                'message' => 'Status harus diisi'
            ]);
        }

        $kategoriModel = new Kategori();
        $kategoriModel->kode_kategori = $kode_kategori;
        $kategoriModel->nama_kategori = $nama_kategori;
        $kategoriModel->deskripsi = $deskripsi;
        $kategoriModel->status = $status;
        $kategoriModel->created_by = auth()->check() ? auth()->user()->id : null;
        $kategoriModel->updated_by = auth()->check() ? auth()->user()->id : null;
        $kategoriModel->created_at = now();
        $kategoriModel->updated_at = now();
        $kategoriModel->save();

        $newLog = new Log();
        $newLog->keterangan = 'Menambahkan kategori baru: ' . $kategoriModel->nama_kategori . ' (Kode Kategori: ' . $kategoriModel->kode_kategori . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil ditambahkan'
        ]);
    }

    public function find($id)
    {
        $kategori = Kategori::with(['creator', 'updater'])->find($id);
        if (!$kategori) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan'
            ]);
        }

        $data = $kategori->toArray();
        $data['created_by'] = $kategori->creator ? $kategori->creator->name : '-';
        $data['updated_by'] = $kategori->updater ? $kategori->updater->name : '-';

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $kategori = Kategori::find($id);
        if (!$kategori) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan'
            ]);
        }

        $kodeKategori = trim($request->input('kode_kategori'));
        $namaKategori = trim($request->input('nama_kategori'));
        $deskripsi = trim($request->input('deskripsi'));
        $status = trim($request->input('status'));

        if (empty($kodeKategori)) {
            return response()->json([
                'status' => false,
                'message' => 'Kode Kategori harus diisi'
            ]);
        }

        if (strlen($kodeKategori) < 3) {
            return response()->json([
                'status' => false,
                'message' => 'Kode Kategori minimal 3 karakter'
            ]);
        }

        if (empty($namaKategori)) {
            return response()->json([
                'status' => false,
                'message' => 'Nama Kategori harus diisi'
            ]);
        }

        if (strlen($namaKategori) < 3) {
            return response()->json([
                'status' => false,
                'message' => 'Nama Kategori minimal 3 karakter'
            ]);
        }

        // cek kode kategori sudah ada atau belum, kecuali dirinya sendiri
        $cekKategori = Kategori::where('kode_kategori', $kodeKategori)->where('id', '!=', $id)->first();
        if ($cekKategori) {
            return response()->json([
                'status' => false,
                'message' => 'Kode Kategori sudah terdaftar'
            ]);
        }

        if (empty($status)) {
            return response()->json([
                'status' => false,
                'message' => 'Status harus diisi'
            ]);
        }

        $kategori->kode_kategori = $kodeKategori;
        $kategori->nama_kategori = $namaKategori;
        $kategori->deskripsi = $deskripsi;
        $kategori->status = $status;
        $kategori->updated_by = auth()->check() ? auth()->user()->id : null;
        $kategori->updated_at = now();
        $kategori->save();

        $newLog = new Log();
        $newLog->keterangan = 'Memperbarui kategori: ' . $kategori->nama_kategori . ' (Kode Kategori: ' . $kategori->kode_kategori . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil diperbarui'
        ]);
    }

    public function delete($id)
    {
        $kategori = Kategori::find($id);
        if (!$kategori) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan'
            ]);
        }

        $namaKategori = $kategori->nama_kategori;
        $kodeKategori = $kategori->kode_kategori;

        $kategori->delete();

        $newLog = new Log();
        $newLog->keterangan = 'Menghapus kategori: ' . $namaKategori . ' (Kode Kategori: ' . $kodeKategori . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil dihapus'
        ]);
    }
}
