<?php

namespace App\Http\Controllers;

//Model
use App\Models\Transfer;
use App\Models\Log;
use App\Models\Kas;
use App\Models\KasSaldo;
use App\Models\KasSaldoTransaksi;

use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function add()
    {
        $kasSaldo = KasSaldo::all();
        return view('transfer.add', compact('kasSaldo'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search')['value'];
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $query = Transfer::with('createdBy');

            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }

            if (!empty($search)) {
                $query->where('bank_tujuan', 'like', '%' . $search . '%')
                      ->orWhere('nominal_transfer', 'like', '%' . $search . '%')
                      ->orWhere('admin_bank', 'like', '%' . $search . '%')
                      ->orWhere('grand_total', 'like', '%' . $search . '%')
                      ->orWhere('catatan', 'like', '%' . $search . '%')
                      ->orWhereHas('createdBy', function($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%');
                      });
            }

            $totalRecords = Transfer::count();
            $filteredRecords = $query->count();

            $transfers = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($transfers as $transfer) {
                $data[] = [
                    'id' => $transfer->id,
                    'bank_asal' => $transfer->bank_asal,
                    'bank_tujuan' => $transfer->bank_tujuan,
                    'nominal_transfer' => number_format($transfer->nominal_transfer, 2),
                    'admin_bank' => number_format($transfer->admin_bank, 2),
                    'grand_total' => number_format($transfer->grand_total, 2),
                    'catatan' => $transfer->catatan ?: '-',
                    'created_by' => $transfer->createdBy ? $transfer->createdBy->name : '-',
                    'created_at' => $transfer->created_at ? \Carbon\Carbon::parse($transfer->created_at)->format('Y-m-d H:i:s') : '-'
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('transfer.index');
    }

    public function store(Request $request)
    {
        $bank_asal = trim($request->bank_asal);
        $bank_tujuan = trim($request->bank_tujuan);
        $nominal_transfer = trim($request->nominal_transfer);
        $admin_bank = trim($request->admin_bank);
        $margin = trim($request->margin);
        $catatan = trim($request->catatan);
        $grand_total = 0;


        if ($bank_asal == '' || $bank_tujuan == '' || $nominal_transfer == '' || $admin_bank == '') {
            return json([
                'status' => false,
                'message' => 'Semua field wajib diisi kecuali catatan'
            ]);
        }

        if (!is_numeric($nominal_transfer) || !is_numeric($admin_bank) || !is_numeric($margin)) {
            return json([
                'status' => false,
                'message' => 'Nominal transfer, admin bank, dan margin harus berupa angka'
            ]);
        }

        $nominal_transfer = round($nominal_transfer);
        $admin_bank = round($admin_bank);
        $margin = round($margin);

        if ($nominal_transfer <= 0) {
            return json([
                'status' => false,
                'message' => 'Nominal transfer harus lebih besar dari 0'
            ]);
        }

        if ($admin_bank < 0) {
            return json([
                'status' => false,
                'message' => 'Admin bank tidak boleh negatif'
            ]);
        }

        if ($margin <= 0) {
            return json([
                'status' => false,
                'message' => 'Margin harus lebih besar dari 0'
            ]);
        }

        if (empty($catatan)) {
            $catatan = 'Tranfer dari ' . $bank_asal . ' ke ' . $bank_tujuan . ' sebesar ' . number_format($nominal_transfer, 2);
        }

        $grand_total = $nominal_transfer + $admin_bank + $margin;
        $grand_total = round($grand_total, 2);

        //AMBIL DULU DATA SALDO
        $kasSaldoData = KasSaldo::find($bank_asal);
        if (!$kasSaldoData) {
            return response()->json([
                'status' => false,
                'message' => 'Sumber kas tidak ditemukan'
            ]);
        }


        if (round($kasSaldoData->saldo,2) < round($nominal_transfer + $admin_bank,2)) {
            return response()->json([
                'status' => false,
                'message' => 'Saldo kas tidak mencukupi untuk melakukan transfer'
            ]);
        }

        $transfer = new Transfer();
        $transfer->bank_asal = $kasSaldoData->kas;
        $transfer->bank_tujuan = $bank_tujuan;
        $transfer->nominal_transfer = $nominal_transfer;
        $transfer->admin_bank = $admin_bank;
        $transfer->margin = $margin;
        $transfer->grand_total = $grand_total;
        $transfer->catatan = $catatan;
        $transfer->created_by = auth()->id();
        $transfer->created_at = now();
        $transfer->save();

        $newLog = new Log();
        $newLog->keterangan = 'Menambahkan transfer dari ' . $kasSaldoData->kas . ' ke ' . $bank_tujuan . ' sebesar ' . number_format($nominal_transfer, 2);
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        $nominalKasKeluar = $nominal_transfer + $admin_bank;
        $nominalKasKeluar = round($nominalKasKeluar, 2);

        $newKas = new Kas();
        $newKas->tanggal = date('Y-m-d');
        $newKas->tipe = 'keluar';
        $newKas->sumber_kas = $kasSaldoData->kas;
        $newKas->kategori = 'Transfer/Topup';
        $newKas->keterangan = 'Transfer ke ' . $bank_tujuan . ' sebesar ' . number_format($nominalKasKeluar, 2);
        $newKas->nominal = $nominalKasKeluar;
        $newKas->user_id = auth()->id();
        $newKas->created_by = auth()->id();
        $newKas->created_at = now();
        $newKas->updated_by = auth()->id();
        $newKas->updated_at = now();
        $newKas->save();

        
        

        $saldoAkhir = $kasSaldoData->saldo;
        $saldoAkhir = round($saldoAkhir, 2);

        $sisaSaldoAkhir = $saldoAkhir - $nominalKasKeluar;
        $sisaSaldoAkhir = round($sisaSaldoAkhir, 2);

        // disini ambil kas saldo berdasarkan sumber_kas_transaksi
        $kasSaldoTransaksi = new KasSaldoTransaksi();
        $kasSaldoTransaksi->kas_saldo_id = $bank_asal;
        $kasSaldoTransaksi->tipe = 'keluar';
        $kasSaldoTransaksi->saldo_awal = $saldoAkhir;
        $kasSaldoTransaksi->saldo_akhir = $sisaSaldoAkhir;
        $kasSaldoTransaksi->keterangan = 'Transfer ke ' . $bank_tujuan . ' sebesar ' . number_format($nominal_transfer, 2);
        $kasSaldoTransaksi->created_by = auth()->id();
        $kasSaldoTransaksi->created_at = now();
        $kasSaldoTransaksi->save();
        
        if ($kasSaldoData) {
            $kasSaldoData->saldo = $sisaSaldoAkhir;
            $kasSaldoData->updated_by = auth()->id();
            $kasSaldoData->updated_at = now();
            $kasSaldoData->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Transfer berhasil ditambahkan'
        ]);
    }

    public function find($id)
    {
        $transfer = Transfer::find($id);
        if (!$transfer) {
            return response()->json([
                'status' => false,
                'message' => 'Transfer tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $transfer
        ]);
    }

    public function update(Request $request, $id)
    {
        $transfer = Transfer::find($id);
        if (!$transfer) {
            return response()->json([
                'status' => false,
                'message' => 'Transfer tidak ditemukan'
            ]);
        }

        $request->validate([
            'bank_tujuan' => 'required',
            'nominal_transfer' => 'required|numeric',
            'admin_bank' => 'required|numeric',
            'catatan' => 'nullable',
        ]);

        $grand_total = $request->nominal_transfer + $request->admin_bank;

        $transfer->bank_asal = $request->bank_asal;
        $transfer->bank_tujuan = $request->bank_tujuan;
        $transfer->nominal_transfer = $request->nominal_transfer;
        $transfer->admin_bank = $request->admin_bank;
        $transfer->margin = $request->margin;
        $transfer->grand_total = $grand_total;
        $transfer->catatan = $request->catatan;
        $transfer->save();

        return response()->json([
            'status' => true,
            'message' => 'Transfer berhasil diperbarui'
        ]);
    }

    public function delete($id)
    {
        $transfer = Transfer::find($id);
        if (!$transfer) {
            return response()->json([
                'status' => false,
                'message' => 'Transfer tidak ditemukan'
            ]);
        }

        $transfer->delete();

        return response()->json([
            'status' => true,
            'message' => 'Transfer berhasil dihapus'
        ]);
    }
}
