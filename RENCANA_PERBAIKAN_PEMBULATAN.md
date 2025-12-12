# Rencana Perbaikan: Pembulatan Subtotal untuk Barang Paket vs Non-Paket

## Masalah
- `subtotal-text` menunjukkan 3500, seharusnya 3667
- Terjadi karena `pembulatanSubtotal` diterapkan pada semua barang
- Seharusnya `pembulatanSubtotal` hanya berlaku untuk barang yang TIDAK terdaftar di paket

## Analisis Kode
Dari analisis file `public/js/penjualan-form.js`, masalah ada di fungsi `updateRowDisplay()`:

1. **Cache paket**: Data paket di-cache di `paketInfoCache[barangId]`
2. **Logic paket**: Fungsi `getTotalQtyForPaket()` dan `updateAllPaketHarga()` sudah ada
3. **Masalah**: `pembulatanSubtotal()` dipanggil untuk semua barang tanpa memeriksa status paket

## Solusi yang Akan Diterapkan

### 1. Buat Fungsi Pengecek Status Paket
```javascript
function isBarangInActivePaket(barangId) {
    if (!paketInfoCache[barangId] || paketInfoCache[barangId].length === 0) {
        return false;
    }
    
    // Check if any paket applies (total_qty condition met)
    for (const paket of paketInfoCache[barangId]) {
        const totalQty = getTotalQtyForPaket(paket);
        if (totalQty >= paket.total_qty) {
            return true; // Barang ini termasuk dalam paket yang aktif
        }
    }
    
    return false; // Tidak ada paket yang berlaku untuk barang ini
}
```

### 2. Modifikasi Fungsi `updateRowDisplay()`
Ubah logic di fungsi `updateRowDisplay()` untuk:

```javascript
// Untuk barang yang TIDAK masuk paket aktif, apply pembulatanSubtotal
const isInActivePaket = isBarangInActivePaket(barangId);

if (!isInActivePaket) {
    // Apply pembulatanSubtotal hanya untuk barang non-paket
    const roundedTotal = pembulatanSubtotal(subtotal);
    subtotalText = Math.round(roundedTotal).toLocaleString('id-ID');
} else {
    // Untuk barang paket, gunakan subtotal asli tanpa pembulatan
    subtotalText = Math.round(subtotal).toLocaleString('id-ID');
}
```

## Detail Implementasi

### File yang Akan Dimodifikasi
- `public/js/penjualan-form.js`

### Perubahan Spesifik
1. **Tambah fungsi `isBarangInActivePaket()`** - Pengecek apakah barang termasuk paket aktif
2. **Modifikasi `updateRowDisplay()`** - Update logic pembulatan berdasarkan status paket
3. **Update semua pemanggilan `pembulatanSubtotal()`** - Pastikan hanya untuk barang non-paket

### Testing
- Verifikasi barang paket: subtotal tidak di-bulatkan
- Verifikasi barang non-paket: subtotal tetap di-bulatkan
- Pastikan total akhir tetap konsisten

## Estimasi Waktu
- Implementasi: 15-20 menit
- Testing: 10 menit
- Total: 25-30 menit
