# TODO: Hilangkan Harga Jual di Halaman Konversi Satuan Data

## Langkah-langkah:
- [x] Update KonversiSatuanController.php: Hapus kolom 'harga_jual' dari array data di method data()
- [ ] Update resources/views/konversi/index.blade.php: Hapus kolom "Harga Jual" dari tabel header
- [ ] Update resources/views/konversi/index.blade.php: Hapus kolom harga_jual dari DataTable columns
- [ ] Update resources/views/konversi/index.blade.php: Hapus baris "Harga Jual" dari modal detail
