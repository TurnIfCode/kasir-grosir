# TODO: Fix Paket Pricing Logic in PenjualanService.php

## Tasks
- [x] Modify paket selection in calculateSubtotalWithPaket to pick the lowest harga paket per total_qty group instead of highest.
- [x] Change bestPaket selection to sort by ascending harga (lowest first) instead of descending.

## Details
- File: app/Services/PenjualanService.php
- Method: calculateSubtotalWithPaket
- Change: In groupedPakets, select $group->sortBy('harga')->first() instead of sortByDesc('harga')->first()
- Change: In applicablePakets, select $applicablePakets->sortBy('harga')->first() instead of sortByDesc('harga')->first()

## Followup
- Test the logic with the example: selling Top Ice Wafer Keju, Cokelat, Strawberry each qty 1 (total qty 3) should use paket price.
