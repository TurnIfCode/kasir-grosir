<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index()
    {
        return view('pelanggan.index');
    }

    public function add()
    {
        return view('pelanggan.add');
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search') ? $request->get('search')['value'] : '';

            $query = Pelanggan::query();

            if (!empty($search)) {
                $query->where('kode_pelanggan', 'like', '%' . $search . '%')
                      ->orWhere('nama_pelanggan', 'like', '%' . $search . '%')
                      ->orWhere('telepon', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('status', 'like', '%' . $search . '%');
            }

            $totalRecords = Pelanggan::count();
            $filteredRecords = $query->count();

            $pelanggans = $query->skip($start)->take($length)->get();

            $data = [];
            $no = $start + 1;
            foreach ($pelanggans as $pelanggan) {
                $data[] = [
                    'DT_RowIndex' => $no++,
                    'kode_pelanggan' => $pelanggan->kode_pelanggan,
                    'nama_pelanggan' => $pelanggan->nama_pelanggan,
                    'telepon' => $pelanggan->telepon ?: '-',
                    'email' => $pelanggan->email ?: '-',
                    'alamat' => $pelanggan->alamat ?: '-',
                    'status' => $pelanggan->status,
                    'aksi' => '<a href="#" id="btnEdit" data-id="' . $pelanggan->id . '" class="btn btn-sm btn-warning">Edit</a> <a href="#" data-id="' . $pelanggan->id . '" id="btnDelete" class="btn btn-sm btn-danger">Hapus</a>'
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('pelanggan.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_pelanggan' => 'required|string|max:50|unique:pelanggan,kode_pelanggan',
            'nama_pelanggan' => 'required|string|max:150',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,non_aktif'
        ], [
            'kode_pelanggan.required' => 'Kode Pelanggan wajib diisi',
            'kode_pelanggan.unique' => 'Kode Pelanggan sudah terdaftar',
            'nama_pelanggan.required' => 'Nama Pelanggan wajib diisi',
            'email.email' => 'Format email tidak valid',
            'status.required' => 'Status wajib dipilih'
        ]);

        Pelanggan::create([
            'kode_pelanggan' => $request->kode_pelanggan,
            'nama_pelanggan' => $request->nama_pelanggan,
            'telepon' => $request->telepon,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'status' => $request->status,
            'created_by' => auth()->check() ? auth()->user()->id : null
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Pelanggan berhasil ditambahkan'
        ]);
    }

    public function find($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        return response()->json($pelanggan);
    }

    public function update(Request $request, $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        $request->validate([
            'kode_pelanggan' => 'required|string|max:50|unique:pelanggan,kode_pelanggan,' . $id,
            'nama_pelanggan' => 'required|string|max:150',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,non_aktif'
        ], [
            'kode_pelanggan.required' => 'Kode Pelanggan wajib diisi',
            'kode_pelanggan.unique' => 'Kode Pelanggan sudah terdaftar',
            'nama_pelanggan.required' => 'Nama Pelanggan wajib diisi',
            'email.email' => 'Format email tidak valid',
            'status.required' => 'Status wajib dipilih'
        ]);

        $pelanggan->update([
            'kode_pelanggan' => $request->kode_pelanggan,
            'nama_pelanggan' => $request->nama_pelanggan,
            'telepon' => $request->telepon,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'status' => $request->status,
            'updated_by' => auth()->check() ? auth()->user()->id : null
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Pelanggan berhasil diperbarui'
        ]);
    }

    public function delete($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->delete();

        return response()->json([
            'status' => true,
            'message' => 'Pelanggan berhasil dihapus'
        ]);
    }
}
