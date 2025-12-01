<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use App\Models\KasSaldo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanKasSaldoController extends Controller
{
    public function index()
    {
        // Get distinct kas names from kas_saldo table
        $kasOptions = KasSaldo::select('kas')->distinct()->pluck('kas')->toArray();

        return view('laporan.kas-saldo', compact('kasOptions'));
    }

    public function data(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $namaKas = $request->nama_kas;

        // Get all kas names if no specific kas selected
        if (!$namaKas || $namaKas == 'all') {
            $kasNames = KasSaldo::select('kas')->distinct()->pluck('kas')->toArray();
        } else {
            $kasNames = [$namaKas];
        }

        $data = [];

        foreach ($kasNames as $kasName) {
            // Get saldo awal
            $saldoAwal = $this->getSaldoAwal($kasName, $startDate);

            // Get total masuk and keluar in date range
            $totalMasuk = Kas::where('sumber_kas', $kasName)
                ->where('tipe', 'masuk')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->sum('nominal');

            $totalKeluar = Kas::where('sumber_kas', $kasName)
                ->where('tipe', 'keluar')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->sum('nominal');

            $saldoAkhir = $saldoAwal + $totalMasuk - $totalKeluar;

            $data[] = [
                'nama_kas' => $kasName,
                'saldo_awal' => $saldoAwal,
                'total_masuk' => $totalMasuk,
                'total_keluar' => $totalKeluar,
                'saldo_akhir' => $saldoAkhir,
            ];
        }

        return DataTables::of(collect($data))
            ->addIndexColumn()
            ->addColumn('saldo_awal_formatted', function ($row) {
                return 'Rp ' . number_format($row['saldo_awal'], 0, ',', '.');
            })
            ->addColumn('total_masuk_formatted', function ($row) {
                return 'Rp ' . number_format($row['total_masuk'], 0, ',', '.');
            })
            ->addColumn('total_keluar_formatted', function ($row) {
                return 'Rp ' . number_format($row['total_keluar'], 0, ',', '.');
            })
            ->addColumn('saldo_akhir_formatted', function ($row) {
                return 'Rp ' . number_format($row['saldo_akhir'], 0, ',', '.');
            })
            ->make(true);
    }

    public function getRingkasan(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $namaKas = $request->nama_kas;

        // Get all kas names if no specific kas selected
        if (!$namaKas || $namaKas == 'all') {
            $kasNames = KasSaldo::select('kas')->distinct()->pluck('kas')->toArray();
        } else {
            $kasNames = [$namaKas];
        }

        $totalSaldoAwal = 0;
        $totalMasuk = 0;
        $totalKeluar = 0;
        $totalSaldoAkhir = 0;

        foreach ($kasNames as $kasName) {
            // Get saldo awal
            $saldoAwal = $this->getSaldoAwal($kasName, $startDate);
            $totalSaldoAwal += $saldoAwal;

            // Get total masuk and keluar in date range
            $masuk = Kas::where('sumber_kas', $kasName)
                ->where('tipe', 'masuk')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->sum('nominal');
            $totalMasuk += $masuk;

            $keluar = Kas::where('sumber_kas', $kasName)
                ->where('tipe', 'keluar')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->sum('nominal');
            $totalKeluar += $keluar;

            $totalSaldoAkhir += ($saldoAwal + $masuk - $keluar);
        }

        return response()->json([
            'total_saldo_awal' => 'Rp ' . number_format($totalSaldoAwal, 0, ',', '.'),
            'total_masuk' => 'Rp ' . number_format($totalMasuk, 0, ',', '.'),
            'total_keluar' => 'Rp ' . number_format($totalKeluar, 0, ',', '.'),
            'total_saldo_akhir' => 'Rp ' . number_format($totalSaldoAkhir, 0, ',', '.'),
        ]);
    }

    private function getSaldoAwal($namaKas, $startDate)
    {
        // First, try to get from kas_saldo table
        $kasSaldo = KasSaldo::where('kas', $namaKas)->first();
        if ($kasSaldo) {
            return $kasSaldo->saldo;
        }

        // If not found, calculate from kas transactions before start_date
        $totalMasuk = Kas::where('sumber_kas', $namaKas)
            ->where('tipe', 'masuk')
            ->where('tanggal', '<', $startDate)
            ->sum('nominal');

        $totalKeluar = Kas::where('sumber_kas', $namaKas)
            ->where('tipe', 'keluar')
            ->where('tanggal', '<', $startDate)
            ->sum('nominal');

        return $totalMasuk - $totalKeluar;
    }

    public function exportPDF(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $namaKas = $request->nama_kas;

        // Get all kas names if no specific kas selected
        if (!$namaKas || $namaKas == 'all') {
            $kasNames = KasSaldo::select('kas')->distinct()->pluck('kas')->toArray();
        } else {
            $kasNames = [$namaKas];
        }

        $data = [];
        $totalSaldoAwal = 0;
        $totalMasuk = 0;
        $totalKeluar = 0;
        $totalSaldoAkhir = 0;

        foreach ($kasNames as $kasName) {
            // Get saldo awal
            $saldoAwal = $this->getSaldoAwal($kasName, $startDate);

            // Get total masuk and keluar in date range
            $masuk = Kas::where('sumber_kas', $kasName)
                ->where('tipe', 'masuk')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->sum('nominal');

            $keluar = Kas::where('sumber_kas', $kasName)
                ->where('tipe', 'keluar')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->sum('nominal');

            $saldoAkhir = $saldoAwal + $masuk - $keluar;

            $data[] = [
                'nama_kas' => $kasName,
                'saldo_awal' => $saldoAwal,
                'total_masuk' => $masuk,
                'total_keluar' => $keluar,
                'saldo_akhir' => $saldoAkhir,
            ];

            $totalSaldoAwal += $saldoAwal;
            $totalMasuk += $masuk;
            $totalKeluar += $keluar;
            $totalSaldoAkhir += $saldoAkhir;
        }

        $ringkasan = [
            'total_saldo_awal' => $totalSaldoAwal,
            'total_masuk' => $totalMasuk,
            'total_keluar' => $totalKeluar,
            'total_saldo_akhir' => $totalSaldoAkhir,
        ];

        $tanggalDari = $startDate ? Carbon::parse($startDate)->format('d/m/Y') : '-';
        $tanggalSampai = $endDate ? Carbon::parse($endDate)->format('d/m/Y') : '-';

        $pdf = Pdf::loadView('laporan.kas-saldo-pdf', compact('data', 'ringkasan', 'tanggalDari', 'tanggalSampai'));

        return $pdf->download('laporan-kas-saldo.pdf');
    }
}
