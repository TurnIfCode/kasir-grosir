<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanLogController extends Controller
{
    /**
     * Show the laporan log page with filters.
     */
    public function index()
    {
        $today = Carbon::today()->format('Y-m-d');
        return view('laporan.log', compact('today'));
    }

    /**
     * Return JSON data filtered by tanggal awal and tanggal akhir.
     */
    public function data(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal', Carbon::today()->format('Y-m-d'));
        $tanggalAkhir = $request->input('tanggal_akhir', Carbon::today()->format('Y-m-d'));

        $logs = Log::query();

        // Assuming 'created_at' or similar datetime column exists, otherwise adjust field name
        $logs->whereDate('created_at', '>=', $tanggalAwal)
             ->whereDate('created_at', '<=', $tanggalAkhir)
             ->orderBy('created_at', 'desc');

        $data = $logs->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
