<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

//models
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\PenjualanRetur;
use App\Models\PenjualanReturDetail;
use App\Models\KonversiSatuan;

class ReturPenjualan extends Controller
{
    public function cari(Request $request)
    {
        $kode_penjualan = $request->post('kode_penjualan');
        $kode_penjualan = trim($kode_penjualan);

        $penjualan = Penjualan::where('kode_penjualan', $kode_penjualan)->first();

        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => 'Penjualan dengan kode tersebut tidak ditemukan'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'penjualan_id' => $penjualan->id,
                'kode_penjualan' => $penjualan->kode_penjualan,
                'grand_total' => round($penjualan->grand_total, 2)
            ]
        ]);
    }

    public function cari_penjualan_detail($penjualanId)
    {
        $penjualan_details = PenjualanDetail::with([
            'barang', 'satuan'
        ])->where('penjualan_id', $penjualanId)->get();

        $datas = [];
        foreach ($penjualan_details as $detail) {
            $datas[] = [
                'id' => $detail->id,
                'barang_id' => $detail->barang_id,
                'nama_barang' => $detail->barang->nama_barang,
                'satuan_id' => $detail->satuan_id,
                'nama_satuan' => $detail->satuan->nama_satuan,
                'qty_jual' => round($detail->qty, 2),
                'harga_jual' => round($detail->harga_jual, 2),
                'qty_konversi' => round($detail->qty_konversi, 2),
                'subtotal' => round($detail->subtotal, 2)
            ];
        }

        return response()->json([
            'success' => true,
            'datas' => $datas
        ]);
    }

    public function store(Request $request)
    {
        $kode_retur = $this->generateKodeRetur();

        $kode_penjualan = trim($request->post('kode_penjualan_retur'));

        $grand_total_penjualan = (float) str_replace(
            ['.', ','],
            ['', '.'],
            $request->post('grand_total_retur') ?? 0
        );

        DB::beginTransaction();

        try {

            // simpan header retur
            $penjualanRetur = new PenjualanRetur();
            $penjualanRetur->kode_retur = $kode_retur;
            $penjualanRetur->kode_penjualan = $kode_penjualan;
            $penjualanRetur->grand_total_penjualan = round($grand_total_penjualan, 2);
            $penjualanRetur->created_by = auth()->id();
            $penjualanRetur->created_at = now();
            $penjualanRetur->updated_by = auth()->id();
            $penjualanRetur->updated_at = now();
            $penjualanRetur->save();

            $sumTotalRetur = 0;

            $details = $request->post('details', []);

            foreach ($details as $detail) {

                $barang_id = $detail['barang_id'] ?? 0;

                $satuan_id = $detail['satuan_id'] ?? 0;

                $qty = (float) str_replace(
                    ['.', ','],
                    ['', '.'],
                    $detail['qty_retur'] ?? 0
                );

                $harga = (float) str_replace(
                    ['.', ','],
                    ['', '.'],
                    $detail['harga_retur'] ?? 0
                );

                $qty = round($qty, 2);

                $harga = round($harga, 2);

                $total = 0;

                // Find conversion
                $konversi = KonversiSatuan::where('barang_id', $barang_id)
                    ->where('satuan_konversi_id', $satuan_id)
                    ->where('status', 'aktif')
                    ->first();
                if ($konversi) {
                    $satuan_konversi = $qty*$konversi->nilai_konversi;
                    $satuan_konversi = round($satuan_konversi);
                } else {
                    $satuan_konversi = $qty;
                }

                if ($qty > 0) {

                    $total = round($qty * $harga, 2);

                    $penjualanReturDetail = new PenjualanReturDetail();
                    $penjualanReturDetail->penjualan_retur_id = $penjualanRetur->id;
                    $penjualanReturDetail->barang_id = $barang_id;
                    $penjualanReturDetail->satuan_id = $satuan_id;
                    $penjualanReturDetail->qty = $qty;
                    $penjualanReturDetail->qty_konversi = $satuan_konversi;
                    $penjualanReturDetail->harga = $harga;
                    $penjualanReturDetail->total = $total;
                    $penjualanReturDetail->created_by = auth()->id();
                    $penjualanReturDetail->created_at = now();
                    $penjualanReturDetail->updated_by = auth()->id();
                    $penjualanReturDetail->updated_at = now();
                    $penjualanReturDetail->save();

                    $sumTotalRetur += $total;
                }
            }

            // update total retur
            $updatePenjualanRetur = PenjualanRetur::find($penjualanRetur->id);

            $updatePenjualanRetur->grand_total_retur = round($sumTotalRetur, 2);

            $updatePenjualanRetur->updated_by = auth()->id();

            $updatePenjualanRetur->updated_at = now();

            $updatePenjualanRetur->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Retur penjualan berhasil disimpan',
                'data' => [
                    'penjualan_retur_id' => $penjualanRetur->id,
                    'kode_retur' => $kode_retur,
                    'grand_total_retur' => round($sumTotalRetur, 2)
                ]
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function generateKodeRetur()
    {
        $date = now()->format('Ymd');
        $lastSale = PenjualanRetur::where('kode_retur', 'like', "RTR-{$date}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastSale) {
            $lastNumber = (int) substr($lastSale->kode_penjualan, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "RTR{$date}" . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
