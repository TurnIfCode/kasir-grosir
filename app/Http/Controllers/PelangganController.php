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
                    'aksi' => '<a href="#" id="btnDetail" data-id="' . $pelanggan->id . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a> <a href="#" id="btnEdit" data-id="' . $pelanggan->id . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a> <a href="#" data-id="' . $pelanggan->id . '" id="btnDelete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>'
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
            'nama_pelanggan' => 'required|string|max:150',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,non_aktif'
        ], [
            'nama_pelanggan.required' => 'Nama Pelanggan wajib diisi',
            'email.email' => 'Format email tidak valid',
            'status.required' => 'Status wajib dipilih'
        ]);

        // Generate kode_pelanggan otomatis
        $kodePelanggan = $this->generateKodePelanggan();

        Pelanggan::create([
            'kode_pelanggan' => $kodePelanggan,
            'nama_pelanggan' => $request->nama_pelanggan,
            'telepon' => $request->telepon,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'status' => $request->status,
            'created_by' => auth()->check() ? auth()->user()->id : null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Pelanggan berhasil ditambahkan'
        ]);
    }

    public function find($id)
    {
        $pelanggan = Pelanggan::with(['creator', 'updater'])->findOrFail($id);
        $data = $pelanggan->toArray();
        $data['created_by'] = $pelanggan->creator ? $pelanggan->creator->name : '-';
        $data['updated_by'] = $pelanggan->updater ? $pelanggan->updater->name : '-';
        return response()->json($data);
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
            'updated_by' => auth()->check() ? auth()->user()->id : null,
            'updated_at' => now()
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

    public function generateKode()
    {
        $kodePelanggan = $this->generateKodePelanggan();
        return response()->json(['kode_pelanggan' => $kodePelanggan]);
    }

    private function generateKodePelanggan()
    {
        $lastPelanggan = Pelanggan::orderBy('id', 'desc')->first();
        $nextNumber = $lastPelanggan ? intval(substr($lastPelanggan->kode_pelanggan, 3)) + 1 : 1;
        return 'PLG' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // API untuk autocomplete pelanggan
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $pelanggans = Pelanggan::where(function($q) use ($query) {
            $q->where('nama_pelanggan', 'LIKE', "%{$query}%")
              ->orWhere('kode_pelanggan', 'LIKE', "%{$query}%")
              ->orWhere('telepon', 'LIKE', "%{$query}%");
        })
        ->where('status', 'aktif')
        ->limit(10)
        ->get(['id', 'kode_pelanggan', 'nama_pelanggan', 'telepon']);

        return response()->json([
            'status' => 'success',
            'data' => $pelanggans
        ]);
    }
}
