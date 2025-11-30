<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use App\Models\KasSaldo;
use App\Models\KasSaldoTransaksi;
use App\Models\Log;
use Illuminate\Http\Request;

class KasController extends Controller
{
    public function index(Request $request)
    {
        return view('kas.index');
    }

    public function data(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get('start');
        $length = $request->get('length');
        $search = $request->get('search')['value'];

        $query = Kas::with('user');

        if (!empty($search)) {
            $query->where('tanggal', 'like', '%' . $search . '%')
                  ->orWhere('tipe', 'like', '%' . $search . '%')
                  ->orWhere('sumber_kas', 'like', '%' . $search . '%')
                  ->orWhere('kategori', 'like', '%' . $search . '%')
                  ->orWhere('keterangan', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%');
                  });
        }

        $totalRecords = Kas::count();
        $filteredRecords = $query->count();

        $kas = $query->skip($start)->take($length)->orderBy('tanggal', 'desc')->get();

        $data = [];
        foreach ($kas as $k) {
            $data[] = [
                'tanggal' => $k->tanggal->format('d/m/Y'),
                'tipe' => ucfirst($k->tipe),
                'sumber_kas' => $k->sumber_kas,
                'kategori' => $k->kategori ?: '-',
                'keterangan' => $k->keterangan ?: '-',
                'nominal' => 'Rp ' . number_format($k->nominal, 0, ',', '.'),
                'user' => $k->user ? $k->user->name : '-',
                'aksi' => '<a href="' . route('kas.edit', $k->id) . '" class="btn btn-sm btn-warning">Edit</a> <a data-id="' . $k->id . '" id="btnDelete" class="btn btn-sm btn-danger">Hapus</a>'
            ];
        }

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function create()
    {
        return view('kas.create');
    }

