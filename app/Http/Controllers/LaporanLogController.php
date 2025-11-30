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
        $columns = ['id', 'keterangan', 'created_by', 'created_at'];
        $tanggalAwal = $request->input('tanggal_awal', Carbon::today()->format('Y-m-d'));
        $tanggalAkhir = $request->input('tanggal_akhir', Carbon::today()->format('Y-m-d'));

        $query = Log::with('user')
            ->whereDate('created_at', '>=', $tanggalAwal)
            ->whereDate('created_at', '<=', $tanggalAkhir);

        $totalData = $query->count();

        // Search filter
        $search = $request->input('search.value');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $totalFiltered = $query->count();

        // Ordering
        $orderColumnIndex = $request->input('order.0.column', 3); // Default order by created_at desc
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        $orderDir = $request->input('order.0.dir', 'desc');
        if ($orderColumn == 'created_by') {
            // Order by user's name
            $query->join('users', 'log.created_by', '=', 'users.id')
                ->orderBy('users.name', $orderDir)
                ->select('log.*'); // Ensure correct columns
        } else {
            $query->orderBy($orderColumn, $orderDir);
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $data = $query->skip($start)->take($length)->get();

        // Format data for DataTables
        $jsonData = [
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data->map(function($log) {
                return [
                    'id' => $log->id,
                    'keterangan' => $log->keterangan,
                    'created_by' => $log->user ? $log->user->name : '-',
                    'created_at' => $log->created_at,
                ];
            }),
        ];

        return response()->json($jsonData);
    }
}
