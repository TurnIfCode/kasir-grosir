# TODO List for Kas Saldo Page Modification

## Completed Tasks
- [x] Update KasSaldoController index method to pass kasSaldos to view
- [x] Replace "Tambah Saldo Kas" button with form select in index.blade.php
- [x] Add JavaScript to send kas_saldo_id parameter in DataTable AJAX request
- [x] Update KasSaldoController data method to filter by kas_saldo_id
- [x] Add change event handler for select to reload DataTable

## Pending Tasks
- [ ] Test the functionality to ensure filtering works correctly
- [ ] Verify that "Semua" shows all transactions and specific kas_saldo_id filters correctly
- [ ] Ensure ordering is by kas_saldo_transaksi_id desc as specified
