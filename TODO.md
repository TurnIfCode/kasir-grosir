
# TODO - Modifikasi Laporan Kas Saldo untuk menggunakan KasSaldoTransaksi

## Plan
- [x] Import KasSaldoTransaksi model di controller
- [x] Modifikasi method index() untuk mendapatkan kas options dari KasSaldoTransaksi
- [x] Modifikasi method data() untuk menggunakan data dari KasSaldoTransaksi
- [x] Modifikasi method getRingkasan() untuk menggunakan data dari KasSaldoTransaksi
- [x] Modifikasi method exportPDF() untuk menggunakan data dari KasSaldoTransaksi
- [x] Hapus method getSaldoAwal() karena saldo akan diambil langsung dari KasSaldoTransaksi

## Status
✅ Completed - Controller sudah dimodifikasi untuk menggunakan KasSaldoTransaksi

## Perubahan yang dilakukan:
1. Import model KasSaldoTransaksi
2. Method index(): Menggunakan KasSaldoTransaksi untuk mendapatkan daftar kas
3. Method data(): Menggunakan KasSaldoTransaksi untuk grouped data dan menghitung saldo masuk/keluar
4. Method getRingkasan(): Menggunakan KasSaldoTransaksi untuk menghitung total saldo
5. Method exportPDF(): Menggunakan KasSaldoTransaksi untuk export PDF
6. Perhitungan saldo masuk/keluar menggunakan selisih saldo_akhir dan saldo_awal
