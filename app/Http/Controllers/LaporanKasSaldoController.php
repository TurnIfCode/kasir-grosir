<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use App\Models\KasSaldo;
use App\Models\KasSaldoTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanKasSaldoController extends Controller
{
    public function index()
    {
        // Get distinct kas names from kas_saldo_transaksi through kas_saldo relationship
        $kasOptions = KasSaldoTransaksi::with('kasSaldo')
            ->get()
            ->pluck('kasSaldo.kas')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return view('laporan.kas-saldo', compact('kasOptions'));
    }

    public function data(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $namaKas = $request->nama_kas;

        // Get kas names from KasSaldoTransaksi
        $kasSaldoQuery = KasSaldoTransaksi::with('kasSaldo');

        // Filter by kas name if specified
        if ($namaKas && $namaKas != 'all') {
            $kasSaldoQuery->whereHas('kasSaldo', function($query) use ($namaKas) {
                $query->where('kas', $namaKas);
            });
        }

        // Get data grouped by kas name
        $groupedData = $kasSaldoQuery
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get()
            ->groupBy(function($item) {
                return $item->kasSaldo->kas ?? 'Unknown';
            });

        $data = [];

        foreach ($groupedData as $kasName => $transactions) {
            // Calculate totals based on tipe transaction
            $totalMasuk = $transactions->where('tipe', 'masuk')->sum(function($transaksi) {
                return $transaksi->saldo_akhir - $transaksi->saldo_awal;
            });
            
            $totalKeluar = $transactions->where('tipe', 'keluar')->sum(function($transaksi) {
                return $transaksi->saldo_awal - $transaksi->saldo_akhir;
            });
            
            // Get saldo awal from first transaction in range
            $saldoAwal = $transactions->first()->saldo_awal ?? 0;
            // Get saldo akhir from last transaction in range
            $saldoAkhir = $transactions->last()->saldo_akhir ?? 0;

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

        // Get transactions from KasSaldoTransaksi
        $kasSaldoQuery = KasSaldoTransaksi::with('kasSaldo');

        // Filter by kas name if specified
        if ($namaKas && $namaKas != 'all') {
            $kasSaldoQuery->whereHas('kasSaldo', function($query) use ($namaKas) {
                $query->where('kas', $namaKas);
            });
        }

        $transactions = $kasSaldoQuery
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();

        // Calculate totals
        $totalMasuk = $transactions->where('tipe', 'masuk')->sum(function($transaksi) {
            return $transaksi->saldo_akhir - $transaksi->saldo_awal;
        });
        
        $totalKeluar = $transactions->where('tipe', 'keluar')->sum(function($transaksi) {
            return $transaksi->saldo_awal - $transaksi->saldo_akhir;
        });
        
        // Get saldo awal from earliest transaction and saldo akhir from latest transaction
        $totalSaldoAwal = $transactions->first()->saldo_awal ?? 0;
        $totalSaldoAkhir = $transactions->last()->saldo_akhir ?? 0;

        return response()->json([
            'total_saldo_awal' => 'Rp ' . number_format($totalSaldoAwal, 0, ',', '.'),
            'total_masuk' => 'Rp ' . number_format($totalMasuk, 0, ',', '.'),
            'total_keluar' => 'Rp ' . number_format($totalKeluar, 0, ',', '.'),
            'total_saldo_akhir' => 'Rp ' . number_format($totalSaldoAkhir, 0, ',', '.'),
        ]);
    }

    public function exportPDF(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $namaKas = $request->nama_kas;

        // Get transactions from KasSaldoTransaksi
        $kasSaldoQuery = KasSaldoTransaksi::with('kasSaldo');

        // Filter by kas name if specified
        if ($namaKas && $namaKas != 'all') {
            $kasSaldoQuery->whereHas('kasSaldo', function($query) use ($namaKas) {
                $query->where('kas', $namaKas);
            });
        }

        $transactions = $kasSaldoQuery
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();


        // Group data by kas name
        $groupedData = $transactions->groupBy(function($item) {
            return $item->kasSaldo->kas ?? 'Unknown';
        });

        $data = [];
        $totalSaldoAwal = 0;
        $totalMasuk = 0;
        $totalKeluar = 0;
        $totalSaldoAkhir = 0;

        foreach ($groupedData as $kasName => $kasTransactions) {
            // Calculate totals based on tipe transaction
            $masuk = $kasTransactions->where('tipe', 'masuk')->sum(function($transaksi) {
                return $transaksi->saldo_akhir - $transaksi->saldo_awal;
            });
            
            $keluar = $kasTransactions->where('tipe', 'keluar')->sum(function($transaksi) {
                return $transaksi->saldo_awal - $transaksi->saldo_akhir;
            });
            
            // Get saldo awal from first transaction and saldo akhir from last transaction
            $saldoAwal = $kasTransactions->first()->saldo_awal ?? 0;
            $saldoAkhir = $kasTransactions->last()->saldo_akhir ?? 0;

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
