<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Paket;
use App\Models\PaketDetail;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Log;

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
        $nama = trim($request->nama);
        $total_qty = trim($request->total_qty);
        $harga = trim($request->harga);
        $jenis = trim($request->jenis);
        $status = trim($request->status);
        $barang_ids = array_map('trim', $request->barang_ids);


        //disini cek dulu nama paketnya.
        $cekNama = Paket::where('nama', $nama)->count();
        if ($cekNama > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Nama paket sudah terdaftar',
                'form'      => 'nama'
            ]);
        }

        $total_qty  = round($total_qty);
        $harga      = round($harga);

        if ($total_qty <= 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Total quantity harus lebih besar dari 0',
                'form'      => 'total_qty'
            ]);
        }


        if ($harga <= 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Harga harus lebih besar dari 0',
                'form'      => 'harga'
            ]);
        }

        $paket = new Paket();
        $paket->nama = $nama;
        $paket->total_qty = $total_qty;
        $paket->harga = $harga;
        $paket->jenis = $jenis;
        $paket->status = $status;
        $paket->created_by = auth()->id();
        $paket->created_at = now();
        $paket->updated_by = auth()->id();
        $paket->updated_at = now();
        $paket->save();

        if (count($barang_ids) > 0) {
            foreach ($barang_ids as $barang_id) {
                $paketDetail = new PaketDetail();
                $paketDetail->paket_id = $paket->id;
                $paketDetail->barang_id = $barang_id;
                $paketDetail->created_by = auth()->id();
                $paketDetail->created_at = now();
                $paketDetail->updated_by = auth()->id();
                $paketDetail->updated_at = now();
                $paketDetail->save();
            }
            
        }

        $log = new Log();
        $log->keterangan = 'Tambah paket : '.$paket->nama.' | Total Quantity : '.round($paket->total_qty).' | Harga Paket : '.round($paket->harga).' | Jenis : '.$paket->jenis.' Status : '.$paket->status;
        $log->created_by = auth()->id();
        $log->created_at = now();
        $log->save();

        return response()->json([
            'success'   => true,
            'message'   => 'Berhasil tambah paket'
        ]);
    }

    public function edit($id)
    {
        $paket = Paket::with('details.barang')->findOrFail($id);
        return view('master.paket.edit', compact('paket'));
    }

    public function update(Request $request, $id)
    {
        $nama = trim($request->nama);
        $total_qty = trim($request->total_qty);
        $harga = trim($request->harga);
        $jenis = trim($request->jenis);
        $status = trim($request->status);
        $barang_ids = array_map('trim', $request->barang_ids);

        //disini cek dulu nama paketnya.
        $cekNama = Paket::where('nama', $nama)->where('id', '!=', $id)->count();
        if ($cekNama > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Nama paket sudah terdaftar',
                'form'      => 'nama'
            ]);
        }

        $total_qty  = round($total_qty);
        $harga      = round($harga);

        if ($total_qty <= 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Total quantity harus lebih besar dari 0',
                'form'      => 'total_qty'
            ]);
        }


        if ($harga <= 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Harga harus lebih besar dari 0',
                'form'      => 'harga'
            ]);
        }

        $paket = Paket::find($id);
        $paket->nama = $nama;
        $paket->total_qty = $total_qty;
        $paket->harga = $harga;
        $paket->jenis = $jenis;
        $paket->status = $status;
        $paket->updated_by = auth()->id();
        $paket->updated_at = now();
        $paket->save();

        if (count($barang_ids) > 0) {
            PaketDetail::where('paket_id', $id)
                ->whereNotIn('barang_id', $barang_ids)
                ->delete();

                $existingBarangIds = PaketDetail::where('paket_id', $id)->pluck('barang_id')->toArray();

            foreach ($barang_ids as $barang_id) {
                if (!in_array($barang_id, $existingBarangIds)) {
                    $paketDetail = new PaketDetail();
                    $paketDetail->paket_id = $id;
                    $paketDetail->barang_id = $barang_id;
                    $paketDetail->created_by = auth()->id();
                    $paketDetail->created_at = now();
                    $paketDetail->updated_by = auth()->id();
                    $paketDetail->updated_at = now();
                    $paketDetail->save();
                }
            }
        }

        $log = new Log();
        $log->keterangan = 'Ubah paket : '.$paket->nama.' | Total Quantity : '.round($paket->total_qty).' | Harga Paket : '.round($paket->harga).' | Jenis : '.$paket->jenis.' Status : '.$paket->status;
        $log->created_by = auth()->id();
        $log->created_at = now();
        $log->save();

        return response()->json([
            'success'   => true,
            'message'   => 'Berhasil ubah paket'
        ]);
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
