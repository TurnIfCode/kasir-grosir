# Rencana Perbaikan: Penggantian Tipe Pelanggan

## Informasi yang Dikumpulkan
- Sistem sudah memiliki field tersembunyi `#is_special_customer` untuk pelanggan jenis 'modal'
- Sistem sudah memiliki field tersembunyi `#pelanggan_ongkos` untuk nilai ongkos pelanggan
- Saat ini kode memeriksa nama pelanggan untuk mendeteksi tipe (hardcoded 'kedai kopi', 'hubuan')
- Sistem memiliki field `#pelanggan_id` untuk mendapatkan data pelanggan

## Plan Perbaikan
### 1. Penggantian Variabel
- `isKedaiKopi` → `isModal`
- `isHubuan` → `isAntar`

### 2. Perubahan Logika Deteksi Pelanggan
**Sebelum:**
```javascript
const isKedaiKopi = $('#pelanggan_autocomplete').val().toLowerCase() === 'kedai kopi';
const isHubuan = $('#pelanggan_autocomplete').val().toLowerCase() === 'hubuan';
```

**Sesudah:**
```javascript
const isModal = $('#is_special_customer').val() === '1';
const pelanggan = getSelectedPelanggan();
const isAntar = pelanggan && pelanggan.jenis === 'antar';
```

### 3. Perubahan Rumus Subtotal untuk Pelanggan 'Antar'
**Sebelum:**
```javascript
if (barangInfo.kategori && barangInfo.kategori.toLowerCase() === 'rokok & tembakau' && barangInfo.jenis && barangInfo.jenis.toLowerCase() === 'legal') {
    subtotal = Math.round(qty * (hargaJual + 3000));
}
```

**Sesudah:**
```javascript
if (isAntar && barangInfo.kategori && barangInfo.kategori.toLowerCase() === 'rokok & tembakau' && barangInfo.jenis && barangInfo.jenis.toLowerCase() === 'legal') {
    const ongkos = parseFloat($('#pelanggan_ongkos').val()) || 0;
    subtotal = Math.round(qty * (hargaJual + ongkos));
}
```

### 4. Fungsi Pembantu untuk Data Pelanggan
Tambahkan fungsi untuk mendapatkan data pelanggan yang dipilih.

## File yang akan Diedit
- `public/js/penjualan-form.js`

## Hasil yang Diharapkan
1. Sistem menggunakan tipe pelanggan dari database (jenis 'modal' dan 'antar')
2. Tidak lagi mengandalkan nama pelanggan untuk deteksi tipe
3. Ongkos untuk pelanggan 'antar' diambil dari field database
4. Kode lebih maintainable dan fleksibel
