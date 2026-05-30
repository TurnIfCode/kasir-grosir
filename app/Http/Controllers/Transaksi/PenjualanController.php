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
use PhpOffice\PhpSpreadsheet\Reader\IReader;

class PenjualanController extends Controller
{

    public function index()
    {
        return view('transaksi.penjualan.index');
    }

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

    public function storePenjualanCepat(Request $request) {

        $kode_penjualan         = $this->generateKodePenjualan();
        $tanggal_penjualan      = date('Y-m-d');
        $pelanggan_id           = 1;
        $catatan                = '-';
        $potongan               = 0;

        //disini simpan penjualan
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

        $hitungStok = 0;

        //disini simpan penjualan detailnya
        foreach ($request->details as $detail) {
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
            $harga_jual = round($harga_jual,0);

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

            //disini ambil data barang untuk update stok saat penjualan
            $dataBarang = Barang::find($detail['barang_id']);
            $stokSebelum = round($dataBarang->stok,2);
            $hitungStok = $stokSebelum - $satuan_konversi;
            $hitungStok = round($hitungStok);

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

        $remainder = ($subTotal-$potongan) % 1000;
        $pembulatan = 0;

        if ($remainder >= 1 && $remainder <= 499) {
            $pembulatan = 500 - $remainder;
        } else if ($remainder >= 501) {
            $pembulatan = 1000 - $remainder;
        }
        $pembulatan = round($pembulatan,2);
        $grandTotal = ($subTotal-$potongan)+($pembulatan);
        $grandTotal = round($grandTotal);

        $kembalian = 0;

        //disini insert ke pembayaran
        $penjualanPembayaran = new PenjualanPembayaran();
        $penjualanPembayaran->penjualan_id = $penjualan->id;
        $penjualanPembayaran->metode = 'tunai';
        $penjualanPembayaran->nominal =  $grandTotal;
        $penjualanPembayaran->created_by = auth()->id();
        $penjualanPembayaran->created_at = now();
        $penjualanPembayaran->updated_by = auth()->id();
        $penjualanPembayaran->updated_at = now();
        $penjualanPembayaran->save();

        //disini sum subtotal untuk ke subtotal master
        $penjualanUpdate = Penjualan::find($penjualan->id);
        $penjualanUpdate->jenis_pembayaran = 'tunai';
        $penjualanUpdate->dibayar = $grandTotal;
        $penjualanUpdate->kembalian = $kembalian;
        $penjualanUpdate->total = $subTotal;
        $penjualanUpdate->potongan = $potongan;
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
        $potongan               = trim($request->potongan);
        $penjualan_retur_id     = trim($request->penjualan_retur_id);


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

        $remainder = ($subTotal-$potongan) % 1000;
        $pembulatan = 0;

        if ($remainder >= 1 && $remainder <= 499) {
            $pembulatan = 500 - $remainder;
        } else if ($remainder >= 501) {
            $pembulatan = 1000 - $remainder;
        }
        $pembulatan = round($pembulatan,2);
        $grandTotal = ($subTotal-$potongan)+($pembulatan);
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
        $penjualanUpdate->potongan = $potongan;
        $penjualanUpdate->pembulatan = $pembulatan;
        $penjualanUpdate->grand_total = $grandTotal;
        $penjualanUpdate->status = 'selesai';
        $penjualanUpdate->updated_by = auth()->id();
        $penjualanUpdate->updated_at = now();
        $penjualanUpdate->save();

        //disini update stok barang jika ada retur
        if ($penjualan_retur_id) {
            $penjualanReturDetail = PenjualanReturDetail::where('penjualan_retur_id', $penjualan_retur_id)->get();
            foreach($penjualanReturDetail as $returDetail) {
                $barang = Barang::find($returDetail->barang_id);
                if ($barang) {
                    $barang->stok += $returDetail->qty_konversi;
                    $barang->save();
                }
            }

            //cari penjualan retur
            $penjualanRetur = PenjualanRetur::find($penjualan_retur_id);
            
            //disini cari penjualan sebelumnya
            $cariPenjualan = Penjualan::where('kode_penjualan', $penjualanRetur->kode_penjualan)->first();

            //disini ambil data penjualan sebelumnya dari penjualan retur untuk update grand total penjualan
            $penjualanReturDetail = PenjualanReturDetail::where('penjualan_retur_id', $penjualan_retur_id)->get();
            foreach($penjualanReturDetail as $returDetail) {
                //disini cari penjualan detail sebelumnya
                $penjualanDetailSebelum = PenjualanDetail::where('penjualan_id', $cariPenjualan->id)
                    ->where('barang_id', $returDetail->barang_id)
                    ->first();
                if ($penjualanDetailSebelum) {
                    //hitung subtotal yang baru
                    $penjualanDetailSebelum->qty_konversi -= $returDetail->qty_konversi;
                    $penjualanDetailSebelum->subtotal -= $returDetail->total;
                    $penjualanDetailSebelum->save();
                }
            }

            //disini sum total penjualan sebelumnya untuk update grand total penjualan
            $totalPenjualanSebelum = PenjualanDetail::where('penjualan_id', $cariPenjualan->id)->sum('subtotal');

            //disini ambil semua data sebelumnya untuk update grand total penjualan
            $potonganSebelum = round($cariPenjualan->potongan,2);
            
            $pembulatanSekarang = 0;
            //disini update ulang pembulatannya
            $remainderPenjualanSebelum = ($totalPenjualanSebelum-$potonganSebelum) % 1000;
            if ($remainderPenjualanSebelum >= 1 && $remainderPenjualanSebelum <= 499) {
                $pembulatanSekarang = 500 - $remainderPenjualanSebelum;
            } else if ($remainderPenjualanSebelum >= 501) {
                $pembulatanSekarang = 1000 - $remainderPenjualanSebelum;
            }
            $pembulatanSekarang = round($pembulatanSekarang,2);
            $grandTotalUpdate = ($totalPenjualanSebelum-$potonganSebelum)+($pembulatanSekarang);
            $grandTotalUpdate = round($grandTotalUpdate);

            $cariPenjualan->total = $totalPenjualanSebelum;
            $cariPenjualan->pembulatan = $pembulatanSekarang;
            $cariPenjualan->grand_total = $grandTotalUpdate;
            $cariPenjualan->updated_by = auth()->id();
            $cariPenjualan->updated_at = now();
            $cariPenjualan->save();

        }

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

    public function print($id){
        $penjualan = Penjualan::with(['details.barang', 'details.satuan', 'pelanggan', 'pembayarans', 'creator'])->findOrFail($id);
        $profilToko = ProfilToko::first();
        return view('transaksi.penjualan.print', compact('penjualan', 'profilToko'));
    }
    

    public function getPaketBarang(Request $request)
    {
        // ============================================
        // AMBIL DATA DARI FRONTEND
        // ============================================


        $barangIds     = $request->barang_ids ?? [];
        $qtyMap        = $request->qty_map ?? [];
        $satuanIds     = $request->satuan_ids ?? [];
        $tipeHargaMap  = $request->tipe_harga_map ?? [];
        $barangSatuanMap = [];

        // ============================================
        // RESPONSE DEFAULT
        // ============================================

        $defaultResponse = [
            'success'         => true,
            'paket_details'   => [],
            'updated_items'   => [],
            'subtotal_akhir'  => 0,
            'is_paket'        => false
        ];

        // ============================================
        // VALIDASI BARANG
        // ============================================

        if (empty($barangIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada barang yang dipilih'
            ]);
        }

        // ============================================
        // AMBIL ID KATEGORI ROKOK
        // ============================================

        $kategoriRokok = \App\Models\Kategori::where('kode_kategori', 'ROKOK')
            ->first();

        // ============================================
        // AMBIL DATA BARANG
        // ============================================

        $barangs = \App\Models\Barang::select(
                'id',
                'nama_barang',
                'satuan_id',
                'harga_jual',
                'kategori_id'
            )
            ->whereIn('id', $barangIds)

            ->when($kategoriRokok, function ($query) use ($kategoriRokok) {

                $query->where('kategori_id', '!=', $kategoriRokok->id);

            })

            ->get()
            ->keyBy('id');

        // ============================================
        // VALIDASI SATUAN
        // ============================================

        foreach ($barangIds as $index => $barangId) {
            $barangSatuanMap[$barangId] = $satuanIds[$index] ?? null;
            $requestSatuanId = $satuanIds[$index] ?? null;

            $barang = $barangs->get($barangId);

            if (!$barang) {
                continue;
            }

            if ((int)$barang->satuan_id !== (int)$requestSatuanId) {
                continue;
            }
        }

        // ============================================
        // HASIL FINAL
        // ============================================

        $result = $defaultResponse;

        // ============================================
        // FILTER BARANG NON ROKOK
        // ============================================

        $barangIdsNonRokok = $barangs->pluck('id')->toArray();

        // ============================================
        // CEK APAKAH ADA PAKET
        // ============================================

        $paketBarangIds = \App\Models\PaketDetail::whereIn(
                'barang_id',
                $barangIdsNonRokok
            )
            ->pluck('barang_id')
            ->unique()
            ->toArray();

        if (!empty($paketBarangIds)) {
            $result['is_paket'] = true;
        }

        // ============================================
        // FUNCTION AMBIL HARGA BARANG
        // ============================================

        $getHargaBarang = function (
            $barangId,
            $satuanId,
            $tipeHarga
        ) use ($barangs) {

            $hargaBarang = \App\Models\HargaBarang::where('barang_id', $barangId)
                ->where('satuan_id', $satuanId)
                ->where('tipe_harga', $tipeHarga)
                ->where('status', 'aktif')
                ->first();

            if ($hargaBarang) {
                return $hargaBarang->harga;
            }

            $barang = $barangs->get($barangId);

            if ($barang) {
                return $barang->harga_jual;
            }

            return 0;
        };

        // ============================================
        // FUNCTION CEK PAKET
        // ============================================

        $cekPaket = function ($jenisPaket) use (
            $barangIdsNonRokok,
            $qtyMap,
            $barangs,
            &$result
        ) {

            $pakets = \App\Models\Paket::where('jenis', $jenisPaket)
                ->where('status', 'aktif')
                ->orderBy('harga')
                ->get();

            foreach ($pakets as $paket) {

                $paketBarangIds = \App\Models\PaketDetail::where('paket_id', $paket->id)
                    ->pluck('barang_id')
                    ->toArray();

                $matchingBarangs = array_intersect(
                    $barangIdsNonRokok,
                    $paketBarangIds
                );

                if (empty($matchingBarangs)) {
                    continue;
                }

                $totalQtyPaket = 0;

                foreach ($matchingBarangs as $barangId) {

                    $qty = $qtyMap[$barangId] ?? 0;

                    $totalQtyPaket += (int)$qty;
                }

                if ($totalQtyPaket < $paket->total_qty) {
                    continue;
                }

                $hargaSatuanPaket = floor(
                    $paket->harga / $paket->total_qty
                );

                $paketDetail = [
                    'paket_id'             => $paket->id,
                    'nama_paket'           => $paket->nama,
                    'harga_paket'          => $paket->harga,
                    'harga_satuan_paket'   => $hargaSatuanPaket,
                    'total_qty_paket'      => $paket->total_qty,
                    'jenis_paket'          => $paket->jenis,
                    'items'                => []
                ];

                foreach ($matchingBarangs as $barangId) {

                    $qty = $qtyMap[$barangId] ?? 0;

                    if ($qty <= 0) {
                        continue;
                    }

                    $barang = $barangs->get($barangId);

                    if (!$barang) {
                        continue;
                    }

                    $subtotal = $qty * $hargaSatuanPaket;
                    $subtotal = round($subtotal, 2);

                    $paketDetail['items'][] = [
                        'barang_id'               => $barangId,
                        'barang_nama'             => $barang->nama_barang,
                        'qty'                     => $qty,
                        'harga_setelah_paket'     => $hargaSatuanPaket,
                        'subtotal_setelah_paket'  => $subtotal
                    ];

                    $result['updated_items'][] = [
                        'barang_id'     => $barangId,
                        'harga_baru'    => round($hargaSatuanPaket, 2),
                        'subtotal_baru' => round($subtotal, 2),
                        'qty'           => round($qty, 2)
                    ];
                }

                if (!empty($paketDetail['items'])) {

                    $result['paket_details'][] = $paketDetail;
                    $result['is_paket'] = true;

                    foreach ($paketDetail['items'] as $item) {

                        $result['subtotal_akhir'] +=
                            $item['subtotal_setelah_paket'];
                    }

                    return true;
                }
            }

            return false;
        };

        // ============================================
        // CEK PAKET TIDAK
        // ============================================

        $foundPaket = $cekPaket('tidak');

        // ============================================
        // CEK PAKET CAMPUR
        // ============================================

        if (!$foundPaket) {
            $cekPaket('campur');
        }

        // ============================================
        // AMBIL BARANG YANG SUDAH MASUK PAKET
        // ============================================

        $barangYangSudahDipaketkan = [];

        foreach ($result['updated_items'] as $item) {

            $barangYangSudahDipaketkan[] = $item['barang_id'];
        }

        // ============================================
        // HITUNG BARANG YANG TIDAK MASUK PAKET
        // ============================================

        foreach ($barangIdsNonRokok as $barangId) {

            if (in_array($barangId, $barangYangSudahDipaketkan)) {
                continue;
            }

            $barang = $barangs->get($barangId);

            if (!$barang) {
                continue;
            }

            $qty = $qtyMap[$barangId] ?? 0;

            $satuanId = $barangSatuanMap[$barangId] ?? null;

            $tipeHarga = $tipeHargaMap[$barangId] ?? null;

            $harga = $getHargaBarang(
                $barangId,
                $satuanId,
                $tipeHarga
            );

            $subtotal = $qty * $harga;

            $result['subtotal_akhir'] += $subtotal;

            $result['updated_items'][] = [
                'barang_id'     => $barangId,
                'barang_nama'   => $barang->nama_barang,
                'qty'           => round($qty, 2),
                'harga_baru'    => round($harga, 2),
                'subtotal_baru' => round($subtotal, 2)
            ];
        }

        // ============================================
        // RESPONSE
        // ============================================

        return response()->json($result);


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

    /**
     * Data endpoint for index
     */
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

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $penjualan = Penjualan::with(['details.barang', 'details.satuan', 'pelanggan', 'pembayarans'])->findOrFail($id);
        return view('transaksi.penjualan.show', compact('penjualan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Implement edit form if needed
        return view('transaksi.penjualan.edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Implement update logic if needed
        return response()->json(['success' => false, 'message' => 'Method not implemented']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $penjualan = Penjualan::findOrFail($id);
            $penjualan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Penjualan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status of the specified resource.
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $penjualan = Penjualan::findOrFail($id);
            $penjualan->status = $request->status;
            $penjualan->save();

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX endpoint for barang autocomplete
     */
    public function autocompleteBarang(Request $request)
    {
        $term = $request->get('term', '');
        $barangs = Barang::where('nama_barang', 'like', "%{$term}%")
            ->orWhere('kode_barang', 'like', "%{$term}%")
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $barangs
        ]);
    }

    /**
     * AJAX endpoint to get satuan options for a barang
     */
    public function getSatuanByBarang($barangId)
    {
        try {
            $satuans = HargaBarang::where('barang_id', $barangId)
                ->with('satuan')
                ->where('status', 'aktif')
                ->get()
                ->pluck('satuan')
                ->unique();

            return response()->json([
                'success' => true,
                'data' => $satuans
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX endpoint to get available tipe_harga for a barang + satuan
     */
    public function getTipeHargaByBarangSatuan($barangId, $satuanId)
    {
        try {
            $tipeHargas = HargaBarang::where('barang_id', $barangId)
                ->where('satuan_id', $satuanId)
                ->where('status', 'aktif')
                ->pluck('tipe_harga')
                ->unique()
                ->values()
                ->toArray();

            // If no specific harga_barang records, default to ecer
            if (empty($tipeHargas)) {
                $tipeHargas = ['ecer'];
            }

            return response()->json([
                'success' => true,
                'data' => $tipeHargas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX endpoint to get harga for barang + satuan + tipe
     */
    public function getHargaByBarangSatuan($barangId, $satuanId, Request $request)
    {
        $tipe = $request->get('tipe', 'ecer');
        $pelangganId = $request->get('pelanggan_id');

        try {
            $hargaBarang = HargaBarang::where('barang_id', $barangId)
                ->where('satuan_id', $satuanId)
                ->where('tipe_harga', $tipe)
                ->where('status', 'aktif')
                ->first();

            if (!$hargaBarang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Harga tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'harga' => round($hargaBarang->harga),
                    'barang_id' => $hargaBarang->barang_id,
                    'satuan_id' => $hargaBarang->satuan_id,
                    'tipe_harga' => $hargaBarang->tipe_harga
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX endpoint to get harga for barang + satuan (defaulting to ecer)
     */
    public function getHargaByBarangSatuanDefault($barangId, $satuanId)
    {
        try {
            $hargaBarang = HargaBarang::where('barang_id', $barangId)
                ->where('satuan_id', $satuanId)
                ->where('tipe_harga', 'ecer')
                ->where('status', 'aktif')
                ->first();

            if (!$hargaBarang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Harga tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'harga' => round($hargaBarang->harga),
                    'barang_id' => $hargaBarang->barang_id,
                    'satuan_id' => $hargaBarang->satuan_id,
                    'tipe_harga' => $hargaBarang->tipe_harga
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX endpoint to get harga_barang info for a barang
     */
    public function getHargaBarangInfo($barangId)
    {
        try {
            $hargaBarang = HargaBarang::where('barang_id', $barangId)
                ->where('status', 'aktif')
                ->with(['satuan'])
                ->get()
                ->map(function($harga) {
                    return [
                        'satuan_id' => $harga->satuan_id,
                        'nama_satuan' => $harga->satuan->nama_satuan ?? 'Satuan',
                        'tipe_harga' => $harga->tipe_harga,
                        'harga' => $harga->harga
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $hargaBarang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getSatuan($satuanId) {
        $satuan = Satuan::find($satuanId);
        
        return response()->json([
            'success'   => true,
            'data'      => $satuan
        ]);
    }

    /**
     * AJAX endpoint to calculate subtotal and pembulatan for current transaction details
     */
    public function calculateSubtotal(Request $request)
    {
        try {
            $details = $request->details ?? [];
            $subTotal = 0;

            foreach ($details as $detail) {
                $subTotal += $detail['subtotal'] ?? 0;
            }

            $subTotal = round($subTotal, 2);

            $remainder = $subTotal % 1000;
            $pembulatan = 0;

            if ($remainder >= 1 && $remainder <= 499) {
                $pembulatan = 500 - $remainder;
            } else if ($remainder >= 501) {
                $pembulatan = 1000 - $remainder;
            }
            $pembulatan = round($pembulatan, 2);
            $grandTotal = $subTotal + $pembulatan;
            $grandTotal = round($grandTotal);

            return response()->json([
                'success' => true,
                'data' => [
                    'subtotal' => $subTotal,
                    'pembulatan' => $pembulatan,
                    'grand_total' => $grandTotal
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
