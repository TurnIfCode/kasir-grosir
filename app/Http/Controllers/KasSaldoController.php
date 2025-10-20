<?php

namespace App\Http\Controllers;

use App\Models\KasSaldo;
use Illuminate\Http\Request;

class KasSaldoController extends Controller
{
    public function index()
    {
        $saldos = KasSaldo::all();
        return view('kas-saldo.index', compact('saldos'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search')['value'];

            $query = KasSaldo::query();

            if (!empty($search)) {
                $query->where('sumber_kas', 'like', '%' . $search . '%');
            }

            $totalRecords = KasSaldo::count();
            $filteredRecords = $query->count();

            $saldos = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($saldos as $saldo) {
                $data[] = [
                    'sumber_kas' => $saldo->sumber_kas,
                    'saldo_awal' => 'Rp ' . number_format($saldo->saldo_awal, 0, ',', '.'),
                    'saldo_akhir' => 'Rp ' . number_format($saldo->saldo_akhir, 0, ',', '.'),
                    'aksi' => '<a href="' . route('kas-saldo.edit', $saldo->id) . '" class="btn btn-sm btn-warning">Edit</a>'
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('kas-saldo.index');
    }

    public function create()
    {
        return view('kas-saldo.create');
    }

    public function store(Request $request)
    {
        $sumberKas = trim($request->input('sumber_kas'));
        $saldoAwal = $request->input('saldo_awal', 0);

        if (empty($sumberKas)) {
            return response()->json([
                'status' => false,
                'message' => 'Sumber Kas harus diisi'
            ]);
        }

        // Check if sumber_kas already exists
        $existing = KasSaldo::where('sumber_kas', $sumberKas)->first();
        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'Sumber Kas sudah ada'
            ]);
        }

        if (!is_numeric($saldoAwal) || $saldoAwal < 0) {
            return response()->json([
                'status' => false,
                'message' => 'Saldo Awal harus berupa angka positif atau nol'
            ]);
        }

        $saldo = new KasSaldo();
        $saldo->sumber_kas = $sumberKas;
        $saldo->saldo_awal = $saldoAwal;
        $saldo->saldo_akhir = $saldoAwal;
        $saldo->save();

        return response()->json([
            'status' => true,
            'message' => 'Saldo kas berhasil ditambahkan'
        ]);
    }

    public function edit($id)
    {
        $saldo = KasSaldo::find($id);
        if (!$saldo) {
            return redirect()->route('kas-saldo.index')->with('error', 'Data tidak ditemukan');
        }

        return view('kas-saldo.edit', compact('saldo'));
    }

    public function update(Request $request, $id)
    {
        $saldo = KasSaldo::find($id);
        if (!$saldo) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        $sumberKas = trim($request->input('sumber_kas'));
        $saldoAwal = $request->input('saldo_awal', 0);

        if (empty($sumberKas)) {
            return response()->json([
                'status' => false,
                'message' => 'Sumber Kas harus diisi'
            ]);
        }

        // Check if sumber_kas already exists for other records
        $existing = KasSaldo::where('sumber_kas', $sumberKas)->where('id', '!=', $id)->first();
        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'Sumber Kas sudah ada'
            ]);
        }

        if (!is_numeric($saldoAwal) || $saldoAwal < 0) {
            return response()->json([
                'status' => false,
                'message' => 'Saldo Awal harus berupa angka positif atau nol'
            ]);
        }

        $saldo->sumber_kas = $sumberKas;
        $saldo->saldo_awal = $saldoAwal;
        // Recalculate saldo_akhir based on transactions
        $totalMasuk = \App\Models\Kas::where('sumber_kas', $sumberKas)->where('tipe', 'masuk')->sum('nominal');
        $totalKeluar = \App\Models\Kas::where('sumber_kas', $sumberKas)->where('tipe', 'keluar')->sum('nominal');
        $saldo->saldo_akhir = $saldoAwal + $totalMasuk - $totalKeluar;
        $saldo->save();

        return response()->json([
            'status' => true,
            'message' => 'Saldo kas berhasil diperbarui'
        ]);
    }

    public function destroy($id)
    {
        $saldo = KasSaldo::find($id);
        if (!$saldo) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        // Check if there are transactions for this sumber_kas
        $transactions = \App\Models\Kas::where('sumber_kas', $saldo->sumber_kas)->count();
        if ($transactions > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak dapat menghapus saldo kas yang memiliki transaksi'
            ]);
        }

        $saldo->delete();

        return response()->json([
            'status' => true,
            'message' => 'Saldo kas berhasil dihapus'
        ]);
    }
}
