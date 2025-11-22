# TODO: Add Logs and Fix Timestamps in Controllers

## Controllers to Update:
- [ ] KategoriController: Add log in update method
- [ ] SatuanController: Add log in store method
- [ ] JenisBarangController: Add log in delete method
- [ ] PelangganController: Add log in update method, fix timestamps in store (set updated_by in create)
- [ ] SupplierController: Add log in update method, fix timestamps in store (set updated_by in create)
- [ ] HargaBarangController: Check and add logs if missing
- [ ] ProfilTokoController: Check update method
- [ ] StokMinimumController: Check store and delete
- [ ] KasController: Check store and update
- [ ] StokOpnameController: Check store
- [ ] Master/PaketController: Check store, update, delete
- [ ] KasSaldoController: Check store and update
- [ ] Transaksi/PembelianController: Check store and update
- [ ] KonversiSatuanController: Check store, update, delete
- [ ] UserController: Check store, update, delete
- [ ] Api/PenjualanApiController: Check store
- [ ] PenjualanController: Check store and update

## Notes:
- Ensure in store: set created_by, created_at, and updated_by = created_by, updated_at = created_at
- In update: set updated_by, updated_at
- Add Log insertion after save in store/update, after delete in delete
- Log keterangan: 'Menambahkan/Memperbarui/Menghapus [entity]: [name] ([code]: [code])'
