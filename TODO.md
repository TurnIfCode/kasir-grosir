
# TODO - Perbaikan Query Paket di PenjualanService

## Plan
- [x] Buat helper method `getPaketsByPriority()` di PenjualanService.php
- [x] Method ini akan mengembalikan pakets dengan prioritas jenis 'tidak' > 'campur'
- [x] Ganti kedua query lama dengan pemanggilan helper method baru
- [x] Pastikan logic tetap sama untuk method `determinePaket` (urutkan berdasarkan harga)

## Implementation Steps
1. ✅ Buat helper method `getPaketsByPriority()` 
2. ✅ Update method `updateHargaJualForPaket()` 
3. ✅ Update method `determinePaket()`
4. ✅ Verifikasi implementasi

## Status: COMPLETED ✅

## Summary of Changes
- ✅ Menambahkan helper method `getPaketsByPriority()` yang mengutamakan pencarian berdasarkan jenis 'tidak', jika tidak ada baru jenis 'campur'
- ✅ Method `updateHargaJualForPaket()` menggunakan helper baru tanpa sorting harga
- ✅ Method `determinePaket()` menggunakan helper baru dengan sorting harga untuk maintain existing logic
- ✅ Query lama `$pakets = \App\Models\Paket::with('details')->where('status', 'aktif')->get();` diganti dengan prioritas jenis paket
- ✅ Logic existing tetap dipertahankan untuk backward compatibility
