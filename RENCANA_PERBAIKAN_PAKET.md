# RENCANA PERBAIKAN: Logika Pemilihan Paket

## Masalah yang Ditemukan

1. **Logika pemilihan paket salah**: Kode JavaScript mengambil paket dengan harga tertinggi (11000), padahal seharusnya yang diprioritaskan adalah paket dengan jenis 'tidak' dan harga terendah (10000)

2. **Total tidak terhitung**: Ketika menggunakan harga paket, grandSubtotal, grandPembulatan, dan paymentGrandTotal menjadi 0

## Data Paket yang Dimaksud
- Paket Top Ice: jenis "tidak", harga 10000
- Paket Campur: jenis "tidak", harga 11000
- Yang dipilih seharusnya: Paket Top Ice (harga 10000)

## Perbaikan yang Akan Dilakukan

### 1. Perbaiki Logika Pemilihan Paket di `public/js/penjualan-form.js`

**Sebelum (salah):**
```javascript
// Find the paket with highest harga that applies
let selectedPaket = null;
for (const paket of paketInfoCache[barangId]) {
    const totalQty = getTotalQtyForPaket(paket);
    if (totalQty >= paket.total_qty) {
        if (!selectedPaket || paket.harga > selectedPaket.harga) {
            selectedPaket = paket; // ← Ini salah, mengambil harga tertinggi
        }
    }
}
```

**Setelah (benar):**
```javascript
// Find the paket dengan jenis 'tidak' dan harga terendah
let selectedPaket = null;
for (const paket of paketInfoCache[barangId]) {
    const totalQty = getTotalQtyForPaket(paket);
    if (totalQty >= paket.total_qty) {
        if (!selectedPaket) {
            selectedPaket = paket;
        } else {
            // Prioritaskan jenis 'tidak' terlebih dahulu
            if (paket.jenis === 'tidak' && selectedPaket.jenis !== 'tidak') {
                selectedPaket = paket;
            }
            // Jika jenis sama, pilih harga terendah
            else if (paket.jenis === selectedPaket.jenis && parseFloat(paket.harga) < parseFloat(selectedPaket.harga)) {
                selectedPaket = paket;
            }
        }
    }
}
```

### 2. Pastikan Harga Paket Terhitung dengan Benar

Pastikan fungsi `updateRowDisplay()` dan `calculateSubtotal()` dapat menangani harga paket dengan benar.

### 3. Testing

Setelah perbaikan, test dengan:
1. Tambah barang yang masuk paket
2. Pastikan harga yang diambil adalah harga terendah dari jenis 'tidak'
3. Pastikan total terhitung dengan benar

## File yang Akan Diedit

- `public/js/penjualan-form.js` - Perbaiki logika pemilihan paket di fungsi `loadHarga()`

## Status
- [x] Analisis masalah
- [x] Buat rencana perbaikan
- [ ] Implementasi perbaikan
- [ ] Testing
