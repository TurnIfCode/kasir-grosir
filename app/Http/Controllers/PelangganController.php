<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Log;
use App\Models\Penjualan;
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
                      ->orWhere('jenis', 'like', '%' . $search . '%')
                      ->orWhere('ongkos', 'like', '%' . $search . '%')
                      ->orWhere('status', 'like', '%' . $search . '%');
            }

            // Handle ordering
            $order = $request->get('order');
            $columns = [
                'kode_pelanggan',
                'nama_pelanggan',
                'telepon',
                'email',
                'alamat',
                'jenis',
                'ongkos',
                'status',
                'created_by',
                'created_at',
                'updated_by',
                'updated_at'
            ];

            if ($order) {
                foreach ($order as $orderItem) {
                    $columnIndex = $orderItem['column'];
                    $direction = $orderItem['dir'];
                    if (isset($columns[$columnIndex])) {
                        $column = $columns[$columnIndex];
                        $query->orderBy($column, $direction);
                    }
                }
            } else {
                // Default order by kode_pelanggan asc
                $query->orderBy('kode_pelanggan', 'asc');
            }

            $totalRecords = Pelanggan::count();
            $filteredRecords = $query->count();

            $pelanggans = $query->with(['creator', 'updater'])->skip($start)->take($length)->get();

            $data = [];
            $no = $start + 1;
            foreach ($pelanggans as $pelanggan) {
                $data[] = [
                    'kode_pelanggan' => $pelanggan->kode_pelanggan,
                    'nama_pelanggan' => $pelanggan->nama_pelanggan,
                    'telepon' => $pelanggan->telepon ?: '-',
                    'email' => $pelanggan->email ?: '-',
                    'alamat' => $pelanggan->alamat ?: '-',
                    'jenis' => $pelanggan->jenis ?: '-',
                    'ongkos' => 'Rp. '.number_format($pelanggan->ongkos,0, ',','.') ?: '0',
                    'status' => $pelanggan->status,
                    'created_by' => $pelanggan->creator ? $pelanggan->creator->name : '-',
                    'created_at' => $pelanggan->created_at ? date('Y-m-d H:i:s', $pelanggan->created_at->timestamp) : '-',
                    'updated_by' => $pelanggan->updater ? $pelanggan->updater->name : '-',
                    'updated_at' => $pelanggan->updated_at ? date('Y-m-d H:i:s', $pelanggan->updated_at->timestamp) : '-',
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
        // Generate kode_pelanggan otomatis
        $kodePelanggan = $this->generateKodePelanggan();

        //cek dulu

        $namaPelanggan  = trim($request->nama_pelanggan);
        $telepon        = trim($request->telepon);
        $email          = trim($request->email);
        $alamat         = trim($request->alalamat);
        $jenis          = trim($request->jenis);
        $ongkos         = trim($request->ongkos);
        $status         = trim($request->status);

        if (empty($namaPelanggan)) {
            return response()->json([
                'success'   => false,
                'message'   => 'Nama pelanggan harus diisi.',
                'form'      => 'nama_pelanggan'
            ]);
        }

        // cek jika nama pelanggan sudah ada
        $cekPelanggan = Pelanggan::where('nama_pelanggan', $namaPelanggan)->first();
        if ($cekPelanggan) {
            return response()->json([
                'success'   => false,
                'message'   => 'Nama pelanggan sudah terdaftar.',
                'form'      => 'nama_pelanggan'
            ]);
        }

        if (empty($telepon)) {
            $telepon = '-';
        }

        if (empty($email)) {
            $email = '-';
        }

        if (empty($alamat)) {
            $alamat = '-';
        }

        if ($jenis != 'antar') {
            $ongkos = 0;
        }

        $ongkos = round($ongkos);

        if ($jenis == 'antar') {
            if ($ongkos <= 0) {
                return response()->json([
                    'success'   => false,
                    'message'   => 'Harga tambah harus lebih besar dari 0',
                    'form'      => 'ongkos'
                ]);
            }
        }

        $pelanggan = new Pelanggan();
        $pelanggan->kode_pelanggan = $kodePelanggan;
        $pelanggan->nama_pelanggan = $namaPelanggan;
        $pelanggan->telepon = $telepon;
        $pelanggan->email = $email;
        $pelanggan->alamat = $alamat;
        $pelanggan->jenis = $jenis;
        $pelanggan->ongkos = $ongkos;
        $pelanggan->status = $status;
        $pelanggan->created_by = auth()->id();
        $pelanggan->created_at = now();
        $pelanggan->updated_by = auth()->id();
        $pelanggan->updated_at = now();
        $pelanggan->save();

        $newLog = new Log();
        $newLog->keterangan = 'Menambahkan pelanggan baru: ' . $pelanggan->nama_pelanggan . ' (Kode Pelanggan: ' . $pelanggan->kode_pelanggan . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil tambah data.'
        ]);
    }

    public function find($id)
    {
        $pelanggan = Pelanggan::with(['creator', 'updater'])->find($id);
        if (!$pelanggan) {
            return response()->json([
                'success'   => false,
                'message'   => 'Data tidak ditemukan'
            ]);
        }
        $data = $pelanggan->toArray();
        $data['created_by'] = $pelanggan->creator ? $pelanggan->creator->name : '-';
        $data['updated_by'] = $pelanggan->updater ? $pelanggan->updater->name : '-';
        $data['ongkos']     = round($pelanggan->ongkos);

        return response()->json([
            'success'   => true,
            'data'      => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $pelanggan = Pelanggan::find($id);

        //cek pelanggan terdaftar atau tidak
        if (!$pelanggan) {
            return response()->json([
                'success'   => false,
                'message'   => 'Data pelanggan tidak ditemukan',
                'form'      => false
            ]);
        }

        //disini ambil semua request kirimnya
        $namaPelanggan  = trim($request->nama_pelanggan);
        $telepon        = trim($request->telepon);
        $email          = trim($request->email);
        $alamat         = trim($request->alamat);
        $jenis          = trim($request->jenis);
        $ongkos         = trim($request->ongkos);
        $status         = trim($request->status);

        //disini cek dulu apakah ada
        $cekNama = Pelanggan::where('nama_pelanggan', $namaPelanggan)->where('id','!=',$id)->first();
        if ($cekNama) {
            if ($ongkos <= 0) {
                return response()->json([
                    'success'   => false,
                    'message'   => 'Harga tambah harus lebih besar dari 0',
                    'form'      => true,
                    'form-edit' => 'edit_nama_pelanggan'
                ]);
            }
        }

        if (empty($telepon)) {
            $telepon = '-';
        }

        if (empty($email)) {
            $email = '-';
        }

        if (empty($alamat)) {
            $alamat = '-';
        }

        if ($jenis != 'antar') {
            $ongkos = 0;
        }

        $ongkos = round($ongkos);

        if ($jenis == 'antar') {
            if ($ongkos <= 0) {
                return response()->json([
                    'success'   => false,
                    'message'   => 'Harga tambah harus lebih besar dari 0',
                    'form'      => true,
                    'form-edit' => 'ongkos'
                ]);
            }
        }

        $pelanggan->nama_pelanggan = $namaPelanggan;
        $pelanggan->telepon = $telepon;
        $pelanggan->email = $email;
        $pelanggan->alamat = $alamat;
        $pelanggan->jenis = $jenis;
        $pelanggan->ongkos = $ongkos;
        $pelanggan->status = $status;
        $pelanggan->updated_by = auth()->id();
        $pelanggan->updated_at = now();
        $pelanggan->save();
        
        $newLog = new Log();
        $newLog->keterangan = 'Memperbarui pelanggan: ' . $pelanggan->nama_pelanggan . ' (Kode Pelanggan: ' . $pelanggan->kode_pelanggan . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil diperbarui'
        ]);
    }

    public function delete($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        //cek pelanggan ada atau tidak
        if (!$pelanggan) {
            return response()->json([
                'success'   => false,
                'message'   => 'Data tidak ditemukan'
            ]);
        }

        // cek sudah ada di penjualan atau belum
        $cekPenjualan = Penjualan::where('pelanggan_id', $id)->count();
        if ($cekPenjualan > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Pelanggan sudah ada di transaksi penjualan. Tidak dapat dihapus.'
            ]);
        }

        $namaPelanggan = $pelanggan->nama_pelanggan;
        $kodePelanggan = $pelanggan->kode_pelanggan;
        

        $pelanggan->delete();

        $newLog = new Log();
        $newLog->keterangan = 'Menghapus pelanggan: ' . $namaPelanggan . ' (Kode Pelanggan: ' . $kodePelanggan . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'success' => true,
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
        ->get(['id', 'kode_pelanggan', 'nama_pelanggan', 'telepon', 'jenis', 'ongkos']);

        return response()->json([
            'success' => true,
            'data' => $pelanggans
        ]);
    }
}
