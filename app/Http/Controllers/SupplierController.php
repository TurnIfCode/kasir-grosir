<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return view('supplier.index');
    }

    public function add()
    {
        return view('supplier.add');
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search') ? $request->get('search')['value'] : '';

            $query = Supplier::query();

            if (!empty($search)) {
                $query->where('kode_supplier', 'like', '%' . $search . '%')
                      ->orWhere('nama_supplier', 'like', '%' . $search . '%')
                      ->orWhere('kontak_person', 'like', '%' . $search . '%')
                      ->orWhere('telepon', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('kota', 'like', '%' . $search . '%')
                      ->orWhere('provinsi', 'like', '%' . $search . '%')
                      ->orWhere('status', 'like', '%' . $search . '%');
            }

            $totalRecords = Supplier::count();
            $filteredRecords = $query->count();

            $suppliers = $query->skip($start)->take($length)->get();

            $data = [];
            $no = $start + 1;
            foreach ($suppliers as $supplier) {
                $data[] = [
                    'DT_RowIndex' => $no++,
                    'kode_supplier' => $supplier->kode_supplier,
                    'nama_supplier' => $supplier->nama_supplier,
                    'kontak_person' => $supplier->kontak_person ?: '-',
                    'telepon' => $supplier->telepon ?: '-',
                    'email' => $supplier->email ?: '-',
                    'alamat' => $supplier->alamat ?: '-',
                    'kota' => $supplier->kota ?: '-',
                    'provinsi' => $supplier->provinsi ?: '-',
                    'status' => $supplier->status,
                    'aksi' => '<a href="#" id="btnDetail" data-id="' . $supplier->id . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a> <a href="#" id="btnEdit" data-id="' . $supplier->id . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a> <a href="#" data-id="' . $supplier->id . '" id="btnDelete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>'
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('supplier.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:150',
            'kontak_person' => 'nullable|string|max:100',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
            'kota' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'status' => 'required|in:aktif,nonaktif'
        ], [
            'nama_supplier.required' => 'Nama Supplier wajib diisi',
            'email.email' => 'Format email tidak valid',
            'status.required' => 'Status wajib dipilih'
        ]);

        // Generate kode_supplier otomatis
        $kodeSupplier = $this->generateKodeSupplier();

        $supplier = Supplier::create([
            'kode_supplier' => $kodeSupplier,
            'nama_supplier' => $request->nama_supplier,
            'kontak_person' => $request->kontak_person,
            'telepon' => $request->telepon,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'kota' => $request->kota,
            'provinsi' => $request->provinsi,
            'status' => $request->status,
            'created_by' => auth()->check() ? auth()->user()->id : null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $newLog = new Log();
        $newLog->keterangan = 'Menambahkan supplier baru: ' . $supplier->nama_supplier . ' (Kode Supplier: ' . $supplier->kode_supplier . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'status' => true,
            'message' => 'Supplier berhasil ditambahkan'
        ]);
    }

    public function find($id)
    {
        $supplier = Supplier::with(['creator', 'updater'])->findOrFail($id);
        $data = $supplier->toArray();
        $data['created_by'] = $supplier->creator ? $supplier->creator->name : '-';
        $data['updated_by'] = $supplier->updater ? $supplier->updater->name : '-';
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'kode_supplier' => 'required|string|max:50|unique:supplier,kode_supplier,' . $id,
            'nama_supplier' => 'required|string|max:150',
            'kontak_person' => 'nullable|string|max:100',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
            'kota' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'status' => 'required|in:aktif,nonaktif'
        ], [
            'kode_supplier.required' => 'Kode Supplier wajib diisi',
            'kode_supplier.unique' => 'Kode Supplier sudah terdaftar',
            'nama_supplier.required' => 'Nama Supplier wajib diisi',
            'email.email' => 'Format email tidak valid',
            'status.required' => 'Status wajib dipilih'
        ]);

        $supplier->update([
            'kode_supplier' => $request->kode_supplier,
            'nama_supplier' => $request->nama_supplier,
            'kontak_person' => $request->kontak_person,
            'telepon' => $request->telepon,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'kota' => $request->kota,
            'provinsi' => $request->provinsi,
            'status' => $request->status,
            'updated_by' => auth()->check() ? auth()->user()->id : null,
            'updated_at' => now()
        ]);

        $newLog = new Log();
        $newLog->keterangan = 'Memperbarui supplier: ' . $supplier->nama_supplier . ' (Kode Supplier: ' . $supplier->kode_supplier . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'status' => true,
            'message' => 'Supplier berhasil diperbarui'
        ]);
    }

    public function delete($id)
    {
        $supplier = Supplier::findOrFail($id);
        $namaSupplier = $supplier->nama_supplier;
        $kodeSupplier = $supplier->kode_supplier;

        $supplier->delete();

        $newLog = new Log();
        $newLog->keterangan = 'Menghapus supplier: ' . $namaSupplier . ' (Kode Supplier: ' . $kodeSupplier . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'status' => true,
            'message' => 'Supplier berhasil dihapus'
        ]);
    }

    public function generateKode()
    {
        $kodeSupplier = $this->generateKodeSupplier();
        return response()->json(['kode_supplier' => $kodeSupplier]);
    }

    public function search(Request $request)
    {
        $q = $request->get('q');
        $suppliers = Supplier::where('nama_supplier', 'like', '%' . $q . '%')
                            ->orWhere('kode_supplier', 'like', '%' . $q . '%')
                            ->where('status', 'aktif')
                            ->limit(10)
                            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $suppliers
        ]);
    }

    private function generateKodeSupplier()
    {
        $lastSupplier = Supplier::orderBy('id', 'desc')->first();
        $nextNumber = $lastSupplier ? intval(substr($lastSupplier->kode_supplier, 3)) + 1 : 1;
        return 'SUP' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
