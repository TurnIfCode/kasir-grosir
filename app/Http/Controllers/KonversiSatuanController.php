<?php

namespace App\Http\Controllers;

use App\Models\KonversiSatuan;
use App\Models\Barang;
use App\Models\Satuan;
use Illuminate\Http\Request;

class KonversiSatuanController extends Controller
{
    public function index()
    {
        $satuan = Satuan::where('status', 'aktif')->get();
        return view('konversi.index', compact('satuan'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search') ? $request->get('search')['value'] : '';
            $barangId = $request->get('barang_id');

            $query = KonversiSatuan::with('barang', 'satuanDasar', 'satuanKonversi');

            if ($barangId) {
                $query->where('barang_id', $barangId);
            }

            if (!empty($search)) {
                $query->whereHas('barang', function($q) use ($search) {
                    $q->where('nama_barang', 'like', '%' . $search . '%')
                      ->orWhere('kode_barang', 'like', '%' . $search . '%');
                })->orWhereHas('satuanDasar', function($q) use ($search) {
                    $q->where('nama_satuan', 'like', '%' . $search . '%');
                })->orWhereHas('satuanKonversi', function($q) use ($search) {
                    $q->where('nama_satuan', 'like', '%' . $search . '%');
                })->orWhere('status', 'like', '%' . $search . '%');
            }

            $totalRecords = KonversiSatuan::count();
            $filteredRecords = $query->count();

            $konversi = $query->skip($start)->take($length)->get();

            $data = [];
            $no = $start + 1;
            foreach ($konversi as $k) {
            $data[] = [
                    'id' => $k->id,
                    'DT_RowIndex' => $no++,
                    'barang' => $k->barang ? $k->barang->nama_barang : '-',
                    'satuan_dasar' => $k->satuanDasar ? $k->satuanDasar->nama_satuan : '-',
                    'satuan_konversi' => $k->satuanKonversi ? $k->satuanKonversi->nama_satuan : '-',
                    'nilai_konversi' => round($k->nilai_konversi),
                    'harga_beli' => 'Rp ' . number_format($k->harga_beli, 0, ',', '.'),
                    'harga_jual' => 'Rp ' . number_format($k->harga_jual, 0, ',', '.'),
                    'status' => $k->status,
                    'aksi' => '<a href="#" id="btnDetail" data-id="' . $k->id . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>'
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('konversi.index');
    }

    public function add()
    {
        $satuan = Satuan::where('status', 'aktif')->orderBy('nama_satuan', 'asc')->get();
        return view('konversi.add', compact('satuan'));
    }

    public function store(Request $request)
    {
        $barangId = $request->input('barang_id');
        $satuanDasarId = $request->input('satuan_dasar_id');
        $satuanKonversiId = $request->input('satuan_konversi_id');
        $nilaiKonversi = $request->input('nilai_konversi', 1);
        $hargaBeli = $request->input('harga_beli', 0);
        $hargaJual = $request->input('harga_jual', 0);
        $status = $request->input('status', 'aktif');

        if (empty($barangId)) {
            return response()->json([
                'status' => false,
                'message' => 'Barang harus dipilih'
            ]);
        }

        if (empty($satuanDasarId)) {
            return response()->json([
                'status' => false,
                'message' => 'Satuan Dasar harus dipilih'
            ]);
        }

        if (empty($satuanKonversiId)) {
            return response()->json([
                'status' => false,
                'message' => 'Satuan Konversi harus dipilih'
            ]);
        }

        if ($satuanDasarId == $satuanKonversiId) {
            return response()->json([
                'status' => false,
                'message' => 'Satuan Dasar dan Satuan Konversi tidak boleh sama'
            ]);
        }

        // Set multi_satuan to true for the barang
        $barang = Barang::find($barangId);
        if ($barang) {
            $barang->multi_satuan = true;
            $barang->save();
        }

        if (!is_numeric($nilaiKonversi) || $nilaiKonversi <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Nilai Konversi harus berupa angka positif'
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

        // cek konversi sudah ada atau belum
        $cekKonversi = KonversiSatuan::where('barang_id', $barangId)
                                      ->where('satuan_dasar_id', $satuanDasarId)
                                      ->where('satuan_konversi_id', $satuanKonversiId)
                                      ->first();
        if ($cekKonversi) {
            return response()->json([
                'status' => false,
                'message' => 'Konversi Satuan sudah terdaftar'
            ]);
        }

        $konversi = new KonversiSatuan();
        $konversi->barang_id = $barangId;
        $konversi->satuan_dasar_id = $satuanDasarId;
        $konversi->satuan_konversi_id = $satuanKonversiId;
        $konversi->nilai_konversi = $nilaiKonversi;
        $konversi->harga_beli = $hargaBeli;
        $konversi->harga_jual = $hargaJual;
        $konversi->status = $status;
        $konversi->created_at = now();
        $konversi->updated_at = now();
        $konversi->save();

        return response()->json([
            'status' => true,
            'message' => 'Konversi Satuan berhasil ditambahkan'
        ]);
    }

    public function find($id)
    {
        $konversi = KonversiSatuan::with('barang', 'satuanDasar', 'satuanKonversi', 'creator', 'updater')->find($id);
        if (!$konversi) {
            return response()->json([
                'status' => false,
                'message' => 'Konversi Satuan tidak ditemukan'
            ]);
        }

        $data = $konversi->toArray();
        $data['created_by'] = $konversi->creator ? $konversi->creator->name : '-';
        $data['updated_by'] = $konversi->updater ? $konversi->updater->name : '-';

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $konversi = KonversiSatuan::find($id);
        if (!$konversi) {
            return response()->json([
                'status' => false,
                'message' => 'Konversi Satuan tidak ditemukan'
            ]);
        }

        $barangId = $request->input('barang_id');
        $satuanDasarId = $request->input('satuan_dasar_id');
        $satuanKonversiId = $request->input('satuan_konversi_id');
        $nilaiKonversi = $request->input('nilai_konversi', 1);
        $hargaBeli = $request->input('harga_beli', 0);
        $hargaJual = $request->input('harga_jual', 0);
        $status = $request->input('status', 'aktif');

        if (empty($barangId)) {
            return response()->json([
                'status' => false,
                'message' => 'Barang harus dipilih'
            ]);
        }

        if (empty($satuanDasarId)) {
            return response()->json([
                'status' => false,
                'message' => 'Satuan Dasar harus dipilih'
            ]);
        }

        if (empty($satuanKonversiId)) {
            return response()->json([
                'status' => false,
                'message' => 'Satuan Konversi harus dipilih'
            ]);
        }

        if ($satuanDasarId == $satuanKonversiId) {
            return response()->json([
                'status' => false,
                'message' => 'Satuan Dasar dan Satuan Konversi tidak boleh sama'
            ]);
        }

        if (!is_numeric($nilaiKonversi) || $nilaiKonversi <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Nilai Konversi harus berupa angka positif'
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

        // cek konversi sudah ada atau belum, kecuali dirinya sendiri
        $cekKonversi = KonversiSatuan::where('barang_id', $barangId)
                                      ->where('satuan_dasar_id', $satuanDasarId)
                                      ->where('satuan_konversi_id', $satuanKonversiId)
                                      ->where('id', '!=', $id)
                                      ->first();
        if ($cekKonversi) {
            return response()->json([
                'status' => false,
                'message' => 'Konversi Satuan sudah terdaftar'
            ]);
        }

        $konversi->barang_id = $barangId;
        $konversi->satuan_dasar_id = $satuanDasarId;
        $konversi->satuan_konversi_id = $satuanKonversiId;
        $konversi->nilai_konversi = $nilaiKonversi;
        $konversi->harga_beli = $hargaBeli;
        $konversi->harga_jual = $hargaJual;
        $konversi->status = $status;
        $konversi->updated_at = now();
        $konversi->save();

        return response()->json([
            'status' => true,
            'message' => 'Konversi Satuan berhasil diperbarui'
        ]);
    }

    public function delete($id)
    {
        $konversi = KonversiSatuan::find($id);
        if (!$konversi) {
            return response()->json([
                'status' => false,
                'message' => 'Konversi Satuan tidak ditemukan'
            ]);
        }

        $konversi->delete();

        // Check if barang still has other konversi, if not set multi_satuan to false
        $remainingKonversi = KonversiSatuan::where('barang_id', $konversi->barang_id)->count();
        if ($remainingKonversi == 0) {
            $barang = Barang::find($konversi->barang_id);
            if ($barang) {
                $barang->multi_satuan = false;
                $barang->save();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Konversi Satuan berhasil dihapus'
        ]);
    }
}