    public function store(Request $request)
    {
        $tanggal = trim($request->input('tanggal'));
        $tipe = trim($request->input('tipe'));
        $sumberKas = trim($request->input('sumber_kas'));
        $kategori = trim($request->input('kategori'));
        $keterangan = trim($request->input('keterangan'));
        $nominal = $request->input('nominal');

        if (empty($tanggal)) {
            return response()->json([
                'status' => false,
                'message' => 'Tanggal harus diisi'
            ]);
        }

        if (empty($tipe)) {
            return response()->json([
                'status' => false,
                'message' => 'Tipe harus diisi'
            ]);
        }

        if (!in_array($tipe, ['masuk', 'keluar'])) {
            return response()->json([
                'status' => false,
                'message' => 'Tipe tidak valid'
            ]);
        }

        if (empty($sumberKas)) {
            return response()->json([
                'status' => false,
                'message' => 'Sumber Kas harus diisi'
            ]);
        }

        if (!is_numeric($nominal) || $nominal <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Nominal harus berupa angka positif'
            ]);
        }
        
        
        
        //disini cek dulu keluar atau tidak
        if ($tipe == 'keluar') {
            //ambil data saldo kas
            $saldo = KasSaldo::where('kas', $sumberKas)->first();
            //cek apakah saldo cukup
            if (!$saldo) {
                return response()->json([
                    'status' => false,
                    'message' => 'Saldo kas "' . $sumberKas . '" tidak mencukupi untuk transaksi keluar sebesar Rp ' . number_format($nominal, 0, ',', '.')
                ]);
            }
        }


        $kas = new Kas();
        $kas->tanggal = $tanggal;
        $kas->tipe = $tipe;
        $kas->sumber_kas = $sumberKas;
        $kas->kategori = $kategori ?: null;
        $kas->keterangan = $keterangan ?: null;
        $kas->nominal = $nominal;
        $kas->user_id = auth()->id();
        $kas->created_by = auth()->id();
        $kas->save();

        $keteranganTransaksi = 'Transaksi kas: ' . ucfirst($tipe) . ' - ' . $sumberKas . ' (Rp ' . number_format($nominal, 0, ',', '.') . ')';

        // Update saldo kas
        $this->updateSaldoKas($sumberKas, $tipe, $nominal, $keteranganTransaksi);

        $newLog = new Log();
        $newLog->keterangan = 'Menambahkan transaksi kas: ' . ucfirst($tipe) . ' - ' . $sumberKas . ' (Rp ' . number_format($nominal, 0, ',', '.') . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'status' => true,
            'message' => 'Transaksi kas berhasil ditambahkan'
        ]);
    }

    public function edit($id)
    {
        $kas = Kas::find($id);
        if (!$kas) {
            return redirect()->route('kas.index')->with('error', 'Data tidak ditemukan');
        }

        return view('kas.edit', compact('kas'));
    }

    public function update(Request $request, $id)
    {
        $kas = Kas::find($id);
        if (!$kas) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        $tanggal = trim($request->input('tanggal'));
        $tipe = trim($request->input('tipe'));
        $sumberKas = trim($request->input('sumber_kas'));
        $kategori = trim($request->input('kategori'));
        $keterangan = trim($request->input('keterangan'));
        $nominal = $request->input('nominal');

        if (empty($tanggal)) {
            return response()->json([
                'status' => false,
                'message' => 'Tanggal harus diisi'
            ]);
        }

        if (empty($tipe)) {
            return response()->json([
                'status' => false,
                'message' => 'Tipe harus diisi'
            ]);
        }

        if (!in_array($tipe, ['masuk', 'keluar'])) {
            return response()->json([
                'status' => false,
                'message' => 'Tipe tidak valid'
            ]);
        }

        if (empty($sumberKas)) {
            return response()->json([
                'status' => false,
                'message' => 'Sumber Kas harus diisi'
            ]);
        }

        if (!is_numeric($nominal) || $nominal <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Nominal harus berupa angka positif'
            ]);
        }

        $keteranganTransaksi = 'Menghapus transaksi kas: ' . ucfirst($tipe) . ' - ' . $sumberKas . ' (Rp ' . number_format($kas->nominal, 0, ',', '.') . ')';

        // Revert previous saldo
        $this->updateSaldoKas($kas->sumber_kas, $kas->tipe === 'masuk' ? 'keluar' : 'masuk', $kas->nominal,$keteranganTransaksi);

        $kas->tanggal = $tanggal;
        $kas->tipe = $tipe;
        $kas->sumber_kas = $sumberKas;
        $kas->kategori = $kategori ?: null;
        $kas->keterangan = $keterangan ?: null;
        $kas->nominal = $nominal;
        $kas->save();

        $keteranganTransaksi = 'Memperbarui transaksi kas: ' . ucfirst($tipe) . ' - ' . $sumberKas . ' (Rp ' . number_format($nominal, 0, ',', '.') . ')';
        // Update new saldo
        $this->updateSaldoKas($sumberKas, $tipe, $nominal, $keteranganTransaksi);

        $newLog = new Log();
        $newLog->keterangan = 'Memperbarui transaksi kas: ' . ucfirst($tipe) . ' - ' . $sumberKas . ' (Rp ' . number_format($nominal, 0, ',', '.') . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'status' => true,
            'message' => 'Transaksi kas berhasil diperbarui'
        ]);
    }

    public function destroy($id)
    {
        $kas = Kas::find($id);
        if (!$kas) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        $keteranganTransaksi = 'Menghapus transaksi kas: ' . ucfirst($kas->tipe) . ' - ' . $kas->sumber_kas . ' (Rp ' . number_format($kas->nominal, 0, ',', '.') . ')';

        // Revert saldo before delete
        $this->updateSaldoKas($kas->sumber_kas, $kas->tipe === 'masuk' ? 'keluar' : 'masuk', $kas->nominal, $keteranganTransaksi);

        $tipeKas = $kas->tipe;
        $sumberKas = $kas->sumber_kas;
        $nominalKas = $kas->nominal;

        $kas->delete();

        $newLog = new Log();
        $newLog->keterangan = 'Menghapus transaksi kas: ' . ucfirst($tipeKas) . ' - ' . $sumberKas . ' (Rp ' . number_format($nominalKas, 0, ',', '.') . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'status' => true,
            'message' => 'Transaksi kas berhasil dihapus'
        ]);
    }

    private function updateSaldoKas($sumberKas, $tipe, $nominal, $keteranganTransaksi)
    {
        //disini ambil data kas_saldo berdasarkan sumber_kas

        $saldo = KasSaldo::where('kas', $sumberKas)->first();

        if (!$saldo) {
            $saldo = new KasSaldo();
            $saldo->kas = $sumberKas;
            $saldo->saldo = 0;
            $saldo->created_by = auth()->id();
            $saldo->created_at = now();
            $saldo->updated_by = auth()->id();
            $saldo->updated_at = now();
            $saldo->save();
        }

        $saldoAwal = round($saldo->saldo,2);

        if ($tipe === 'masuk') {
            $saldoAkhir = $saldo->saldo+$nominal;
        } else {
            $saldoAkhir = $saldo->saldo-$nominal;
        }
        $saldoAkhir = round($saldoAkhir,2);

        $saldo->saldo = $saldoAkhir;
        $saldo->updated_by =  auth()->id();
        $saldo->updated_at = now();
        $saldo->save();

        // simpan ke kas_saldo_transaksi
        $newKasSaldoTransaksi = new KasSaldoTransaksi();
        $newKasSaldoTransaksi->kas_saldo_id = $saldo->id;
        $newKasSaldoTransaksi->tipe = $tipe;
        $newKasSaldoTransaksi->saldo_awal = $saldoAwal;
        $newKasSaldoTransaksi->saldo_akhir = $saldoAkhir;
        $newKasSaldoTransaksi->keterangan = $keteranganTransaksi;
        $newKasSaldoTransaksi->created_by = auth()->id();
        $newKasSaldoTransaksi->created_at = now();
        $newKasSaldoTransaksi->save();
    }
}
