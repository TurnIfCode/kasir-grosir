<?php

namespace App\Http\Controllers;

//Model
use App\Models\User;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function add()
    {
        return view('user.add');
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search')['value'];

            $query = User::query();

            if (!empty($search)) {
                $query->where('username', 'like', '%' . $search . '%')
                      ->orWhere('name', 'like', '%' . $search . '%')
                      ->orWhere('role', 'like', '%' . $search . '%')
                      ->orWhere('status', 'like', '%' . $search . '%');
            }

            $totalRecords = User::count();
            $filteredRecords = $query->count();

            $users = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($users as $user) {
                $data[] = [
                    'username' => $user->username,
                    'name' => $user->name,
                    'role' => $user->role,
                    'status' => $user->status,
                    'aksi' => '<a id="btnDetail" data-id="' . $user->id . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a> <a id="btnEdit" data-id="' . $user->id . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a> <a data-id="' . $user->id . '" id="btnDelete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>'
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('user.data');
    }

    public function store(Request $request)
    {
        $username   = trim($request->input('username'));
        $name       = trim($request->input('name'));
        $password   = trim($request->input('password'));
        $role       = trim($request->input('role'));
        $status     = trim($request->input('status'));

        if (empty($username)) {
            return response()->json([
                'success'    => false,
                'message'   => 'Username harus diisi'
            ]);
        }

        if (strlen($username) < 3) {
            return response()->json([
                'success'    => false,
                'message'   => 'Username minimal 3 karakter'
            ]);
        }

        //cek username sudah ada atau belum
        $cekUser = User::where('username', $username)->first();
        if ($cekUser) {
            return response()->json([
                'success'    => false,
                'message'   => 'Username sudah terdaftar'
            ]);
        }

        if (empty($name)) {
            return response()->json([
                'status'    => false,
                'message'   => 'Nama harus diisi'
            ]);
        }

        if (empty($password)) {
            return response()->json([
                'success'    => false,
                'message'   => 'Password harus diisi'
            ]);
        }

        if (strlen($password) < 6) {
            return response()->json([
                'success'    => false,
                'message'   => 'Password minimal 6 karakter'
            ]);
        }

        if (empty($role)) {
            return response()->json([
                'success'    => false,
                'message'   => 'Role harus diisi'
            ]);
        }

        if (empty($status)) {
            return response()->json([
                'success'    => false,
                'message'   => 'Status harus diisi'
            ]);
        }

        $user               = new User();
        $user->username     = $username;
        $user->name         = $name;
        $user->password     = bcrypt($password);
        $user->role         = $role;
        $user->status       = $status;
        $user->created_by   = auth()->check() ? auth()->user()->username : null;
        $user->created_at   = now();
        $user->updated_by   = auth()->check() ? auth()->user()->username : null;
        $user->updated_at   = now();
        $user->save();

        return response()->json([
            'success'    => true,
            'message'   => 'User berhasil ditambahkan'
        ]);
    }

    public function find($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success'    => false,
                'message'   => 'User tidak ditemukan'
            ]);
        }

        return response()->json([
            'success'    => true,
            'data'      => $user
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success'    => false,
                'message'   => 'User tidak ditemukan'
            ]);
        }

        $name       = trim($request->input('name'));
        $password   = trim($request->input('password'));
        $role       = trim($request->input('role'));
        $status     = trim($request->input('status'));

        if (empty($name)) {
            return response()->json([
                'status'    => false,
                'message'   => 'Nama harus diisi'
            ]);
        }

        if (!empty($password) && strlen($password) < 6) {
            return response()->json([
                'success'    => false,
                'message'   => 'Password minimal 6 karakter'
            ]);
        }

        if (empty($role)) {
            return response()->json([
                'status'    => false,
                'message'   => 'Role harus diisi'
            ]);
        }

        if (empty($status)) {
            return response()->json([
                'status'    => false,
                'message'   => 'Status harus diisi'
            ]);
        }

        $user->name         = $name;
        if (!empty($password)) {
            $user->password = bcrypt($password);
        }
        $user->role         = $role;
        $user->status       = $status;
        $user->updated_by   = auth()->check() ? auth()->user()->username : null;
        $user->updated_at   = now();
        $user->save();

        return response()->json([
            'success'    => true,
            'message'   => 'User berhasil diperbarui'
        ]);
    }

    public function delete($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success'    => false,
                'message'   => 'User tidak ditemukan'
            ]);
        }

        $user->delete();

        return response()->json([
            'success'    => true,
            'message'   => 'User berhasil dihapus'
        ]);
    }
}
