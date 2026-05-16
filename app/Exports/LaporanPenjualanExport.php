<?php

namespace App\Exports;

use App\Models\Penjualan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;
use DB;

class LaporanPenjualanExport implements FromView
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $query = Penjualan::with(['pelanggan', 'details', 'creator'])
            ->leftJoin('users', 'penjualan.created_by', '=', 'users.id')
            ->select([
                'penjualan.id',
                'penjualan.kode_penjualan',
                'penjualan.tanggal_penjualan',
                'penjualan.pelanggan_id',
                'penjualan.total',
                'penjualan.diskon',
                'penjualan.ppn',
                'penjualan.pembulatan',
                'penjualan.grand_total',
                'penjualan.jenis_pembayaran',
                'penjualan.dibayar',
                'penjualan.kembalian',
                'penjualan.catatan',
                'penjualan.status',
                'penjualan.created_by',
                'penjualan.updated_by',
                'penjualan.created_at',
                'penjualan.updated_at',
                'users.name as kasir_name',
                DB::raw('ROUND(COALESCE(SUM(penjualan_detail.qty_konversi), 0)) as jumlah_item'),
                DB::raw('ROUND(COALESCE(SUM(penjualan_detail.qty_konversi * penjualan_detail.harga_beli), 0)) as total_hpp'),
                DB::raw('ROUND((penjualan.dibayar - penjualan.kembalian) - COALESCE(SUM(penjualan_detail.qty_konversi * penjualan_detail.harga_beli), 0)) as laba')
            ])
            ->leftJoin('penjualan_detail', 'penjualan.id', '=', 'penjualan_detail.penjualan_id')
            ->groupBy('penjualan.id', 'penjualan.kode_penjualan', 'penjualan.tanggal_penjualan', 'penjualan.pelanggan_id', 'penjualan.total', 'penjualan.diskon', 'penjualan.ppn', 'penjualan.pembulatan', 'penjualan.grand_total', 'penjualan.jenis_pembayaran', 'penjualan.dibayar', 'penjualan.kembalian', 'penjualan.catatan', 'penjualan.status', 'penjualan.created_by', 'penjualan.updated_by', 'penjualan.created_at', 'penjualan.updated_at', 'users.name');

        // Apply filters
        if ($this->request->filled('tanggal_dari')) {
            $query->where('tanggal_penjualan', '>=', $this->request->tanggal_dari);
        }
        if ($this->request->filled('tanggal_sampai')) {
            $query->where('tanggal_penjualan', '<=', $this->request->tanggal_sampai);
        }
        if ($this->request->filled('pelanggan_id') && $this->request->pelanggan_id != 'all') {
            $query->where('pelanggan_id', $this->request->pelanggan_id);
        }
        if ($this->request->filled('status') && $this->request->status != 'all') {
            $query->where('penjualan.status', $this->request->status);
        }
        if ($this->request->filled('metode_pembayaran') && $this->request->metode_pembayaran != 'all') {
            if ($this->request->metode_pembayaran == 'tunai') {
                $query->where('jenis_pembayaran', 'tunai');
            } else {
                $query->whereIn('jenis_pembayaran', ['non_tunai', 'campuran']);
            }
        }

        $data = $query->get();

        // Calculate summaries (samakan dengan rumus di halaman DataTables)
        // Di halaman: 
        // - Laba Kotor = (dibayar - kembalian)
        // - Laba Bersih = (laba_kotor - total_modal)
        $totalModal = $data->sum('total_hpp');
        $totalLabaKotor = $data->sum(function ($row) {
            return ((float) $row->dibayar) - ((float) $row->kembalian);
        });

        $summary = [
            'total_transaksi' => $data->count(),
            'total_penjualan' => $data->sum('total'),
            'total_pembulatan' => $data->sum('pembulatan'),
            'total_laba_kotor' => $totalLabaKotor,
            'total_modal' => $totalModal,
            'total_laba_bersih' => $totalLabaKotor - $totalModal
        ];

        return view('exports.laporan-penjualan', compact('data', 'summary'));
    }
}
