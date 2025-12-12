<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Paket;
use App\Models\PaketDetail;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaketController extends Controller
{
    public function index()
    {
        $paket = Paket::with('details.barang')->paginate(10);
        return view('master.paket.index', compact('paket'));
    }

    public function data(Request $request)
    {
        $columns = [
            0 => 'id',
            1 => 'nama',
            2 => 'total_qty',
            3 => 'status',
        ];

        $totalData = Paket::count();
        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $orderColumnIndex = $request->input('order.0.column');
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDir = $request->input('order.0.dir', 'asc');
        $searchValue = $request->input('search.value');

        $query = Paket::query();

        if(!empty($searchValue)) {
            $query->where(function($q) use ($searchValue) {
                $q->where('nama', 'LIKE', "%{$searchValue}%")
                  ->orWhere('status', 'LIKE', "%{$searchValue}%");
            });

            $totalFiltered = $query->count();
        }

        $paket = $query->offset($start)
            ->limit($limit)
            ->orderBy($orderColumn, $orderDir)
            ->get();

        $data = [];

        foreach ($paket as $item) {
            $nestedData = [];
            $nestedData[] = $item->id;
            $nestedData[] = $item->nama;
            $nestedData[] = $item->total_qty;
            $nestedData[] = 'Rp ' . number_format($item->harga, 0, ',', '.');
            $nestedData[] = ucfirst($item->status);
            $nestedData[] = '<a href="'. route('master.paket.edit', $item->id) .'" class="btn btn-sm btn-warning">Edit</a>';
            $data[] = $nestedData;
        }

        $json_data = [
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ];

        return response()->json($json_data);
    }

    public function create()
    {
        return view('master.paket.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'total_qty' => 'required|integer|min:1',
            'harga' => 'required|numeric|min:0',
            'status' => 'required|in:aktif,nonaktif',
            'barang_ids' => 'required|array',
            'barang_ids.*' => 'integer|exists:barang,id',
        ]);

        DB::beginTransaction();
        try {
            $paket = Paket::create([
                'nama' => $request->nama,
                'total_qty' => $request->total_qty,
                'harga' => $request->harga,
                'status' => $request->status,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            foreach ($request->barang_ids as $barang_id) {
                PaketDetail::create([
                    'paket_id' => $paket->id,
                    'barang_id' => $barang_id,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }
            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Paket berhasil ditambahkan'
                ]);
            }

            return redirect()->route('master.paket.index')->with('success', 'Paket created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create paket: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->withErrors(['error' => 'Failed to create paket: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $paket = Paket::with('details.barang')->findOrFail($id);
        return view('master.paket.edit', compact('paket'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'total_qty' => 'required|integer|min:1',
            'harga' => 'required|numeric|min:0',
            'status' => 'required|in:aktif,nonaktif',
            'barang_ids' => 'required|array',
            'barang_ids.*' => 'integer|exists:barang,id',
        ]);

        DB::beginTransaction();
        try {
            $paket = Paket::findOrFail($id);
            $paket->update([
                'nama' => $request->nama,
                'total_qty' => $request->total_qty,
                'harga' => $request->harga,
                'status' => $request->status,
                'updated_by' => Auth::id(),
            ]);

            // Sync paket details
            PaketDetail::where('paket_id', $id)
                ->whereNotIn('barang_id', $request->barang_ids)
                ->delete();

            $existingBarangIds = PaketDetail::where('paket_id', $id)->pluck('barang_id')->toArray();

            foreach ($request->barang_ids as $barang_id) {
                if (!in_array($barang_id, $existingBarangIds)) {
                    PaketDetail::create([
                        'paket_id' => $id,
                        'barang_id' => $barang_id,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);
                }
            }


        DB::commit();
            return redirect()->route('master.paket.index')->with('success', 'Paket updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update paket: ' . $e->getMessage()]);
        }
    }

    /**
     * Check apakah barang ada di dalam paket dan mengembalikan info paket
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPaketForBarang(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:barang,id'
        ]);

        try {
            $barangId = $request->barang_id;

            // Cari paket yang mengandung barang ini
            $paketDetail = PaketDetail::select('paket_id')
                ->where('barang_id', $barangId)
                ->first();

            if (!$paketDetail) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Barang tidak ditemukan di paket manapun'
                ]);
            }

            // Ambil info paket lengkap
            $paket = Paket::with('details.barang')
                ->where('id', $paketDetail->paket_id)
                ->where('status', 'aktif')
                ->first();

            if (!$paket) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Paket tidak aktif'
                ]);
            }

            // Ambil semua barang_id dalam paket ini
            $paketBarangIds = $paket->details->pluck('barang_id')->toArray();

            $responseData = [
                'paket_id' => $paket->id,
                'nama' => $paket->nama,
                'jenis' => $paket->jenis,
                'total_qty' => $paket->total_qty,
                'harga' => $paket->harga,
                'paket_barang_ids' => $paketBarangIds,
                'paket_barang_count' => count($paketBarangIds)
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Barang ditemukan di paket: ' . $paket->nama
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
