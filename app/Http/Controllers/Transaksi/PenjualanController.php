<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;


//MODEL
use App\Models\Barang;
use App\Models\HargaBarang;
use App\Models\Log;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\KonversiSatuan;

use App\Models\PenjualanDetail;
use Illuminate\Http\Request;

class PenjualanController extends Controller
{
    public function create() {
        $kodePenjualan = $this->generateKodePenjualan();
        //disini ambil pelanggannya
        $pelangganDefault = Pelanggan::find(1);
        return view('transaksi.penjualan.create',['kodePenjualan'=>$kodePenjualan,'pelangganDefault'=>$pelangganDefault]);
    }

    public function getSatuanBarang($barangId) {
        $hargaBarang = HargaBarang::where('harga_barang.barang_id', $barangId)
            ->join('satuan', 'satuan.id', '=', 'harga_barang.satuan_id')
            ->select(
                'harga_barang.*',
                'satuan.nama_satuan'
            )
            ->get();
        return response()->json([
            'success'=>true,
            'datas' => $hargaBarang
        ]);
    }

    public function getTypeHargaBarang($barangId, $satuanId)
    {
        $data = HargaBarang::where('barang_id', $barangId)
            ->where('satuan_id', $satuanId)
            ->join('satuan', 'satuan.id', '=', 'harga_barang.satuan_id')
            ->select(
                'harga_barang.*',
                'satuan.nama_satuan'
            )
            ->get();

        return response()->json([
            'success' => true,
            'datas' => $data
        ]);
    }

    public function getHargaJual($barangId, $satuanId, $tipeHarga) {
        $data = HargaBarang::where('barang_id', $barangId)
            ->where('satuan_id', $satuanId)
            ->where('tipe_harga', $tipeHarga)
            ->join('satuan', 'satuan.id', '=', 'harga_barang.satuan_id')
            ->select(
                'harga_barang.*',
                'satuan.nama_satuan'
            )
            ->first();
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getDetailBarang($barangId) {
        $data = Barang::with(['kategori', 'satuan'])->find($barangId);

        return response()->json([
            'success' => true,
            'data'  => $data
        ]);
    }

    public function store(Request $request) {
        $kode_penjualan     = $this->generateKodePenjualan();
        $tanggal_penjualan  = trim($request->tanggal_penjualan);
        $pelanggan_id       = trim($request->pelanggan_id);
        $catatan            = trim($request->catatan);

        $kode_penjualan     = trim($kode_penjualan);
        $tanggal_penjualan  = date('Y-m-d', strtotime($tanggal_penjualan));

        

        //disini save dulu
        $penjualan = new Penjualan();
        $penjualan->kode_penjualan = $kode_penjualan;
        $penjualan->tanggal_penjualan = $tanggal_penjualan;
        $penjualan->pelanggan_id = $pelanggan_id;
        $penjualan->total = 0;
        $penjualan->diskon = 0;
        $penjualan->ppn = 0;
        $penjualan->grand_total = 0;
        $penjualan->catatan = $catatan;
        $penjualan->created_by = auth()->id();
        $penjualan->created_at = now();
        $penjualan->updated_by = auth()->id();
        $penjualan->updated_at = now();
        $penjualan->save();

        $satuan_konversi = 0;
        $subTotal = 0;

        //disini simpan penjualan detailnya
        foreach ($request->detail as $detail) {
            //disini ambil data barang
            $barang = Barang::find($detail['barang_id']);
            $harga_beli = round($barang->harga_beli,2);

            // Find conversion
            $konversi = KonversiSatuan::where('barang_id', $detail['barang_id'])
                ->where('satuan_konversi_id', $detail['satuan_id'])
                ->where('status', 'aktif')
                ->first();
            if ($konversi) {
                $satuan_konversi = $detail['qty']*$konversi->nilai_konversi;
                $satuan_konversi = round($satuan_konversi);
            } else {
                $satuan_konversi = $detail['qty'];
            }

            $harga_jual = $detail['subtotal']/$satuan_konversi;
            $harga_jual = round($harga_jual,2);

            $penjualanDetail = new PenjualanDetail();
            $penjualanDetail->penjualan_id = $penjualan->id;
            $penjualanDetail->barang_id = $detail['barang_id'];
            $penjualanDetail->satuan_id = $detail['satuan_id'];
            $penjualanDetail->qty = $detail['qty'];
            $penjualanDetail->qty_konversi = $satuan_konversi;
            $penjualanDetail->harga_beli = $harga_beli;
            $penjualanDetail->harga_jual = $harga_jual;
            $penjualanDetail->subtotal = $detail['subtotal'];
            $penjualanDetail->created_by = auth()->id();
            $penjualanDetail->created_at = now();
            $penjualanDetail->updated_by = auth()->id();
            $penjualanDetail->updated_at = now();
            $penjualanDetail->save();

            $subTotal += $detail['subtotal'];
        }

        $subTotal = round($subTotal,2);

        $remainder = $subTotal % 1000;
        $pembulatan = 0;

        if ($remainder >= 1 && $remainder <= 499) {
            $pembulatan = 500 - $remainder;
        } else if ($pembulatan >= 501) {
            $pembulatan = 1000 - $remainder;
        }
        $pembulatan = round($pembulatan,2);
        $grandTotal = $subTotal+($pembulatan);
        $grandTotal = round($grandTotal);


        //disini sum subtotal untuk ke subtotal master
        $penjualanUpdate = Penjualan::find($penjualan->id);
        $penjualanUpdate->total = $subTotal;
        $penjualanUpdate->pembulatan = $pembulatan;
        $penjualanUpdate->grand_total = $grandTotal;
        $penjualanUpdate->updated_by = auth()->id();
        $penjualanUpdate->updated_at = now();
        $penjualanUpdate->save();

        $log = new Log();
        $log->keterangan = 'Tambah Penjualan | No. Penjualan : '.$penjualanUpdate->kode_penjualan.' | Grand Total Penjualan : Rp. '.number_format($penjualanUpdate->grand_total, 0, ',', '.');
        $log->created_by = auth()->id();
        $log->created_at = now();
        $log->save();

        return response()->json([
            'success'   => true,
            'message'   => 'Penjualan berhasil'
        ]);
    }
    
    public function getPaketBarang(Request $request) {
        $barangIds = $request->barang_ids;
        $totalBarang = count($barangIds);

        
    }

    private function generateKodePenjualan(): string
    {
        $date = now()->format('Ymd');
        $lastSale = Penjualan::where('kode_penjualan', 'like', "PJ-{$date}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastSale) {
            $lastNumber = (int) substr($lastSale->kode_penjualan, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "PJ-{$date}-" . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
