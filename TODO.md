# Penjualan Pricing for Special Customers - Implementation Complete

## Changes Made:
- [x] Modified `HargaService::lookupHarga()` to check if customer is "Kedai Kopi" or "Hubuan" and use `harga_beli` for Kedai Kopi
- [x] Updated `PenjualanController::getHargaByBarangSatuan()` to accept `pelanggan_id` from request
- [x] Updated JavaScript `loadHarga()` function to send `pelanggan_id` in AJAX request
- [x] Added event listener in view to recalculate prices when customer changes
- [x] Updated frontend logic to handle special pricing for Kedai Kopi and Hubuan
- [x] Modified autocomplete logic for Hubuan customer: when searching for 'Rokok & Tembakau', show only items with 'slop' unit from harga_barang table

## Logic:
### For Regular Customers:
- Normal pricing with all markups and surcharges
- Rokok surcharge (+500/+1000) for grosir
- Barang timbangan markup (ceiling to nearest 1000 + 1000)
- Price rounding applied

### For Special Customers ("Kedai Kopi" and "Hubuan"):
- **Kedai Kopi**: Uses `harga_beli` from konversi_satuan table, just `qty * harga_beli` (no additional calculations)
- **Hubuan**:
  - Normal pricing but skips all surcharges and markups
  - If buying Rokok with jenis 'legal': subtotal = qty * (hargaJual + 3000)
  - Unit for Rokok legal should be 'slop' (not 'bungkus')
  - Autocomplete for 'Rokok & Tembakau' shows only items with 'slop' unit
- No price rounding for special customers

## Backend Pricing Logic for Kedai Kopi:
1. `harga_beli` from `konversi_satuan` table if available
2. `harga_beli` from `barang` table if satuan matches
3. Fallback to `barang.harga_beli`

## Autocomplete Logic for Hubuan:
- When customer is 'Hubuan' and search term is 'Rokok & Tembakau', filter results to show only items that have 'slop' unit in harga_barang table
- Updated `BarangController::search()` to accept `pelanggan_id` and apply filtering
- Updated JavaScript `initializeAutocomplete()` to send `pelanggan_id` in AJAX request

## Testing:
- Access http://127.0.0.1:8000/penjualan/create
- Select "Kedai Kopi" as customer: verify exact harga_beli pricing
- Select "Hubuan" as customer: verify normal prices without surcharges/markups and autocomplete shows only 'slop' units for 'Rokok & Tembakau'
- Select other customers: verify all normal pricing logic applies
