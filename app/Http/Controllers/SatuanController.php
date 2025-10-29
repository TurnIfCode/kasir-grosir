<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Http\Request;

class SatuanController extends Controller
{
    public function add()
    {
        return view('satuan.add');
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search') ? $request->get('search')['value'] : '';

            $query = Satuan::where('status', 'AKTIF');

            if (!empty($search)) {
                $query->where('kode_satuan', 'like', '%' . $search . '%')
                      ->orWhere('nama_satuan', 'like', '%' . $search . '%')
                      ->orWhere('deskripsi', 'like', '%' . $search . '%');
            }

            $totalRecords = Satuan::where('status', 'AKTIF')->count();
            $filteredRecords = $query->count();

            $satuans = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($satuans as $satuan) {
                $data[] = [
                    'id' => $satuan->id,
                    'kode_satuan' => $satuan->kode_satuan,
                    'nama_satuan' => $satuan->nama_satuan,
                    'deskripsi' => $satuan->deskripsi,
                    'status' => $satuan->status,
                    'aksi' => '<a id="btnDetail" data-id="' . $satuan->id . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a> <a id="btnEdit" data-id="' . $satuan->id . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a> <a data-id="' . $satuan->id . '" id="btnDelete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>'
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('satuan.index');
    }

    public function store(Request $request)
    {
        $kode_satuan = trim($request->input('kode_satuan'));
        $nama_satuan = trim($request->input('nama_satuan'));
        $deskripsi = trim($request->input('deskripsi'));
        $status = trim($request->input('status'));

        if (empty($kode_satuan)) {
            return response()->json([
                'status' => false,
                'message' => 'Kode Satuan harus diisi'
            ]);
        }

        if (strlen($kode_satuan) < 3) {
            return response()->json([
                'status' => false,
                'message' => 'Kode Satuan minimal 3 karakter'
            ]);
        }

        // cek kode_satuan sudah ada atau belum
        $cekSatuan = Satuan::where('kode_satuan', $kode_satuan)->first();
        if ($cekSatuan) {
            return response()->json([
                'status' => false,
                'message' => 'Kode Satuan sudah terdaftar'
            ]);
        }

        if (empty($nama_satuan)) {
            return response()->json([
                'status' => false,
                'message' => 'Nama Satuan harus diisi'
            ]);
        }

        if (empty($status)) {
            return response()->json([
                'status' => false,
                'message' => 'Status harus diisi'
            ]);
        }

        $satuan = new Satuan();
        $satuan->kode_satuan = $kode_satuan;
        $satuan->nama_satuan = $nama_satuan;
        $satuan->deskripsi = $deskripsi;
        $satuan->status = $status;
        $satuan->created_by = auth()->check() ? auth()->user()->id : null;
        $satuan->updated_by = auth()->check() ? auth()->user()->id : null;
        $satuan->created_at = now();
        $satuan->updated_at = now();
        $satuan->save();

        return response()->json([
            'status' => true,
            'message' => 'Satuan berhasil ditambahkan'
        ]);
    }

    public function find($id)
    {
        $satuan = Satuan::with(['creator', 'updater'])->find($id);
        if (!$satuan) {
            return response()->json([
                'status' => false,
                'message' => 'Satuan tidak ditemukan'
            ]);
        }

        $data = $satuan->toArray();
        $data['created_by'] = $satuan->creator ? $satuan->creator->name : '-';
        $data['updated_by'] = $satuan->updater ? $satuan->updater->name : '-';

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $satuan = Satuan::find($id);
        if (!$satuan) {
            return response()->json([
                'status' => false,
                'message' => 'Satuan tidak ditemukan'
            ]);
        }

        $nama_satuan = trim($request->input('nama_satuan'));
        $deskripsi = trim($request->input('deskripsi'));
        $status = trim($request->input('status'));

        if (empty($nama_satuan)) {
            return response()->json([
                'status' => false,
                'message' => 'Nama Satuan harus diisi'
            ]);
        }

        if (empty($status)) {
            return response()->json([
                'status' => false,
                'message' => 'Status harus diisi'
            ]);
        }

        $satuan->nama_satuan = $nama_satuan;
        $satuan->deskripsi = $deskripsi;
        $satuan->status = $status;
        $satuan->updated_by = auth()->check() ? auth()->user()->id : null;
        $satuan->updated_at = now();
        $satuan->save();

        return response()->json([
            'status' => true,
            'message' => 'Satuan berhasil diperbarui'
        ]);
    }

    public function delete($id)
    {
        $satuan = Satuan::find($id);
        if (!$satuan) {
            return response()->json([
                'status' => false,
                'message' => 'Satuan tidak ditemukan'
            ]);
        }

        $satuan->delete();

        return response()->json([
            'status' => true,
            'message' => 'Satuan berhasil dihapus'
        ]);
    }

    // API untuk autocomplete satuan
    public function search(Request $request)
    {
        $q = $request->get('q');
        $satuans = Satuan::where('nama_satuan', 'like', '%' . $q . '%')
                        ->orWhere('kode_satuan', 'like', '%' . $q . '%')
                        ->where('status', 'AKTIF')
                        ->limit(10)
                        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $satuans
        ]);
    }
}
