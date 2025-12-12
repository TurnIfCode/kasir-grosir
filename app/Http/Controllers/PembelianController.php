<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kas;
use App\Models\KasSaldo;
use App\Models\KasSaldoTransaksi;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\PembelianPembayaran;
use App\Models\Satuan;
use App\Models\Supplier;
use App\Models\Log;
use App\Models\KonversiSatuan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PembelianController extends Controller
{
    public function index()
    {
        return view('transaksi.pembelian.index');
    }

    public function create()
    {
        $kasSaldo = KasSaldo::all();
        return view('transaksi.pembelian.create', compact('kasSaldo'));
    }

    public function store(Request $request) {
        $kode_pembelian     = $this->generateKodePembelian();
        $tanggal_pembelian  = trim($request->tanggal_pembelian);
        $diskon             = trim($request->diskon);
        $ppn                = trim($request->ppn);
        $catatan            = trim($request->catatan);
        $supplier_id        = trim($request->supplier_id);
        $tanggal_pembelian  = date('Y-m-d', strtotime($tanggal_pembelian));

        //cek supplier terdatar atau tidak
        $cekSupplier = Supplier::where('id',$supplier_id)->where('status','aktif')->count();
        if ($cekSupplier == 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Supplier tidak ditemukan.',
                'form'      => 'supplier_nama'
            ]);
        }

        //disini insert ke database
        $pembelian                      = new Pembelian();
        $pembelian->kode_pembelian      = $kode_pembelian;
        $pembelian->tanggal_pembelian   = $tanggal_pembelian;
        $pembelian->subtotal            = 0;
        $pembelian->diskon              = $diskon;
        $pembelian->ppn                 = $ppn;
        $pembelian->total               = 0;
        $pembelian->catatan             = $catatan;
        $pembelian->supplier_id         = $supplier_id;
        $pembelian->status              = 'draft';
        $pembelian->created_by          = auth()->id();
        $pembelian->created_at          = now();
        $pembelian->updated_by          = auth()->id();
        $pembelian->updated_at          = now();
        $pembelian->save();

        //insert ke lognya
        $log = new Log();
        $log->keterangan = 'Tambah Pembelian : '.$pembelian->kode_pembelian.' Tanggal : '.$pembelian->tanggal_pembelian.'';
        $log->created_by = auth()->id();
        $log->created_at = now();
        $log->save();

        return response()->json([
            'success' => true,
            'message'   => 'Pembelian berhasil ditambah',
            'pembelian_id'  => $pembelian->id
        ]);
    }

    public function savedtl(Request $request) {
        $pembelian_id   = trim($request->pembelian_id);
        $barang_id      = trim($request->barang_id);
        $satuan_id      = trim($request->satuan_id);
        $qty            = trim($request->qty);
        $harga_beli     = trim($request->harga_beli);

        //cek disini pembelian
        $cekSelesai = Pembelian::where('id', $pembelian_id)->where('status', 'selesai')->count();
        if ($cekSelesai>0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Pembelian sudah selesai'
            ]);
        }

        $cekBatal = Pembelian::where('id', $pembelian_id)->where('status', 'batal')->count();
        if ($cekBatal > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Pembelian sudah dibatalkan'
            ]);
        }

        //cek barang
        $cekBarang = Barang::where('id', $barang_id)->where('status', 'aktif')->count();
        if ($cekBarang == 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Barang tidak aktif atau tidak terdaftar'
            ]);
        }

        //cek satuan
        $cekSatuan = Satuan::where('id', $satuan_id)->where('status', 'aktif')->count();
        if ($cekSatuan == 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Satuan tidak aktif atau tidak terdaftar'
            ]);
        }

        $qty = round($qty);
        $harga_beli = round($harga_beli);

        if ($qty <= 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Qty harus lebih besar dari 0'
            ]);
        }

        if ($harga_beli <= 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Harga beli harus lebih besar dari 0'
            ]);
        }

        $subtotal = $qty*$harga_beli;
        $subtotal = round($subtotal);

        $pembelianDetail = new PembelianDetail();
        $pembelianDetail->pembelian_id = $pembelian_id;
        $pembelianDetail->barang_id = $barang_id;
        $pembelianDetail->satuan_id = $satuan_id;
        $pembelianDetail->qty = $qty;
        $pembelianDetail->harga_beli = $harga_beli;
        $pembelianDetail->subtotal = $subtotal;
        $pembelianDetail->created_by = auth()->id();
        $pembelianDetail->created_at = now();
        $pembelianDetail->updated_by = auth()->id();
        $pembelianDetail->updated_at = now();
        $pembelianDetail->save();

        //disini ambil barangnya
        $barang = Barang::find($barang_id);

        //disini ambil satuannya
        $satuan = Satuan::find($satuan_id);

        //disini ambil pembelian
        $pembelian = Pembelian::find($pembelian_id);

        $keteranganLog = 'Tambah barang di pembelian '.$pembelian->kode_pembelian.' Barang : '.$barang->nama_barang.' | Qty : '.$qty.' | Satuan : '.$satuan->nama_satuan.' | Harga beli : '.$harga_beli.' | Subtotal Harga : '.$subtotal;

        $log = new Log();
        $log->keterangan = $keteranganLog;
        $log->created_by = auth()->id();
        $log->created_at = now();
        $log->save();

        //disini sum semua harga subtotal pembelian detail
        $sumSubtotal = PembelianDetail::where('pembelian_id', $pembelian_id)->sum('subtotal');
        $sumSubtotal = round($sumSubtotal);

        //disini update pembelian
        $diskon = round($pembelian->diskon);
        $ppn = round($pembelian->ppn);

        $total = $sumSubtotal+$ppn+$diskon;
        $total = round($total);

        $pembelian->subtotal = $sumSubtotal;
        $pembelian->total = $total;
        $pembelian->updated_by = auth()->id();
        $pembelian->updated_at = now();
        $pembelian->save();

        return response()->json([
            'success'   => true,
            'message'   => 'Berhasil',
            'data'      => $pembelian
        ]);
    }

    public function getDtl($pembelian_id) {
        $pembelianDetail = PembelianDetail::where('pembelian_id', $pembelian_id)
        ->with('barang', 'satuan')
        ->orderBy('id', 'desc')
        ->get();

        return response()->json([
            'success'   => true,
            'datas'     => $pembelianDetail
        ]);

    }

    public function deleteDtl($pembelian_id, $pembelian_detail_id) {
        $data = PembelianDetail::find($pembelian_detail_id);
        $data->delete();

        //ambil pembelian
        $pembelian = Pembelian::find($pembelian_id);

        //disini sum semua subtotal
        $sumSubtotal = PembelianDetail::where('pembelian_id', $pembelian_id)->sum('subtotal');
        $sumSubtotal = round($sumSubtotal);

        $diskon = round($pembelian->diskon);
        $ppn = round($pembelian->ppn);

        $total = $sumSubtotal+$ppn+$diskon;
        $total = round($total);

        $pembelian->subtotal = $sumSubtotal;
        $pembelian->total = $total;
        $pembelian->updated_by = auth()->id();
        $pembelian->updated_at = now();
        $pembelian->save();

        return response()->json([
            'success'   => true,
            'message'   => 'Berhasil',
            'data'      => $pembelian
        ]);
    }

    public function cekDtl($pembelian_id) {
        $count = PembelianDetail::where('pembelian_id',$pembelian_id)->count();

        if ($count == 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Belum ada barang detail'
            ]);
        }
        
        return response()->json([
            'success'   => true,
            'message'   => 'Ok'
        ]);
    }

    public function saveAll(Request $request, $pembelian_id) {
        $metode_pembayaran  = trim($request->metode_pembayaran);
        $nominal_pembayaran = trim($request->nominal_pembayaran);
        $nominal_pembayaran = round($nominal_pembayaran);
        $keterangan_pembayaran = trim($request->keterangan_pembayaran);

        //disini ambil data pembelian
        $pembelian = Pembelian::find($pembelian_id);
        $total = round($pembelian->total);

        if ($nominal_pembayaran < $total) {
            return response()->json([
                'success' => false,
                'meesage'   => 'Nominal pembayaran kurang',
                'form'      => 'nominal_pembayaran'
            ]);
        }

        //insert pembayaran detail
        $pembelianPembayaran = new PembelianPembayaran();
        $pembelianPembayaran->pembelian_id = $pembelian_id;
        $pembelianPembayaran->metode = $metode_pembayaran;
        $pembelianPembayaran->nominal = $nominal_pembayaran;
        $pembelianPembayaran->created_by = auth()->id();
        $pembelianPembayaran->created_at = now();
        $pembelianPembayaran->updated_by = auth()->id();
        $pembelianPembayaran->updated_at = now();
        $pembelianPembayaran->save();

        //update pembelian
        $pembelian->status = 'selesai';
        $pembelian->updated_by = auth()->id();
        $pembelian->updated_at = now();
        $pembelian->save();

        if ($metode_pembayaran == 'transfer') {
            //disini ambil dulu data kassaldo
            $kasSaldo = KasSaldo::where('kas', $keterangan_pembayaran)->first();
            if ($kasSaldo) {
                $saldo = round($kasSaldo->saldo);
                $sisaSaldo = $saldo-$nominal_pembayaran;
                $sisaSaldo = round($sisaSaldo);

                $now = date("Y-m-d H:i:s");
                $dateNow = date('Y-m-d', strtotime($now));

                //disini insert ke table kas
                $kas = new Kas();
                $kas->tanggal = $dateNow;
                $kas->tipe = 'keluar';
                $kas->sumber_kas = $kasSaldo->kas;
                $kas->kategori = 'Pembelian';
                $kas->nominal = $nominal_pembayaran;
                $kas->keterangan = 'Pembayaran Pembelian '.$pembelian->kode_pembelian.' Sebesar : Rp.'.number_format($nominal_pembayaran);
                $kas->user_id = auth()->id();
                $kas->created_by = auth()->id();
                $kas->created_at = now();
                $kas->updated_by = auth()->id();
                $kas->updated_at = now();
                $kas->save();

                //disini insert kas saldo transaksi
                $kasSaldoTransaksi = new KasSaldoTransaksi();
                $kasSaldoTransaksi->kas_saldo_id = $kasSaldo->id;
                $kasSaldoTransaksi->tipe = 'keluar';
                $kasSaldoTransaksi->saldo_awal = $saldo;
                $kasSaldoTransaksi->saldo_akhir = $sisaSaldo;
                $kasSaldoTransaksi->keterangan = 'Pembayaran Pembelian '.$pembelian->kode_pembelian.' Sebesar : Rp.'.number_format($nominal_pembayaran);
                $kasSaldoTransaksi->created_by = auth()->id();
                $kasSaldoTransaksi->created_at = now();
                $kasSaldoTransaksi->save();

                //update kassaldo
                $kasSaldo->saldo = $sisaSaldo;
                $kasSaldo->updated_by = auth()->id();
                $kasSaldo->updated_at = now();
                $kasSaldo->save();
            }
        }

        //disini ambil pembelian_detail
        $pembelianDetail = PembelianDetail::where('pembelian_id', $pembelian_id)->get();
        foreach ($pembelianDetail as $detail) {
            $this->updateStokDanHargaBarang($detail['barang_id'], $detail['satuan_id'], $detail['qty'], $detail['harga_beli']);
        }
        

        return response()->json([
            'success'   => true,
            'message'   => 'Pembelian berhasil',
            'data'      => $pembelian_id
        ]);
    }

    public function data(Request $request)
    {
        $query = Pembelian::with(['supplier']);

        // Filter berdasarkan parameter
        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('tanggal_dari') && $request->tanggal_dari) {
            $query->where('tanggal_pembelian', '>=', $request->tanggal_dari);
        }

        if ($request->has('tanggal_sampai') && $request->tanggal_sampai) {
            $query->where('tanggal_pembelian', '<=', $request->tanggal_sampai);
        }

        return \DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('tanggal_pembelian_formatted', function ($row) {
                return $row->tanggal_pembelian->format('d/m/Y');
            })
            ->addColumn('supplier_nama', function ($row) {
                return $row->supplier->nama_supplier ?? '-';
            })
            ->addColumn('total_formatted', function ($row) {
                return 'Rp ' . number_format($row->total, 0, ',', '.');
            })
            ->addColumn('aksi', function ($row) {
                return '<a href="' . route('pembelian.show', $row->id) . '" class="btn btn-info btn-sm" title="Lihat"><i class="fas fa-eye"></i></a> ' .
                       ($row->status === 'draft' ? '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>' : '');
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function edit($id)
    {
        $pembelian = Pembelian::with(['details.barang', 'details.satuan'])->findOrFail($id);

        // Cek jika status sudah selesai, tidak bisa diedit
        if ($pembelian->status === 'selesai') {
            return redirect()->route('pembelian.index')->with('error', 'Pembelian yang sudah selesai tidak dapat diedit');
        }

        $kasSaldo = \App\Models\KasSaldo::all();

        return view('transaksi.pembelian.edit', compact('pembelian', 'kasSaldo'));
    }

    public function show($id)
    {
        $pembelian = Pembelian::with(['supplier', 'details.barang', 'details.satuan'])->findOrFail($id);
        return view('transaksi.pembelian.show', compact('pembelian'));
    }

    public function updateStatus(Request $request, $id)
    {
        $pembelian = Pembelian::findOrFail($id);

        // Validasi status
        $request->validate([
            'status' => 'required|in:selesai,batal'
        ]);

        $newStatus = $request->status;

        // Cek jika status sudah selesai atau batal, tidak bisa diubah lagi
        if ($pembelian->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Status pembelian sudah tidak dapat diubah'
            ]);
        }

        DB::beginTransaction();
        try {
            if ($newStatus === 'selesai') {
                // Untuk status selesai, update stok dan harga barang
                foreach ($pembelian->details as $detail) {
                    $this->updateStokDanHargaBarang($detail->barang_id, $detail->satuan_id, $detail->qty, $detail->harga_beli);
                }
            } elseif ($newStatus === 'batal') {
                // Untuk status batal, tidak perlu melakukan apa-apa karena stok belum terupdate
            }

            // Update status pembelian
            $pembelian->update([
                'status' => $newStatus,
                'updated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Status pembelian berhasil diubah menjadi ' . ($newStatus === 'selesai' ? 'selesai' : 'batal')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function updateStokDanHargaBarang($barangId, $satuanId, $qty, $hargaBeli)
    {
        $konversi_pembelian = KonversiSatuan::where('barang_id', $barangId)
            ->where('satuan_konversi_id', $satuanId)
            ->first();

        $barang = Barang::find($barangId);
        $nilai_konversi = $konversi_pembelian ? $konversi_pembelian->nilai_konversi : 1;
        $harga_beli_dasar = $hargaBeli / $nilai_konversi;

        // Update harga dasar dan stok
        $barang->update([
            'stok' => $barang->stok + ($qty * $nilai_konversi),
            'harga_beli' => $harga_beli_dasar,
            'updated_by' => auth()->id(),
            'updated_at' => now()
        ]);

        // Update semua konversi satuan yang berkaitan
        $semua_konversi = KonversiSatuan::where('barang_id', $barangId)->get();
        foreach ($semua_konversi as $k) {
            $harga_konversi = $harga_beli_dasar * $k->nilai_konversi;
            $k->update([
                'harga_beli' => $harga_konversi,
                'updated_by' => auth()->id(),
                'updated_at' => now()
            ]);
        }
    }

    private function generateKodePembelian()
    {
        $tanggal = now()->format('Ymd');
        $lastPembelian = Pembelian::where('kode_pembelian', 'like', 'PB-' . $tanggal . '%')
            ->orderBy('kode_pembelian', 'desc')
            ->first();

        if ($lastPembelian) {
            $lastNumber = intval(substr($lastPembelian->kode_pembelian, -3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'PB-' . $tanggal . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
