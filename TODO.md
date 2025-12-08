# TODO: Integrate Calculation Formula into PenjualanService.php

## Completed Tasks
- [x] Analyze JavaScript calculation logic in penjualan-form.js
- [x] Compare with existing PHP logic in PenjualanService.php
- [x] Identify discrepancy in pembulatan application
- [x] Update calculateNormalPrice method to conditionally apply pembulatan
- [x] Ensure consistency between JS display and PHP database storage

## Summary
The calculateNormalPrice method in PenjualanService.php has been updated to match the JavaScript logic exactly:
- For 'legal' jenis with grosir tipe_harga and satuan_id 2: Apply pembulatan only when surcharge > 0
- For all other items: Always apply pembulatan after adding any applicable surcharges

This ensures that the subtotal values stored in the penjualan_detail table match what is displayed in the JavaScript form, maintaining consistency across the application.
