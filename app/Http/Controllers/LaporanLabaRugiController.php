<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanLabaRugiController extends Controller
{
    public function index()
    {
        return view('laporan.laba_rugi');
    }

    public function data(Request $request)
    {
        $tanggalDari = $request->tanggal_dari;
        $tanggalSampai = $request->tanggal_sampai;

        $query = DB::table('penjualan')
            ->selectRaw('
                DATE(penjualan.tanggal_penjualan) as tanggal,
                SUM(penjualan.grand_total) as total_penjualan,
                SUM(penjualan.ppn) as ppn_penjualan,
                SUM(penjualan.diskon) as diskon_penjualan
            ')
            ->where('penjualan.status', 'selesai')
            ->when($tanggalDari, function ($q) use ($tanggalDari) {
                return $q->where('penjualan.tanggal_penjualan', '>=', $tanggalDari);
            })
            ->when($tanggalSampai, function ($q) use ($tanggalSampai) {
                return $q->where('penjualan.tanggal_penjualan', '<=', $tanggalSampai);
            })
            ->groupBy(DB::raw('DATE(penjualan.tanggal_penjualan)'))
            ->unionAll(
                DB::table('pembelian')
                    ->leftJoin('pembelian_detail', 'pembelian.id', '=', 'pembelian_detail.pembelian_id')
                    ->selectRaw('
                        DATE(pembelian.tanggal_pembelian) as tanggal,
                        0 as total_penjualan,
                        0 as ppn_penjualan,
                        0 as diskon_penjualan
                    ')
                    ->where('pembelian.status', 'selesai')
                    ->when($tanggalDari, function ($q) use ($tanggalDari) {
                        return $q->where('pembelian.tanggal_pembelian', '>=', $tanggalDari);
                    })
                    ->when($tanggalSampai, function ($q) use ($tanggalSampai) {
                        return $q->where('pembelian.tanggal_pembelian', '<=', $tanggalSampai);
                    })
                    ->groupBy(DB::raw('DATE(pembelian.tanggal_pembelian)'))
            )
            ->unionAll(
                DB::table('pembelian')
                    ->leftJoin('pembelian_detail', 'pembelian.id', '=', 'pembelian_detail.pembelian_id')
                    ->selectRaw('
                        DATE(pembelian.tanggal_pembelian) as tanggal,
                        0 as total_penjualan,
                        SUM(pembelian.ppn) as ppn_penjualan,
                        0 as diskon_penjualan
                    ')
                    ->where('pembelian.status', 'selesai')
                    ->when($tanggalDari, function ($q) use ($tanggalDari) {
                        return $q->where('pembelian.tanggal_pembelian', '>=', $tanggalDari);
                    })
                    ->when($tanggalSampai, function ($q) use ($tanggalSampai) {
                        return $q->where('pembelian.tanggal_pembelian', '<=', $tanggalSampai);
                    })
                    ->groupBy(DB::raw('DATE(pembelian.tanggal_pembelian)'))
            );

        $data = DB::table(DB::raw("({$query->toSql()}) as combined"))
            ->mergeBindings($query)
            ->selectRaw('
                tanggal,
                SUM(total_penjualan) as total_penjualan,
                SUM(ppn_penjualan) as ppn_penjualan,
                SUM(diskon_penjualan) as diskon_penjualan
            ')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // Now get pembelian data separately and merge
        $pembelianData = DB::table('pembelian')
            ->leftJoin('pembelian_detail', 'pembelian.id', '=', 'pembelian_detail.pembelian_id')
            ->selectRaw('
                DATE(pembelian.tanggal_pembelian) as tanggal,
                SUM(pembelian_detail.subtotal) as total_pembelian,
                SUM(pembelian.ppn) as ppn_pembelian
            ')
            ->where('pembelian.status', 'selesai')
            ->when($tanggalDari, function ($q) use ($tanggalDari) {
                return $q->where('pembelian.tanggal_pembelian', '>=', $tanggalDari);
            })
            ->when($tanggalSampai, function ($q) use ($tanggalSampai) {
                return $q->where('pembelian.tanggal_pembelian', '<=', $tanggalSampai);
            })
            ->groupBy(DB::raw('DATE(pembelian.tanggal_pembelian)'))
            ->get()
            ->keyBy('tanggal');

        // Merge data
        $mergedData = $data->map(function ($item) use ($pembelianData) {
            $pembelian = $pembelianData->get($item->tanggal);
            $item->total_pembelian = $pembelian ? $pembelian->total_pembelian : 0;
            $item->ppn_pembelian = $pembelian ? $pembelian->ppn_pembelian : 0;
            $item->laba_kotor = $item->total_penjualan;
            $item->laba_bersih = $item->laba_kotor - $item->ppn_pembelian + $item->ppn_penjualan;
            return $item;
        });

        return DataTables::of($mergedData)
            ->addIndexColumn()
            ->addColumn('tanggal_formatted', function ($row) {
                return Carbon::parse($row->tanggal)->format('d/m/Y');
            })
            ->addColumn('total_penjualan_formatted', function ($row) {
                return 'Rp ' . number_format($row->total_penjualan, 0, ',', '.');
            })
            ->addColumn('total_pembelian_formatted', function ($row) {
                return 'Rp ' . number_format($row->total_pembelian, 0, ',', '.');
            })
            ->addColumn('laba_kotor_formatted', function ($row) {
                return 'Rp ' . number_format($row->laba_kotor, 0, ',', '.');
            })
            ->addColumn('laba_bersih_formatted', function ($row) {
                return 'Rp ' . number_format($row->laba_bersih, 0, ',', '.');
            })
            ->rawColumns([])
            ->make(true);
    }

    public function exportPDF(Request $request)
    {
        $tanggalDari = $request->tanggal_dari;
        $tanggalSampai = $request->tanggal_sampai;

        // Reuse the data logic
        $query = DB::table('penjualan')
            ->selectRaw('
                DATE(penjualan.tanggal_penjualan) as tanggal,
                SUM(penjualan.grand_total) as total_penjualan,
                SUM(penjualan.ppn) as ppn_penjualan,
                SUM(penjualan.diskon) as diskon_penjualan
            ')
            ->where('penjualan.status', 'selesai')
            ->when($tanggalDari, function ($q) use ($tanggalDari) {
                return $q->where('penjualan.tanggal_penjualan', '>=', $tanggalDari);
            })
            ->when($tanggalSampai, function ($q) use ($tanggalSampai) {
                return $q->where('penjualan.tanggal_penjualan', '<=', $tanggalSampai);
            })
            ->groupBy(DB::raw('DATE(penjualan.tanggal_penjualan)'));

        $penjualanData = $query->get()->keyBy('tanggal');

        $pembelianQuery = DB::table('pembelian')
            ->leftJoin('pembelian_detail', 'pembelian.id', '=', 'pembelian_detail.pembelian_id')
            ->selectRaw('
                DATE(pembelian.tanggal_pembelian) as tanggal,
                SUM(pembelian_detail.subtotal) as total_pembelian,
                SUM(pembelian.ppn) as ppn_pembelian
            ')
            ->where('pembelian.status', 'selesai')
            ->when($tanggalDari, function ($q) use ($tanggalDari) {
                return $q->where('pembelian.tanggal_pembelian', '>=', $tanggalDari);
            })
            ->when($tanggalSampai, function ($q) use ($tanggalSampai) {
                return $q->where('pembelian.tanggal_pembelian', '<=', $tanggalSampai);
            })
            ->groupBy(DB::raw('DATE(pembelian.tanggal_pembelian)'));

        $pembelianData = $pembelianQuery->get()->keyBy('tanggal');

        // Get all dates
        $allDates = collect(array_merge($penjualanData->keys()->toArray(), $pembelianData->keys()->toArray()))->unique()->sort();

        $data = $allDates->map(function ($tanggal) use ($penjualanData, $pembelianData) {
            $penjualan = $penjualanData->get($tanggal);
            $pembelian = $pembelianData->get($tanggal);
            return (object) [
                'tanggal' => $tanggal,
                'total_penjualan' => $penjualan ? $penjualan->total_penjualan : 0,
                'total_pembelian' => $pembelian ? $pembelian->total_pembelian : 0,
                'ppn_penjualan' => $penjualan ? $penjualan->ppn_penjualan : 0,
                'ppn_pembelian' => $pembelian ? $pembelian->ppn_pembelian : 0,
                'laba_kotor' => ($penjualan ? $penjualan->total_penjualan : 0),
                'laba_bersih' => (($penjualan ? $penjualan->total_penjualan : 0)) - ($pembelian ? $pembelian->ppn_pembelian : 0) + ($penjualan ? $penjualan->ppn_penjualan : 0),
            ];
        });

        $totalPenjualan = $data->sum('total_penjualan');
        $totalPembelian = $data->sum('total_pembelian');
        $totalLabaKotor = $data->sum('laba_kotor');
        $totalLabaBersih = $data->sum('laba_bersih');

        $tanggalDariFormatted = $tanggalDari ? Carbon::parse($tanggalDari)->format('d/m/Y') : '-';
        $tanggalSampaiFormatted = $tanggalSampai ? Carbon::parse($tanggalSampai)->format('d/m/Y') : '-';

        $pdf = Pdf::loadView('laporan.laba_rugi-pdf', compact('data', 'totalPenjualan', 'totalPembelian', 'totalLabaKotor', 'totalLabaBersih', 'tanggalDariFormatted', 'tanggalSampaiFormatted'));

        return $pdf->download('laporan-laba-rugi.pdf');
    }
}
