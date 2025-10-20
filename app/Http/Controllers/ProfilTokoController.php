<?php

namespace App\Http\Controllers;

use App\Models\ProfilToko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilTokoController extends Controller
{
    public function index()
    {
        // Ambil data profil toko pertama atau buat baru jika belum ada
        $profilToko = ProfilToko::first();

        if (!$profilToko) {
            $profilToko = ProfilToko::create([
                'nama_toko' => 'Nama Toko',
                'slogan' => '',
                'alamat' => '',
                'kota' => '',
                'provinsi' => '',
                'kode_pos' => '',
                'no_telp' => '',
                'email' => '',
                'website' => '',
                'npwp' => '',
                'logo' => '',
                'deskripsi' => ''
            ]);
        }

        return view('profil_toko.index', compact('profilToko'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_toko' => 'required|string|max:150',
            'slogan' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'kota' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'kode_pos' => 'nullable|string|max:20',
            'no_telp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'website' => 'nullable|string|max:150',
            'npwp' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'deskripsi' => 'nullable|string'
        ]);

        $profilToko = ProfilToko::findOrFail($id);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Simpan logo ke public/assets/images/logo/logo.png, overwrite jika ada
            $logoPath = public_path('assets/images/logo/logo.png');
            $request->file('logo')->move(dirname($logoPath), 'logo.png');
            $validated['logo'] = 'assets/images/logo/logo.png';
        } else {
            // Jika tidak ada file baru, gunakan logo lama
            unset($validated['logo']);
        }

        $profilToko->update($validated);

        return redirect()->route('profil-toko.index')->with('success', 'Profil toko berhasil diperbarui');
    }
}
