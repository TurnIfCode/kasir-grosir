# RENCANA PERBAIKAN: grandSubtotal, grandPembulatan, paymentGrandTotal = 0 untuk Harga Paket

## Masalah yang Ditemukan

### 1. Logika Pemilihan Paket Salah
- JavaScript mengambil paket dengan harga **tertinggi** (11000)
- Seharusnya prioritas: **jenis 'tidak'** terlebih dahulu, lalu harga **terendah** (10000)

### 2. Total Tidak Terhitung
- Ketika menggunakan harga paket, `grandSubtotal`, `grandPembulatan`, `paymentGrandTotal` menjadi **0**
- Kemungkinan penyebab:
  - Fungsi `isBarangInActivePaket()` tidak bekerja dengan benar
  - Harga paket tidak ter-refresh untuk semua baris
  - Perhitungan `sumSubtotalTexts()` tidak menangkap harga paket dengan benar

## Data Paket yang Dimaksud
- **Paket Top Ice**: jenis "tidak", harga 10000, total_qty = 2
- **Paket Campur**: jenis "tidak", harga 11000, total_qty = 2  
- **Yang dipilih seharusnya**: Paket Top Ice (harga 10000)

## Perbaikan yang Akan Dilakukan

### 1. Perbaiki Logika Pemilihan Paket di `public/js/penjualan-form.js`

**Fungsi `loadHarga()` - BEFORE (salah):**
```javascript
// Find the paket with highest harga that applies
let selectedPaket = null;
for (const paket of paketInfoCache[barangId]) {
    const totalQty = getTotalQtyForPaket(paket);
    if (totalQty >= paket.total_qty) {
        if (!selectedPaket || paket.harga > selectedPaket.harga) {
            selectedPaket = paket; // ← Sini masalahnya, ambil harga tertinggi
        }
    }
}
```

**Fungsi `loadHarga()` - AFTER (benar):**
```javascript
// Find the paket dengan prioritas: jenis 'tidak' dulu, lalu harga terendah
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

### 2. Perbaiki Fungsi `isBarangInActivePaket()`

**BEFORE:**
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

**AFTER:**
```javascript
function isBarangInActivePaket(barangId) {
    if (!paketInfoCache[barangId] || paketInfoCache[barangId].length === 0) {
        return false;
    }
    
    // Check if any paket applies (total_qty condition met)
    for (const paket of paketInfoCache[barangId]) {
        const totalQty = getTotalQtyForPaket(paket);
        if (totalQty >= paket.total_qty) {
            console.log(`Paket applies for barang ${barangId}:`, paket); // Debug log
            return true; // Barang ini termasuk dalam paket yang aktif
        }
    }
    
    console.log(`No paket applies for barang ${barangId}`); // Debug log
    return false; // Tidak ada paket yang berlaku untuk barang ini
}
```

### 3. Debug dan Perbaiki Perhitungan Total

**Tambahkan debug logging di `updateTotals()`:**
```javascript
function updateTotals(data) {
    console.log('Backend calculation result:', data); // Debug backend result
    
    // Round values to integers before formatting
    const subtotal = Math.round(data.subtotal);
    const pembulatan = Math.round(data.pembulatan);
    const grandTotal = Math.round(data.grand_total);

    // Calculate grandSubtotal as sum of subtotal-text values
    const grandSubtotal = sumSubtotalTexts();
    console.log('Frontend grandSubtotal calculation:', grandSubtotal); // Debug frontend calculation

    // Calculate grandPembulatan based on grandSubtotal value
    const grandPembulatanData = calculateRounding(grandSubtotal);
    const grandPembulatan = grandPembulatanData.pembulatan;
    const paymentGrandTotal = grandPembulatanData.grand_total;

    console.log('Final totals:', { grandSubtotal, grandPembulatan, paymentGrandTotal }); // Debug final result

    // Update Ringkasan card to match Total Pembayaran card
    $('#subtotal').val(grandSubtotal.toLocaleString('id-ID'));
    $('#pembulatan').val(grandPembulatan.toLocaleString('id-ID'));
    $('#summaryGrandTotal').val(paymentGrandTotal.toLocaleString('id-ID'));
    $('#grandTotalValue').val(paymentGrandTotal);

    $('#grandSubtotal').val(grandSubtotal.toLocaleString('id-ID'));
    $('#grandPembulatan').val(grandPembulatan.toLocaleString('id-ID'));
    $('#paymentGrandTotal').val(paymentGrandTotal.toLocaleString('id-ID'));
    $('#paymentGrandTotalValue').val(paymentGrandTotal);

    calculateKembalian();
}
```

**Tambahkan debug logging di `sumSubtotalTexts()`:**
```javascript
function sumSubtotalTexts() {
    let total = 0;
    console.log('Processing subtotal texts:'); // Debug
    $('.subtotal-text').each(function() {
        const text = $(this).text().trim();
        const index = $(this).data('index');
        console.log(`Row ${index}: "${text}"`); // Debug each row
        if (text && text !== '-') {
            const parsed = parseIndonesianNumber(text);
            console.log(`Row ${index}: parsed value = ${parsed}`); // Debug parsed value
            total += parsed;
        }
    });
    console.log('Total calculated:', total); // Debug final total
    return total;
}
```

### 4. Pastikan Harga Paket Ter-refresh dengan Benar

**Perbaiki fungsi `updateAllPaketHarga()`:**
```javascript
function updateAllPaketHarga() {
    console.log('Updating all paket harga...'); // Debug
    $('.detail-row').each(function() {
        const index = $(this).data('index');
        console.log(`Updating row ${index}`); // Debug
        loadHarga(index, true);
    });
}
```

### 5. Testing dan Validasi

Setelah perbaikan, test dengan:
1. Tambah barang yang masuk paket (contoh: 2 barang dari paket Top Ice)
2. Pastikan harga yang diambil adalah **harga terendah dari jenis 'tidak'** (10000)
3. Pastikan **total terhitung dengan benar** (tidak 0)
4. Check console log untuk debug information

## File yang Akan Diedit

- `public/js/penjualan-form.js` - Perbaiki semua fungsi terkait paket dan perhitungan total

## Status
- [x] Analisis masalah
- [x] Buat rencana perbaikan
- [ ] Implementasi perbaikan
- [ ] Testing dengan transaksi paket
- [ ] Verifikasi total terhitung dengan benar

## Success Criteria
1. ✅ Paket dengan jenis 'tidak' dan harga terendah yang dipilih
2. ✅ grandSubtotal, grandPembulatan, paymentGrandTotal terhitung dengan benar (tidak 0)
3. ✅ Console debug log menunjukkan proses perhitungan yang benar
