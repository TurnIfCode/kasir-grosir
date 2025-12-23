<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\HargaBarang;
use App\Models\Kas;
use App\Models\KasSaldo;
use App\Models\KasSaldoTransaksi;
use App\Models\Kategori;
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
            $this->updateStokDanHargaBarang($detail['barang_id'], $detail['satuan_id'], round($detail['qty'],2), round($detail['harga_beli'],2));
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
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    
                    $query->where(function($q) use ($search) {
                        $q->where('kode_pembelian', 'LIKE', '%' . $search . '%')
                          ->orWhere('tanggal_pembelian', 'LIKE', '%' . $search . '%')
                          ->orWhere('total', 'LIKE', '%' . $search . '%')
                          ->orWhere('status', 'LIKE', '%' . $search . '%')
                          ->orWhereHas('supplier', function($sq) use ($search) {
                              $sq->where('nama_supplier', 'LIKE', '%' . $search . '%');
                          });
                    });
                }
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
        $nilaiKonversi = 0;
        $hargaBeliDasar = 0;
        //disini ambil dulu Konversi Satuannya
        $konversiSatuan = KonversiSatuan::where('barang_id', $barangId)
                            ->where('satuan_konversi_id', $satuanId)
                            ->first();
        
        if ($konversiSatuan) {
            $nilaiKonversi = round($konversiSatuan->nilai_konversi,2);
            $hargaBeliDasar = round($hargaBeli/$nilaiKonversi);
        } else {
            $nilaiKonversi = 1; // Default nilai konversi jika tidak ada data konversi
            $hargaBeliDasar = $hargaBeli;
        }

        
        $barang         = Barang::find($barangId);
        
        $stokSebelum    = round($barang->stok,2);
        $stokBeli       = $qty*$nilaiKonversi;
        $stokBeli       = round($stokBeli,2);
        $stokBaru       = $stokSebelum+$stokBeli;
        $stokBaru       = round($stokBaru,2);

        //disini ambil harga beli sebelum barang sebelum
        $hargaBeliSebelum = round($barang->harga_beli);
        
        //update data barang
        $barang->stok       = $stokBaru;
        $barang->harga_beli = $hargaBeliDasar;
        $barang->updated_by = auth()->id();
        $barang->updated_at = now();
        $barang->save();

        
        //disini ambil kategorinya dulu
        $kategori = Kategori::where('id', $barang->kategori_id)->first();
        $kodeKategori = $kategori ? $kategori->kode_kategori : '';

        //disini ambil dulu semua data konversi_satuan berdasarkan barang_id dan update semua harga belinya
        $hargaBeli = 0;
        $nilaiKonversi = 0;
        $dataKonversi = KonversiSatuan::where('barang_id',$barangId)->get();
        if (count($dataKonversi) > 0) {
            foreach ($dataKonversi as $dk) {
                $nilaiKonversi = round($dk->nilai_konversi);
                //disini hitung semua harga modal
                $hargaBeli = $hargaBeliDasar*$nilaiKonversi;

                //update data
                $dk->harga_beli = $hargaBeli;
                $dk->updated_at = now();
                $dk->save();
            }


            //disini ambil data dari harga jual
            $dataHargaBarang = HargaBarang::where('barang_id', $barangId)->get();
            if (count($dataHargaBarang) > 0) {
                $hargaBeli = 0;
                $hargaJual = 0;
                $hargaJualSebelum = 0;
                $pembulatanSelisih = 0;
                $hargaBeliBaru = 0;
                $hitungHargaBeliSebelum = 0;
                $selisihHargaBeli = 0;
                $hitungHargaBeliBaru = 0;
                $remainderSelisih = 0;
                foreach ($dataHargaBarang as $dataHarga) {
                    //disini ambil konversinya
                    $cekKonversi = KonversiSatuan::where('barang_id', $barangId)->where('satuan_konversi_id', $dataHarga->satuan_id)->first();
                    
                    if (strtolower($kodeKategori) == 'rokok') {
                        if (strtolower($barang->jenis) == 'legal') {
                            if ($cekKonversi) {
                                if (strtolower($dataHarga->tipe_harga) == 'grosir') {
                                    $hargaBeli = round($cekKonversi->harga_beli);

                                    $hitungHarga = floor($hargaBeli/1000)*1000;
                                    
                                    $hargaJual = round($hitungHarga+2000);

                                    $dataHarga->harga = $hargaJual;
                                    $dataHarga->updated_at = now();
                                    $dataHarga->save();
                                } else if (strtolower($dataHarga->tipe_harga) == 'modal') {
                                    $hargaBeli = round($cekKonversi->harga_beli);
                                    
                                    $hargaJual = round($hargaBeli);

                                    $dataHarga->harga = $hargaJual;
                                    $dataHarga->updated_at = now();
                                    $dataHarga->save();
                                } else if (strtolower($dataHarga->tipe_harga) == 'hubuan') {
                                    $hargaBeli = round($cekKonversi->harga_beli);

                                    $hargaBeli = round($hargaBeli+3000);

                                    $dataHarga->harga = $hargaJual;
                                    $dataHarga->updated_at = now();
                                    $dataHarga->save();
                                }
                            } else {
                                if (strtolower($dataHarga->tipe) == 'ecer') {
                                    $hitungHarga = floor($hargaBeliDasar/1000)*1000;

                                    $hargaJual = $hitungHarga+2000;
                                    
                                    $dataHarga->harga = $hargaJual;
                                    $dataHarga->updated_at = now();
                                    $dataHarga->save();

                                } else if (strtolower($dataHarga->tipe_harga) == 'grosir') {
                                    //disini cek satuan
                                    $cekSatuan = Satuan::find($barang->satuan_id);
                                    if ($cekSatuan && isset($cekSatuan->kode_satuan)) {
                                        if (strtolower($cekSatuan->kode_satuan) == 'klg') {
                                            $hitungHarga = floor($hargaBeliDasar/1000)*1000;

                                            $hargaJual = round($hitungHarga+3000);
                                            
                                            $dataHarga->harga = $hargaJual;
                                            $dataHarga->updated_at = now();
                                            $dataHarga->save();
                                        } else {
                                            $hargaJual = round($hargaBeliDasar);
                                            
                                            $dataHarga->harga = $hargaJual;
                                            $dataHarga->updated_at = now();
                                            $dataHarga->save();
                                        }
                                    }
                                } else if (strtolower($dataHarga->tipe_harga) == 'modal') {
                                    $hargaJual = round($hargaBeliDasar);
                                            
                                    $dataHarga->harga = $hargaJual;
                                    $dataHarga->updated_at = now();
                                    $dataHarga->save();
                                }
                            }
                        }
                    } else if (strtolower($kodeKategori) == 'tbg') {
                        if ($cekKonversi) {
                            //disini ambil harga jual sebelum
                            $hargaJualSebelum = $dataHarga->harga;
                            $hargaJualSebelum = round($hargaJualSebelum,2);

                            //disini ambil harga_beli sebelum
                            $nilaiKonversi = round($cekKonversi->nilai_konversi,2);
                            
                            $hitungHargaBeliSebelum = $hargaBeliSebelum*$nilaiKonversi;
                            $hitungHargaBeliSebelum = round($hitungHargaBeliSebelum,2);

                            //hitung harga_beli baru
                            $hitungHargaBeliBaru = $hargaBeliDasar*$nilaiKonversi;
                            $hitungHargaBeliBaru = round($hitungHargaBeliBaru);

                            //hitung selisih harga beli
                            $selisihHargaBeli = $hitungHargaBeliBaru-$hitungHargaBeliSebelum;
                            $selisihHargaBeli = round($selisihHargaBeli,2);

                            //hitung pembulatannya
                            $remainderSelisih = round($selisihHargaBeli % 1000);
                            if ($remainderSelisih < 0) {
                                $pembulatanSelisih = 1000+$remainderSelisih;
                            } else if ($pembulatanSelisih > 0) {
                                $pembulatanSelisih = 1000-$remainderSelisih;
                            }

                            $hargaJual = $hargaJualSebelum+($selisihHargaBeli+($pembulatanSelisih));
                            $hargaJual = round($hargaJual);

                            $hargaJual = floor($hargaJual/1000)*1000;

                            $dataHarga->harga = $hargaJual;
                            $dataHarga->updated_at = now();
                            $dataHarga->save();
                        } else if (!$cekKonversi) {
                            //disini ambil data konversi dengan nilai_konversi paling tinggi
                            $konversiTertinggi = KonversiSatuan::where('barang_id', $barangId)
                                                ->where('satuan_dasar_id', $dataHarga->satuan_id)
                                                ->orderBy('nilai_konversi', 'asc')
                                                ->first();
                            
                            //disini ambil harga jual tertinggi
                            $hargaTertinggi = HargaBarang::where('barang_id', $barangId)
                                                ->where('satuan_id', '!=', $barang->satuan_id)
                                                ->orderBy('harga', 'asc')
                                                ->first();

                            //hitung berapa harga jualnya
                            $hargaJual = round($hargaTertinggi->harga,2)/round($konversiTertinggi->nilai_konversi,2);
                            $hargaJual = round($hargaJual,2);

                            if (strtolower($barang->kode_barang) == 'tbg-wijen') {
                                $hargaJual = ceil($hargaJual/1000)*1000;
                            }

                            $dataHarga->harga = $hargaJual;
                            $dataHarga->updated_at = now();
                            $dataHarga->save();

                            
                        }
                    } else if (strtolower($kodeKategori) == 'sembako') {
                        if (strtolower($barang->kode_barang) == 'telayam') {
                            if ($cekKonversi) {
                                $hargaJual = round($cekKonversi->harga_beli)+3000;

                                $dataHarga->harga = $hargaJual;
                                $dataHarga->updated_at = now();
                                $dataHarga->save();
                            }
                        }
                    }
                }
            }
        } else {
            $selisihHargaBeli = 0;
            $pembulatanSelisih = 0;
            $remainderSelisih = 0;
            $hargaJualSebelum = 0;
            $dataHargaBarang = HargaBarang::where('barang_id', $barangId)->get();
            foreach($dataHargaBarang as $data) {
                //cek kode kategorinya
                if (strtolower($kodeKategori) == 'tbg') {
                    
                    //hitung harga beli
                    $selisihHargaBeli = $hargaBeliDasar-$hargaBeliSebelum;
                    $selisihHargaBeli = round($selisihHargaBeli,2);

                    //ambil harga jual sebelum
                    $hargaJualSebelum = $data->harga;
                    $hargaJualSebelum = round($hargaJualSebelum,2);


                    //hitung pembulatannya
                    $remainderSelisih = round($selisihHargaBeli % 1000);
                    if ($remainderSelisih < 0) {
                        $pembulatanSelisih = 1000+$remainderSelisih;
                    } else if ($pembulatanSelisih > 0) {
                        $pembulatanSelisih = 1000-$remainderSelisih;
                    }

                    $hargaJual = $hargaJualSebelum+($selisihHargaBeli+($pembulatanSelisih));
                    $hargaJual = round($hargaJual);

                    $hargaJual = floor($hargaJual/1000)*1000;

                    $data->harga = $hargaJual;
                    $data->updated_at = now();
                    $data->save();
                    
                } else if ($kodeKategori == 'sembako') {
                    //hitung harga beli
                    $selisihHargaBeli = $hargaBeliDasar-$hargaBeliSebelum;
                    $selisihHargaBeli = round($selisihHargaBeli,2);

                    //ambil harga jual sebelum
                    $hargaJualSebelum = $data->harga;
                    $hargaJualSebelum = round($hargaJualSebelum,2);


                    //hitung pembulatannya
                    $remainderSelisih = round($selisihHargaBeli % 1000);
                    if ($remainderSelisih < 0) {
                        $pembulatanSelisih = 1000+$remainderSelisih;
                    } else if ($pembulatanSelisih > 0) {
                        $pembulatanSelisih = 1000-$remainderSelisih;
                    }

                    $hargaJual = $hargaJualSebelum+($selisihHargaBeli+($pembulatanSelisih));
                    $hargaJual = round($hargaJual);

                    $hargaJual = floor($hargaJual/1000)*1000;

                    $data->harga = $hargaJual;
                    $data->updated_at = now();
                    $data->save();
                }
                    
            }
            
        }

        //disini update harga jual barang utamakan harga jual ecer
        $hargaEcer = HargaBarang::where('barang_id', $barangId)
                    ->where('satuan_id', $barang->satuan_id)
                    ->where('tipe_harga', 'ecer')
                    ->orderBy('harga', 'asc')
                    ->first();
        if ($hargaEcer) {
            $barang->harga_jual = round($hargaEcer->harga);
            $barang->updated_by = auth()->id();
            $barang->updated_at = now();
            $barang->save();
        } else {
            $hargaGrosir = HargaBarang::where('barang_id', $barangId)
            ->where('satuan_id', $barang->satuan_id)
            ->where('tipe_harga', 'grosir')
            ->orderBy('harga', 'asc')
            ->first();
            if ($hargaGrosir) {
                $barang->harga_jual = round($hargaGrosir->harga);
                $barang->updated_by = auth()->id();
                $barang->updated_at = now();
                $barang->save();
            }
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

    public function destroy($id)
    {
        try {
            $pembelian = Pembelian::findOrFail($id);

            // Hanya bisa hapus pembelian dengan status 'draft'
            if ($pembelian->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembelian yang sudah selesai atau dibatalkan tidak dapat dihapus'
                ], 400);
            }

            // Hapus semua detail pembelian terkait
            PembelianDetail::where('pembelian_id', $id)->delete();

            // Hapus semua pembayaran pembelian terkait
            PembelianPembayaran::where('pembelian_id', $id)->delete();

            // Hapus pembelian utama
            $pembelian->delete();

            // Log aktivitas
            $log = new Log();
            $log->keterangan = 'Hapus Pembelian : ' . $pembelian->kode_pembelian;
            $log->created_by = auth()->id();
            $log->created_at = now();
            $log->save();

            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus pembelian: ' . $e->getMessage()
            ], 500);
        }
    }
}
