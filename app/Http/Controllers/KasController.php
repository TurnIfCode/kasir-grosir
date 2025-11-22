<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use App\Models\KasSaldo;
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

        // Update saldo kas
        $this->updateSaldoKas($sumberKas, $tipe, $nominal);

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

        // Revert previous saldo
        $this->updateSaldoKas($kas->sumber_kas, $kas->tipe === 'masuk' ? 'keluar' : 'masuk', $kas->nominal);

        $kas->tanggal = $tanggal;
        $kas->tipe = $tipe;
        $kas->sumber_kas = $sumberKas;
        $kas->kategori = $kategori ?: null;
        $kas->keterangan = $keterangan ?: null;
        $kas->nominal = $nominal;
        $kas->save();

        // Update new saldo
        $this->updateSaldoKas($sumberKas, $tipe, $nominal);

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

        // Revert saldo before delete
        $this->updateSaldoKas($kas->sumber_kas, $kas->tipe === 'masuk' ? 'keluar' : 'masuk', $kas->nominal);

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

    private function updateSaldoKas($sumberKas, $tipe, $nominal)
    {
        $saldo = KasSaldo::where('sumber_kas', $sumberKas)->first();

        if (!$saldo) {
            $saldo = new KasSaldo();
            $saldo->sumber_kas = $sumberKas;
            $saldo->saldo_awal = 0;
            $saldo->saldo_akhir = 0;
        }

        if ($tipe === 'masuk') {
            $saldo->saldo_akhir += $nominal;
        } else {
            $saldo->saldo_akhir -= $nominal;
        }

        $saldo->save();
    }
}
