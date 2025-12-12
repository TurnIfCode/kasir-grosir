<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use App\Models\Log;
use App\Models\KonversiSatuan;
use App\Models\HargaBarang;
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
            $search = $request->get('search')['value'] ?? '';

            // urutan kolom sesuai DataTables frontend
            $columns = [
                0 => 'kode_satuan',
                1 => 'nama_satuan',
                2 => 'deskripsi',
                3 => 'status'
            ];

            // ===========================
            // 🔹 Ambil parameter ORDER dari DataTables
            // ===========================
            $orderColIndex = $request->order[0]['column'] ?? 0;
            $orderDir      = $request->order[0]['dir'] ?? 'asc';
            $orderColumn   = $columns[$orderColIndex];

            // Query dasar
            $query = Satuan::where('status', 'AKTIF');

            // ===========================
            // 🔍 Searching
            // ===========================
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('kode_satuan', 'like', "%$search%")
                    ->orWhere('nama_satuan', 'like', "%$search%")
                    ->orWhere('deskripsi', 'like', "%$search%");
                });
            }

            // ===========================
            // 🔽 Sorting
            // ===========================
            $query->orderBy($orderColumn, $orderDir);

            // ===========================
            // 📌 Hitung data
            // ===========================
            $totalRecords = Satuan::where('status', 'AKTIF')->count();
            $filteredRecords = $query->count();

            // ===========================
            // 📌 Pagination
            // ===========================
            $satuans = $query->skip($start)->take($length)->get();

            // ===========================
            // 📌 Format DataTables
            // ===========================
            $data = [];
            foreach ($satuans as $satuan) {
                $deleteBtn = auth()->user()->role == 'ADMIN'
                    ? '<a data-id="' . $satuan->id . '" id="btnDelete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>'
                    : '';

                $data[] = [
                    'id'            => $satuan->id,
                    'kode_satuan'   => $satuan->kode_satuan,
                    'nama_satuan'   => $satuan->nama_satuan,
                    'deskripsi'     => $satuan->deskripsi,
                    'status'        => $satuan->status,
                    'aksi'          => '<a id="btnDetail" data-id="' . $satuan->id . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                        <a id="btnEdit" data-id="' . $satuan->id . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>'
                                        . $deleteBtn
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
        $kode_satuan    = trim($request->input('kode_satuan'));
        $nama_satuan    = trim($request->input('nama_satuan'));
        $deskripsi      = trim($request->input('deskripsi'));
        $status         = trim($request->input('status'));

        if (empty($kode_satuan)) {
            return response()->json([
                'success'   => false,
                'message'   => 'Kode Satuan harus diisi',
                'form'      => 'kode_satuan'
            ]);
        }

        if (strlen($kode_satuan) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Kode Satuan minimal 3 karakter',
                'form'      => 'kode_satuan'
            ]);
        }

        // cek kode_satuan sudah ada atau belum
        $cekSatuan = Satuan::where('kode_satuan', $kode_satuan)->first();
        if ($cekSatuan) {
            return response()->json([
                'success'    => false,
                'message'   => 'Kode Satuan sudah terdaftar',
                'form'      => 'kode_satuan'
            ]);
        }

        if (empty($nama_satuan)) {
            return response()->json([
                'success'    => false,
                'message'   => 'Nama Satuan harus diisi',
                'form'      => 'nama_satuan'
            ]);
        }

        //cek nama_satuan sudah ada atau belum
        $cekNama = Satuan::where("nama_satuan", $nama_satuan)->first();
        if ($cekNama) {
            return response()->json([
                'success'    => false,
                'message'   => 'Nama Satuan sudah terdaftar',
                'form'      => 'nama_satuan'
            ]);
        }

        if (empty($status)) {
            return response()->json([
                'success' => false,
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

        $newLog = new Log();
        $newLog->keterangan = 'Menambahkan satuan baru: ' . $satuan->nama_satuan . ' (Kode Satuan: ' . $satuan->kode_satuan . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Satuan berhasil ditambahkan'
        ]);
    }

    public function find($id)
    {
        $satuan = Satuan::with(['creator', 'updater'])->find($id);
        if (!$satuan) {
            return response()->json([
                'success' => false,
                'message' => 'Satuan tidak ditemukan'
            ]);
        }

        $data = $satuan->toArray();
        $data['created_by'] = $satuan->creator ? $satuan->creator->name : '-';
        $data['updated_by'] = $satuan->updater ? $satuan->updater->name : '-';

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $satuan = Satuan::find($id);
        if (!$satuan) {
            return response()->json([
                'success'   => false,
                'message'   => 'Satuan tidak ditemukan',
                'form'      => 'reload'
            ]);
        }

        $nama_satuan = trim($request->input('nama_satuan'));
        $deskripsi = trim($request->input('deskripsi'));
        $status = trim($request->input('status'));

        if (empty($nama_satuan)) {
            return response()->json([
                'success'   => false,
                'message'   => 'Nama Satuan harus diisi',
                'form'      => 'nama_satuan'
            ]);
        }

        if (empty($status)) {
            return response()->json([
                'success' => false,
                'message' => 'Status harus diisi'
            ]);
        }

        //cek nama satuan
        $cekNama = Satuan::where("nama_satuan", $nama_satuan)->where("id", "!=", $id)->count();
        if ($cekNama > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Nama satuan sudah terdaftar',
                'form'      => 'nama_satuan'
            ]);
        }

        $satuan->nama_satuan = $nama_satuan;
        $satuan->deskripsi = $deskripsi;
        $satuan->status = $status;
        $satuan->updated_by = auth()->check() ? auth()->user()->id : null;
        $satuan->updated_at = now();
        $satuan->save();

        $newLog = new Log();
        $newLog->keterangan = 'Memperbarui satuan: ' . $satuan->nama_satuan . ' (Kode Satuan: ' . $satuan->kode_satuan . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Satuan berhasil diperbarui'
        ]);
    }

    public function delete($id)
    {
        $satuan = Satuan::find($id);
        if (!$satuan) {
            return response()->json([
                'success' => false,
                'message' => 'Satuan tidak ditemukan'
            ]);
        }

        $namaSatuan = $satuan->nama_satuan;
        $kodeSatuan = $satuan->kode_satuan;

        //cek sudah ada di table konversi_satuan
        $cekKonversiSatuanDasar = KonversiSatuan::where('satuan_dasar_id', $id)->count();
        if ($cekKonversiSatuanDasar > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Sudah digunakan. Tidak dapat dihapus'
            ]);
        }

        $cekKonversiSatuanKonversi = KonversiSatuan::where('satuan_konversi_id', $id)->count();
        if ($cekKonversiSatuanKonversi > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Sudah digunakan. Tidak dapat dihapus'
            ]);
        }

        //cek sudah ada di harga_barang atau belum
        $cekHargaBarang = HargaBarang::where('satuan_id',$id)->count();
        if ($cekHargaBarang > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Sudah digunakan. Tidak dapat dihapus'
            ]);
        }

        $satuan->delete();

        $newLog = new Log();
        $newLog->keterangan = 'Menghapus satuan: ' . $namaSatuan . ' (Kode Satuan: ' . $kodeSatuan . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'success' => true,
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
            'success' => true,
            'data' => $satuans
        ]);
    }
}
