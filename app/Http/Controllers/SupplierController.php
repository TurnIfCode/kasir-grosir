<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Pembelian;
use App\Models\Supplier;
use Illuminate\Http\Request;
use function PHPUnit\Framework\returnArgument;

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

            // Handle ordering
            if ($request->has('order')) {
                $orderColumnIndex = $request->get('order')[0]['column'];
                $orderDirection = $request->get('order')[0]['dir'];
                $columns = ['kode_supplier', 'nama_supplier', 'kontak_person', 'telepon', 'email', 'alamat', 'kota', 'provinsi', 'status'];
                if (isset($columns[$orderColumnIndex])) {
                    $query->orderBy($columns[$orderColumnIndex], $orderDirection);
                }
            }

            $suppliers = $query->skip($start)->take($length)->get();

            $data = [];
            $no = $start + 1;
            foreach ($suppliers as $supplier) {
                $data[] = [
                    'kode_supplier' => $supplier->kode_supplier,
                    'nama_supplier' => $supplier->nama_supplier,
                    'kontak_person' => $supplier->kontak_person ?: '-',
                    'telepon' => $supplier->telepon ?: '-',
                    'email' => $supplier->email ?: '-',
                    'alamat' => $supplier->alamat ?: '-',
                    'kota' => $supplier->kota ?: '-',
                    'provinsi' => $supplier->provinsi ?: '-',
                    'status' => $supplier->status,
                    'aksi' => '<a href="#" id="btnDetail" data-id="' . $supplier->id . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a> <a href="#" id="btnEdit" data-id="' . $supplier->id . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>' . (auth()->user()->role == 'ADMIN' ? ' <a href="#" data-id="' . $supplier->id . '" id="btnDelete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>' : '')
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
        // Generate kode_supplier otomatis
        $kodeSupplier = $this->generateKodeSupplier();

        //disini ambil dulu semua variablenya
        $nama_supplier  = trim($request->nama_supplier);
        $kontak_person  = trim($request->kontak_person);
        $telepon        = trim($request->telepon);
        $email          = trim($request->email);
        $alamat         = trim($request->alamat);
        $kota           = trim($request->kota);
        $provinsi       = trim($request->provinsi);
        $status         = trim($request->status);

        // cek nama supplier sudah ada atau belum
        $cekNama = Supplier::where('nama_supplier', $nama_supplier)->first();
        if ($cekNama) {
            return response()->json([
                'success'   => false,
                'message'   => 'Nama supplier sudah terdaftar',
                'form'      => 'nama_supplier'
            ]);
        }

        $supplier                   = new Supplier();
        $supplier->kode_supplier    = $kodeSupplier;
        $supplier->nama_supplier    = $nama_supplier;
        $supplier->kontak_person    = $kontak_person;
        $supplier->telepon          = $telepon;
        $supplier->email            = $email;
        $supplier->alamat           = $alamat;
        $supplier->kota             = $kota;
        $supplier->provinsi         = $provinsi;
        $supplier->status           = $status;
        $supplier->created_by       = auth()->id();
        $supplier->created_at       = now();
        $supplier->updated_by       = auth()->id();
        $supplier->updated_at       = now();
        $supplier->save();

        $newLog = new Log();
        $newLog->keterangan = 'Menambahkan supplier baru: ' . $supplier->nama_supplier . ' (Kode Supplier: ' . $supplier->kode_supplier . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil ditambahkan'
        ]);
    }

    public function find($id)
    {
        $supplier = Supplier::with(['creator', 'updater'])->find($id);
        //cek ada datanya atau tidak
        if (!$supplier) {
            return response()->json([
                'success'   => false,
                'message'   => 'Data tidak ditemukan'
            ]);
        }
        $data = $supplier->toArray();
        $data['created_by'] = $supplier->creator ? $supplier->creator->name : '-';
        $data['updated_by'] = $supplier->updater ? $supplier->updater->name : '-';
        return response()->json([
            'success'   => true,
            'data'      => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $nama_supplier  = trim($request->nama_supplier);
        $kontak_person  = trim($request->kontak_person);
        $telepon        = trim($request->telepon);
        $email          = trim($request->email);
        $alamat         = trim($request->alamat);
        $kota           = trim($request->kota);
        $provinsi       = trim($request->provinsi);
        $status         = trim($request->status);

        // cek nama sudah terdaftar atau belum
        $cekNama = Supplier::where('nama_supplier', $nama_supplier)->where('id','!=', $id)->count();
        if ($cekNama > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Nama supplier sudah terdaftar',
                'form'      => 'nama_supplier'
            ]);
        }

        $supplier->nama_supplier = $nama_supplier;
        $supplier->kontak_person = $kontak_person;
        $supplier->telepon = $telepon;
        $supplier->email = $email;
        $supplier->alamat = $alamat;
        $supplier->kota = $kota;
        $supplier->provinsi = $provinsi;
        $supplier->status = $status;
        $supplier->updated_by = auth()->id();
        $supplier->updated_at = now();
        $supplier->save();

        $newLog = new Log();
        $newLog->keterangan = 'Memperbarui supplier: ' . $supplier->nama_supplier . ' (Kode Supplier: ' . $supplier->kode_supplier . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil diperbarui'
        ]);
    }

    public function delete($id)
    {
        $supplier = Supplier::findOrFail($id);
        $namaSupplier = $supplier->nama_supplier;
        $kodeSupplier = $supplier->kode_supplier;

        //cek sudah ada supplier di pembelian atau belum
        $cekPembelian = Pembelian::where('supplier_id',$id)->count();
        if ($cekPembelian > 0) {
            return response()->json([
                'success' => false,
                'message'   => 'Supplier sudah ada di pembelian, tidak dapat dihapus.'
            ]);
        }

        $supplier->delete();

        $newLog = new Log();
        $newLog->keterangan = 'Menghapus supplier: ' . $namaSupplier . ' (Kode Supplier: ' . $kodeSupplier . ')';
        $newLog->created_by = auth()->id();
        $newLog->created_at = now();
        $newLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil dihapus'
        ]);
    }

    public function generateKode()
    {
        $kodeSupplier = $this->generateKodeSupplier();
        return response()->json([
            'success' => true,
            'data' => ['kode_supplier' => $kodeSupplier]
        ]);
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
            'success' => true,
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
