<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanStokMinimumBarangJenisSupplierController extends Controller
{
    public function index()
    {
        return view('laporan.min-stok-jenis-supp');
    }

    public function data(Request $request)
    {
        $supplierId = $request->supplier_id;

        $data = DB::table('jenis_barang as jb')
            ->join('barang as b', 'b.id', '=', 'jb.barang_id')
            ->leftJoin('stok_minimum as sm', 'sm.barang_id', '=', 'b.id')

            ->leftJoin('satuan as s_min', 's_min.id', '=', 'sm.satuan_id')
            ->leftJoin('satuan as s_kecil', 's_kecil.id', '=', 'sm.satuan_terkecil_id')

            ->where('jb.supplier_id', $supplierId)

            ->select(
                'b.id as barang_id',
                'b.kode_barang',
                'b.nama_barang',
                'b.stok',

                'sm.jumlah_minimum',
                'sm.jumlah_satuan_terkecil',

                's_min.nama_satuan as satuan_minimum',
                's_kecil.nama_satuan as satuan_stok'
            )

            ->orderBy('b.nama_barang')
            ->get();

        $result = $data->map(function ($item) {

            $jumlahMinimum = $item->jumlah_minimum ?? 0;
            $jumlahSatuanTerkecil = $item->jumlah_satuan_terkecil ?? 0;

            $satuanMinimum = $item->satuan_minimum ?? '-';
            $satuanStok = $item->satuan_stok ?? '-';

            $konversi = 1;

            if ($jumlahMinimum > 0 && $jumlahSatuanTerkecil > 0) {
                $konversi = $jumlahSatuanTerkecil / $jumlahMinimum;
            }

            $kekurangan = max(
                0,
                $jumlahSatuanTerkecil - $item->stok
            );

            $kurangDalamSatuanMinimum = 0;

            if ($konversi > 0) {
                $kurangDalamSatuanMinimum = ceil(
                    $kekurangan / $konversi
                );
            }

            $isKurang = false;

            if ($jumlahSatuanTerkecil > 0) {
                $isKurang = $item->stok < $jumlahSatuanTerkecil;
            }

            return [

                'barang_id' => $item->barang_id,
                'kode_barang' => $item->kode_barang,
                'nama_barang' => $item->nama_barang,

                'stok' => $item->stok,

                'stok_text' =>
                    number_format($item->stok, 0, ',', '.') .
                    ' ' .
                    $satuanStok,

                'minimum' => $jumlahMinimum,

                'minimum_text' =>
                    $jumlahMinimum > 0
                        ? number_format($jumlahMinimum, 0, ',', '.') .
                            ' ' .
                            $satuanMinimum
                        : '-',

                'kekurangan' => $kekurangan,

                'kekurangan_text' =>
                    $jumlahMinimum > 0
                        ? $kurangDalamSatuanMinimum .
                            ' ' .
                            $satuanMinimum
                        : '-',

                'status' => $isKurang ? 'KURANG' : 'AMAN',

                'badge' => $isKurang
                    ? '<span class="badge bg-danger">KURANG</span>'
                    : '<span class="badge bg-success">AMAN</span>',

                'row_class' => $isKurang
                    ? 'table-danger'
                    : 'table-success',

                'satuan_stok' => $satuanStok,
                'satuan_minimum' => $satuanMinimum
            ];
        });

        return response()->json([
            'success' => true,
            'total_item' => $result->count(),
            'data' => $result
        ]);
    }

    public function exportPdf(Request $request)
    {
        $supplierId = $request->supplier_id;

        $data = DB::table('jenis_barang as jb')
            ->join('barang as b', 'b.id', '=', 'jb.barang_id')
            ->leftJoin('stok_minimum as sm', 'sm.barang_id', '=', 'b.id')

            ->leftJoin('satuan as s_min', 's_min.id', '=', 'sm.satuan_id')
            ->leftJoin('satuan as s_kecil', 's_kecil.id', '=', 'sm.satuan_terkecil_id')

            ->leftJoin('supplier as s', 's.id', '=', 'jb.supplier_id')

            ->where('jb.supplier_id', $supplierId)

            ->select(
                'b.kode_barang',
                'b.nama_barang',
                'b.stok',

                'sm.jumlah_minimum',
                'sm.jumlah_satuan_terkecil',

                's_min.nama_satuan as satuan_minimum',
                's_kecil.nama_satuan as satuan_stok',

                's.nama_supplier'
            )
            ->orderBy('b.nama_barang')
            ->get();

        $result = $data->map(function ($item) {

            $jumlahMinimum = $item->jumlah_minimum ?? 0;
            $jumlahSatuanTerkecil = $item->jumlah_satuan_terkecil ?? 0;

            $satuanMinimum = $item->satuan_minimum ?? '-';
            $satuanStok = $item->satuan_stok ?? '-';

            $konversi = 1;

            if ($jumlahMinimum > 0 && $jumlahSatuanTerkecil > 0) {
                $konversi = $jumlahSatuanTerkecil / $jumlahMinimum;
            }

            $kekurangan = max(
                0,
                $jumlahSatuanTerkecil - $item->stok
            );

            $kurangDalamSatuanMinimum = 0;

            if ($konversi > 0) {
                $kurangDalamSatuanMinimum = ceil(
                    $kekurangan / $konversi
                );
            }

            $isKurang = false;

            if ($jumlahSatuanTerkecil > 0) {
                $isKurang = $item->stok < $jumlahSatuanTerkecil;
            }

            return [
                'kode_barang'      => $item->kode_barang,
                'nama_barang'      => $item->nama_barang,
                'stok_text'        => number_format($item->stok, 0, ',', '.') . ' ' . $satuanStok,
                'minimum_text'     => $jumlahMinimum > 0
                    ? number_format($jumlahMinimum, 0, ',', '.') . ' ' . $satuanMinimum
                    : '-',
                'kekurangan_text'  => $jumlahMinimum > 0
                    ? $kurangDalamSatuanMinimum . ' ' . $satuanMinimum
                    : '-',
                'status'           => $isKurang ? 'KURANG' : 'AMAN',
                'warna'            => $isKurang ? '#f8d7da' : '#d1e7dd'
            ];
        });

        $supplier = DB::table('supplier')
            ->where('id', $supplierId)
            ->first();

        $pdf = PDF::loadView(
            'laporan.stok_minimum_barang_jenis_supplier_pdf',
            [
                'supplier' => $supplier,
                'data' => $result
            ]
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'laporan-stok-minimum-' . date('YmdHis') . '.pdf'
        );
    }
}
