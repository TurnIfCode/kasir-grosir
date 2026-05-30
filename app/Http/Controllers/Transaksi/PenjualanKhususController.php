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
use App\Models\PenjualanPembayaran;
use App\Models\PenjualanRetur;
use App\Models\PenjualanReturDetail;
use App\Models\ProfilToko;
use App\Models\Satuan;

use Illuminate\Http\Request;

class PenjualanKhususController extends Controller
{
    public function index()
    {
        return view('transaksi.penjualan_khusus.index');
    }

    public function data(Request $request)
    {
        $query = Penjualan::with('pelanggan');

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_awal')) {
            $query->where('tanggal_penjualan', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_penjualan', '<=', $request->tanggal_akhir);
        }

        $data = [];
        foreach ($query->get() as $penjualan) {
            $data[] = [
                'kode_penjualan' => $penjualan->kode_penjualan,
                'tanggal_penjualan' => $penjualan->tanggal_penjualan->format('d/m/Y'),
                'pelanggan' => $penjualan->pelanggan ? $penjualan->pelanggan->nama_pelanggan : '-',
                'grand_total' => 'Rp ' . number_format($penjualan->grand_total, 0, ',', '.'),
                'jenis_pembayaran' => $penjualan->jenis_pembayaran,
                'status' => $penjualan->status,
                'aksi' => '<a href="' . route('penjualan.show', $penjualan->id) . '" class="btn btn-info btn-sm" title="Lihat"><i class="fas fa-eye"></i></a>'
            ];
        }

        return response()->json([
            'data' => $data
        ]);
    }

    public function create() {
        $kodePenjualan = $this->generateKodePenjualan();
        //disini ambil pelanggannya
        $pelangganDefault = Pelanggan::find(1);
        return view('transaksi.penjualan_khusus.create',['kodePenjualan'=>$kodePenjualan,'pelangganDefault'=>$pelangganDefault]);
    }

    public function store(Request $request) {
        $kode_penjualan         = $this->generateKodePenjualan();
        $tanggal_penjualan      = trim($request->tanggal_penjualan);
        $pelanggan_id           = trim($request->pelanggan_id);
        $catatan                = trim($request->catatan);


        $kode_penjualan         = trim($kode_penjualan);
        $tanggal_penjualan      = date('Y-m-d', strtotime($tanggal_penjualan));
        $jenis_pembayaran       = trim($request->jenis_pembayaran);
        $dibayar                = trim($request->dibayar);
        $dibayar                = round($dibayar);


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

        $qty_konversi = 0;
        $subTotal = 0;

        $hitungStok = 0;

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
                $qty_konversi = round($detail['qty'],2)*round($konversi->nilai_konversi,2);
                $qty_konversi = round($qty_konversi,2);
            } else {
                $qty_konversi = round($detail['qty'],2);
            }

            $harga_jual = round($detail['subtotal'],2)/$qty_konversi;
            $harga_jual = round($harga_jual,2);
            $subtotal   = $harga_jual*$qty_konversi;
            $subtotal   = round($subtotal,2);

            $penjualanDetail                = new PenjualanDetail();
            $penjualanDetail->penjualan_id  = $penjualan->id;
            $penjualanDetail->barang_id     = $detail['barang_id'];
            $penjualanDetail->satuan_id     = $detail['satuan_id'];
            $penjualanDetail->qty           = $detail['qty'];
            $penjualanDetail->qty_konversi  = $qty_konversi;
            $penjualanDetail->harga_beli    = $harga_beli;
            $penjualanDetail->harga_jual    = $harga_jual;
            $penjualanDetail->subtotal      = $subtotal;
            $penjualanDetail->created_by    = auth()->id();
            $penjualanDetail->created_at    = now();
            $penjualanDetail->updated_by    = auth()->id();
            $penjualanDetail->updated_at    = now();
            $penjualanDetail->save();

            $subTotal += $detail['subtotal'];

            //disini ambil data barang untuk update stok saat penjualan
            $dataBarang = Barang::find($detail['barang_id']);
            $stokSebelum = round($dataBarang->stok,2);
            $hitungStok = $stokSebelum - $qty_konversi;
            $hitungStok = round($hitungStok,2);

            // Update stok barang
            $dataBarang->stok = $hitungStok;
            $dataBarang->updated_by = auth()->id();
            $dataBarang->updated_at = now();
            $dataBarang->save();

            $insertLogBarang = new Log();
            $insertLogBarang->keterangan = 'Update Stok Barang | No. Penjualan : '.$kode_penjualan.' | Barang : '.$dataBarang->nama_barang.' | Stok Sebelum : '.number_format($stokSebelum, 0, ',', '.').' | Stok Setelah : '.number_format($hitungStok, 0, ',', '.');
            $insertLogBarang->created_by = auth()->id();
            $insertLogBarang->created_at = now();
            $insertLogBarang->save();
        }


        $subTotal = round($subTotal,2);

        $remainder = ($subTotal) % 1000;
        $pembulatan = 0;

        if ($remainder >= 1 && $remainder <= 499) {
            $pembulatan = 500 - $remainder;
        } else if ($remainder >= 501) {
            $pembulatan = 1000 - $remainder;
        }
        $pembulatan = round($pembulatan,2);
        $grandTotal = ($subTotal)+($pembulatan);
        $grandTotal = round($grandTotal);

        $kembalian = 0;

        if ($jenis_pembayaran == 'tunai') {
            //disini hitung kembalian
            $kembalian = $dibayar-$grandTotal;
            $kembalian = round($kembalian,2);

            //disini insert ke pembayaran
            $penjualanPembayaran = new PenjualanPembayaran();
            $penjualanPembayaran->penjualan_id = $penjualan->id;
            $penjualanPembayaran->metode = $jenis_pembayaran;
            $penjualanPembayaran->nominal =  $dibayar;
            $penjualanPembayaran->created_by = auth()->id();
            $penjualanPembayaran->created_at = now();
            $penjualanPembayaran->updated_by = auth()->id();
            $penjualanPembayaran->updated_at = now();
            $penjualanPembayaran->save();
        }

        if ($jenis_pembayaran == 'non_tunai') {
            $dibayar = $grandTotal;
        }


        //disini sum subtotal untuk ke subtotal master
        $penjualanUpdate = Penjualan::find($penjualan->id);
        $penjualanUpdate->jenis_pembayaran = $jenis_pembayaran;
        $penjualanUpdate->dibayar = $dibayar;
        $penjualanUpdate->kembalian = $kembalian;
        $penjualanUpdate->total = $subTotal;
        $penjualanUpdate->potongan = 0;
        $penjualanUpdate->pembulatan = $pembulatan;
        $penjualanUpdate->grand_total = $grandTotal;
        $penjualanUpdate->status = 'selesai';
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
            'message'   => 'Penjualan berhasil',
            'data'      => $penjualanUpdate
        ]);
    }

    public function show($id)
    {
        $penjualan = Penjualan::with(['details.barang', 'details.satuan', 'pelanggan', 'pembayarans'])->findOrFail($id);
        return view('transaksi.penjualan.show', compact('penjualan'));
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

