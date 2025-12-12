# PLAN: Implementasi PembulatanSubtotal dengan Kondisi Paket

## Informasi yang Dikumpulkan:
- Method `calculatePembulatan()` sudah ada dengan rumus yang benar
- Method `hasBarangInPaket()` sudah ada untuk memeriksa barang dalam paket
- Method `calculateSubtotalAndPembulatan()` perlu dimodifikasi
- Method `calculateNormalPrice()` juga perlu disesuaikan

## Plan Implementasi:

### 1. Modifikasi Method calculateSubtotalAndPembulatan()
- Tambahkan pengecekan `hasBarangInPaket()` di awal method
- Jika ada barang dalam paket: set pembulatan = 0, skip perhitungan pembulatan
- Jika tidak ada barang dalam paket: lakukan perhitungan pembulatan normal

### 2. Update calculateNormalPrice()
- Sesuaikan logic untuk tidak melakukan pembulatan jika barang ada di paket
- Konsistensi dengan calculateSubtotalAndPembulatan()

### 3. Testing
- Test dengan barang yang terdaftar di paket (pembulatan = 0)
- Test dengan barang yang tidak terdaftar di paket (pembulatan sesuai rumus)

## Dependent Files:
- app/Services/PenjualanService.php (file utama yang diedit)

## Followup Steps:
- Test manual dengan skenario berbeda
- Verifikasi hasil melalui browser testing jika diperlukan
