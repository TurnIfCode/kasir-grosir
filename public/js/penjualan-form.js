let rowIndex = 0;
let paymentIndex = 0;
let barangInfoCache = {}; // Cache for barang information
let paketInfoCache = {}; // Cache for paket data per barang
let calculateTimeout = null; // For debouncing calculations
let isCalculating = false; // Flag to prevent recursion in calculations

$(document).ready(function() {
    // Initialize form
    generateKodePenjualan();
    addNewRow();
    updateRemoveButtons();
});

function generateKodePenjualan() {
    const today = new Date();
    const dateStr = today.getFullYear() +
                   String(today.getMonth() + 1).padStart(2, '0') +
                   String(today.getDate()).padStart(2, '0');
    $('#kode_penjualan').val(`PJ-${dateStr}-001`);
}

function addNewRow() {
    // Desktop table row
    const rowHtml = `
        <tr class="detail-row" data-index="${rowIndex}">
            <td>
                <input type="text" class="form-control barang-autocomplete" placeholder="Cari barang..." data-index="${rowIndex}" required>
                <input type="hidden" name="details[${rowIndex}][barang_id]" class="barang-id-input" data-index="${rowIndex}">
            </td>
            <td>
                <select class="form-control satuan-select" name="details[${rowIndex}][satuan_id]" data-index="${rowIndex}" required onchange="onSatuanChange(${rowIndex})">
                    <option value="">Pilih Satuan</option>
                </select>
            </td>
            <td>
                <select class="form-control tipe-harga-select" name="details[${rowIndex}][tipe_harga]" data-index="${rowIndex}" required onchange="loadHarga(${rowIndex})">
                    <!-- Options will be loaded dynamically -->
                </select>
            </td>
            <td>
                <input type="number" class="form-control qty-input" name="details[${rowIndex}][qty]" data-index="${rowIndex}" min="0.01" step="0.01" onchange="calculateSubtotal(${rowIndex})" onkeyup="calculateSubtotal(${rowIndex})" required>
            </td>
            <td>
                <div class="input-group">
                    <input type="number" class="form-control harga-jual-input" name="details[${rowIndex}][harga_jual]" data-index="${rowIndex}" min="0" step="0.01" readonly>
                </div>
            </td>
            <td>
                <span class="subtotal-text" data-index="${rowIndex}">-</span>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row" onclick="removeRow(${rowIndex})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;

    $('#detailContainer').append(rowHtml);
    initializeAutocomplete(rowIndex);
    rowIndex++;
    updateRemoveButtons();
}

function removeRow(index) {
    // Remove from desktop table
    $(`.detail-row[data-index="${index}"]`).remove();
    calculateTotal();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const rowCount = $('.detail-row').length;
    if (rowCount > 1) {
        $('.remove-row').show();
    } else {
        $('.remove-row').hide();
    }
}




function initializeAutocomplete(index) {
    $(`.barang-autocomplete[data-index="${index}"]`).autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '/barang/search',
                data: { q: request.term, pelanggan_id: $('#pelanggan_id').val() },
                success: function(data) {
                    if (data.success === true) {
                        response(data.data.map(item => ({
                            label: `${item.kode_barang} - ${item.nama_barang}`,
                            value: item.nama_barang,
                            id: item.id
                        })));
                    }
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $(this).val(ui.item.value);
            $(`.barang-id-input[data-index="${index}"]`).val(ui.item.id);
            loadBarangInfo(index, ui.item.id);
            return false;
        }
    });
}

function loadBarangInfo(index, barangId) {
    if (barangInfoCache[barangId]) {
        // Use cached data
        updateBarangInfo(index, barangInfoCache[barangId]);
    } else {
        $.ajax({
            url: '/barang/' + barangId + '/info',
            success: function(data) {
                if (data.success === true) {
                    barangInfoCache[barangId] = data.data;
                    updateBarangInfo(index, data.data);
                }
            }
        });
    }

    // Fetch paket info for the barang
    if (paketInfoCache[barangId]) {
        // Paket info cached, update display after loading
        updateRowDisplay(index);
    } else {
        $.ajax({
            url: '/penjualan/get-paket-barang/' + barangId,
            success: function(data) {
                if (data.success === true) {
                    paketInfoCache[barangId] = data.data;
                    // Update display after paket info is loaded
                    updateRowDisplay(index);
                }
            }
        });
    }
}


function getTotalQtyForPaket(paket) {
    let totalQty = 0;
    $('.detail-row').each(function() {
        const rowIndex = $(this).data('index');
        const barangId = $(`.barang-id-input[data-index="${rowIndex}"]`).val();
        if (paket.barang_ids.includes(parseInt(barangId))) {
            const qty = parseFloat($(`.qty-input[data-index="${rowIndex}"]`).val()) || 0;
            totalQty += qty;
        }
    });
    return totalQty;
}


function isBarangInActivePaket(barangId) {
    console.log(`Checking if barang ${barangId} is in active paket...`); // Debug log
    
    if (!paketInfoCache[barangId] || paketInfoCache[barangId].length === 0) {
        console.log(`No paket cache for barang ${barangId}`); // Debug log
        return false;
    }
    
    // Check if any paket applies (total_qty condition met)
    for (const paket of paketInfoCache[barangId]) {
        const totalQty = getTotalQtyForPaket(paket);
        console.log(`Paket ${paket.nama} (jenis: ${paket.jenis}): totalQty=${totalQty}, required=${paket.total_qty}`); // Debug log
        
        if (totalQty >= paket.total_qty) {
            console.log(`Paket applies for barang ${barangId}:`, paket); // Debug log
            return true; // Barang ini termasuk dalam paket yang aktif
        }
    }
    
    console.log(`No paket applies for barang ${barangId}`); // Debug log
    return false; // Tidak ada paket yang berlaku untuk barang ini
}



function updateAllPaketHarga() {
    console.log('Updating all paket harga...'); // Debug log
    let completed = 0;
    const totalRows = $('.detail-row').length;
    
    $('.detail-row').each(function() {
        const index = $(this).data('index');
        console.log(`Updating row ${index}`); // Debug log
        
        // Modified loadHarga to accept callback
        loadHargaWithCallback(index, true, function() {
            completed++;
            if (completed === totalRows) {
                // All rows updated, now update display for all rows
                updateAllRowDisplays();
            }
        });
    });
    
    // If no rows, update displays immediately
    if (totalRows === 0) {
        updateAllRowDisplays();
    }
}


function loadHargaWithCallback(index, skipCalculation = false, callback = null) {
    const barangId = $(`.barang-id-input[data-index="${index}"]`).val();
    const satuanId = $(`.satuan-select[data-index="${index}"]`).val();
    const tipeHarga = $(`.tipe-harga-select[data-index="${index}"]`).val();
    const pelangganId = $('#pelanggan_id').val();

    if (!barangId || !satuanId || !tipeHarga) {
        if (callback) callback();
        return;
    }

    // Check if barang is in paket and paket condition applies
    if (paketInfoCache[barangId] && paketInfoCache[barangId].length > 0) {
        console.log('Paket info cache for barang', barangId, ':', paketInfoCache[barangId]); // Debug log
        
        // Find the paket dengan prioritas: jenis 'tidak' dulu, lalu harga terendah
        let selectedPaket = null;
        for (const paket of paketInfoCache[barangId]) {
            const totalQty = getTotalQtyForPaket(paket);
            console.log(`Checking paket ${paket.nama} (jenis: ${paket.jenis}, harga: ${paket.harga}, total_qty: ${paket.total_qty}, totalQty: ${totalQty})`); // Debug log
            
            if (totalQty >= paket.total_qty) {
                if (!selectedPaket) {
                    selectedPaket = paket;
                    console.log(`Selected paket (first): ${paket.nama}`); // Debug log
                } else {
                    // Prioritaskan jenis 'tidak' terlebih dahulu
                    if (paket.jenis === 'tidak' && selectedPaket.jenis !== 'tidak') {
                        selectedPaket = paket;
                        console.log(`Selected paket (prioritas jenis): ${paket.nama}`); // Debug log
                    }
                    // Jika jenis sama, pilih harga terendah
                    else if (paket.jenis === selectedPaket.jenis && parseFloat(paket.harga) < parseFloat(selectedPaket.harga)) {
                        selectedPaket = paket;
                        console.log(`Selected paket (harga lebih rendah): ${paket.nama}`); // Debug log
                    }
                }
            }
        }

        console.log('Final selected paket:', selectedPaket); // Debug log

        if (selectedPaket) {
            // Apply paket harga and round to integer - sama dengan backend
            const paketHargaJual = Math.round(selectedPaket.harga / selectedPaket.total_qty);
            console.log(`Applied paket harga: ${paketHargaJual} (${selectedPaket.harga} / ${selectedPaket.total_qty})`); // Debug log
            $(`.harga-jual-input[data-index="${index}"]`).val(paketHargaJual);
            if (!skipCalculation) {
                // Skip calculateSubtotal here to avoid recursion - will be called by updateAllPaketHarga
                updateRowDisplay(index);
            }
            if (callback) callback();
            return;
        }
    }

    console.log('No paket applies, using normal harga calculation'); // Debug log

    // Fallback to normal harga and round to integer
    $.ajax({
        url: `/penjualan/barang/${barangId}/harga/${satuanId}`,
        data: { tipe: tipeHarga, pelanggan_id: pelangganId },
        success: function(data) {
            if (data.success === true) {
                const roundedHarga = Math.round(data.data.harga);
                console.log('Normal harga result:', roundedHarga); // Debug log
                $(`.harga-jual-input[data-index="${index}"]`).val(roundedHarga);
                if (!skipCalculation) {
                    updateRowDisplay(index);
                }
            }
            if (callback) callback();
        },
        error: function() {
            if (callback) callback();
        }
    });
}

function updateAllRowDisplays() {
    console.log('Updating all row displays...'); // Debug log
    $('.detail-row').each(function() {
        const index = $(this).data('index');
        updateRowDisplay(index);
    });
}


function loadHarga(index, skipCalculation = false) {
    const barangId = $(`.barang-id-input[data-index="${index}"]`).val();
    const satuanId = $(`.satuan-select[data-index="${index}"]`).val();
    const tipeHarga = $(`.tipe-harga-select[data-index="${index}"]`).val();
    const pelangganId = $('#pelanggan_id').val();

    if (!barangId || !satuanId || !tipeHarga) {
        return;
    }

    // Check if barang is in paket and paket condition applies
    if (paketInfoCache[barangId] && paketInfoCache[barangId].length > 0) {
        console.log('Paket info cache for barang', barangId, ':', paketInfoCache[barangId]); // Debug log
        
        // Find the paket dengan prioritas: jenis 'tidak' dulu, lalu harga terendah
        let selectedPaket = null;
        for (const paket of paketInfoCache[barangId]) {
            const totalQty = getTotalQtyForPaket(paket);
            console.log(`Checking paket ${paket.nama} (jenis: ${paket.jenis}, harga: ${paket.harga}, total_qty: ${paket.total_qty}, totalQty: ${totalQty})`); // Debug log
            
            if (totalQty >= paket.total_qty) {
                if (!selectedPaket) {
                    selectedPaket = paket;
                    console.log(`Selected paket (first): ${paket.nama}`); // Debug log
                } else {
                    // Prioritaskan jenis 'tidak' terlebih dahulu
                    if (paket.jenis === 'tidak' && selectedPaket.jenis !== 'tidak') {
                        selectedPaket = paket;
                        console.log(`Selected paket (prioritas jenis): ${paket.nama}`); // Debug log
                    }
                    // Jika jenis sama, pilih harga terendah
                    else if (paket.jenis === selectedPaket.jenis && parseFloat(paket.harga) < parseFloat(selectedPaket.harga)) {
                        selectedPaket = paket;
                        console.log(`Selected paket (harga lebih rendah): ${paket.nama}`); // Debug log
                    }
                }
            }
        }

        console.log('Final selected paket:', selectedPaket); // Debug log


        if (selectedPaket) {
            // Apply paket harga and round to integer
            const paketHargaJual = Math.round(selectedPaket.harga / selectedPaket.total_qty);
            console.log(`Applied paket harga: ${paketHargaJual} (${selectedPaket.harga} / ${selectedPaket.total_qty})`); // Debug log
            $(`.harga-jual-input[data-index="${index}"]`).val(paketHargaJual);
            if (!skipCalculation) {
                calculateSubtotal(index);
            } else {
                // Update row display after setting harga
                updateRowDisplay(index);
            }
            return;
        }
    }

    console.log('No paket applies, using normal harga calculation'); // Debug log

    // Fallback to normal harga and round to integer
    $.ajax({
        url: `/penjualan/barang/${barangId}/harga/${satuanId}`,
        data: { tipe: tipeHarga, pelanggan_id: pelangganId },

        success: function(data) {
            if (data.success === true) {
                const roundedHarga = Math.round(data.data.harga);
                console.log('Normal harga result:', roundedHarga); // Debug log
                $(`.harga-jual-input[data-index="${index}"]`).val(roundedHarga);
                if (!skipCalculation) {
                    calculateSubtotal(index);
                } else {
                    // Update row display after setting harga
                    updateRowDisplay(index);
                }
            }
        }
    });
}

function updateBarangInfo(index, barangInfo) {
    // Load satuan options
    loadSatuanOptions(index, barangInfo.id);

    // Set initial qty to 1
    $(`.qty-input[data-index="${index}"]`).val(1);
}

function loadSatuanOptions(index, barangId) {
    $.ajax({
        url: `/penjualan/barang/${barangId}/harga-barang-info`,
        success: function(hargaData) {
            const satuanSelect = $(`.satuan-select[data-index="${index}"]`);
            satuanSelect.empty();
            satuanSelect.append('<option value="">Pilih Satuan</option>');

            if (hargaData.success === true && hargaData.data.length > 0) {
                // Get unique satuan from harga_barang
                let uniqueSatuan = [];
                const seen = new Set();
                hargaData.data.forEach(function(harga) {
                    if (!seen.has(harga.satuan_id)) {
                        seen.add(harga.satuan_id);
                        uniqueSatuan.push({
                            satuan_id: harga.satuan_id,
                            nama_satuan: harga.nama_satuan
                        });
                    }
                });


                // Check if customer is Antar and barang is Rokok & Tembakau
                const pelanggan = getSelectedPelanggan();
                const isAntar = pelanggan && pelanggan.jenis === 'antar';
                const barangInfo = barangInfoCache[barangId];
                const isRokokTembakau = barangInfo && barangInfo.kategori && barangInfo.kategori.toLowerCase() === 'rokok & tembakau';

                if (isAntar && isRokokTembakau) {
                    // Filter to only show 'slop' satuan
                    uniqueSatuan = uniqueSatuan.filter(satuan => satuan.nama_satuan.toLowerCase() === 'slop');
                }

                uniqueSatuan.forEach(function(satuan) {
                    satuanSelect.append(`<option value="${satuan.satuan_id}">${satuan.nama_satuan}</option>`);
                });

                // Auto-select the first satuan and load tipe harga
                if (uniqueSatuan.length > 0) {
                    const selectedSatuanId = uniqueSatuan[0].satuan_id;
                    satuanSelect.val(selectedSatuanId);
                    loadTipeHarga(index, barangId, selectedSatuanId, hargaData.data);
                }
            } else {
                // No harga_barang data, show no options
                satuanSelect.append('<option value="" disabled>Tidak ada satuan tersedia</option>');
            }
        },
        error: function() {
            const satuanSelect = $(`.satuan-select[data-index="${index}"]`);
            satuanSelect.empty();
            satuanSelect.append('<option value="">Pilih Satuan</option>');
            satuanSelect.append('<option value="" disabled>Error loading satuan</option>');
        }
    });
}

function onSatuanChange(index) {
    const barangId = $(`.barang-id-input[data-index="${index}"]`).val();
    const satuanId = $(`.satuan-select[data-index="${index}"]`).val();

    if (!barangId || !satuanId) {
        return;
    }

    // Load tipe harga options based on selected satuan
    loadTipeHarga(index, barangId, satuanId);
}

function loadTipeHarga(index, barangId, satuanId, hargaData = null) {
    const tipeHargaSelect = $(`.tipe-harga-select[data-index="${index}"]`);
    tipeHargaSelect.empty();

    if (hargaData) {
        // Use provided hargaData to find tipe_harga for the satuan
        const matchingHargas = hargaData.filter(h => h.satuan_id == satuanId);
        if (matchingHargas.length > 0) {
            // Get unique tipe_harga
            const uniqueTipes = [...new Set(matchingHargas.map(h => h.tipe_harga))];

            uniqueTipes.forEach(function(tipe) {
                tipeHargaSelect.append(`<option value="${tipe}">${tipe.charAt(0).toUpperCase() + tipe.slice(1)}</option>`);
            });

            // Auto-select the first tipe_harga and load harga
            if (uniqueTipes.length > 0) {
                tipeHargaSelect.val(uniqueTipes[0]);
                loadHarga(index);
            }
        } else {
            // No matching harga for this satuan
            tipeHargaSelect.append('<option value="" disabled>Tidak ada tipe harga tersedia</option>');
        }
    } else {
        // Fallback: fetch tipe_harga via AJAX if hargaData not provided
        $.ajax({
            url: `/penjualan/barang/${barangId}/satuan/${satuanId}/tipe-harga`,
            success: function(data) {
                if (data.success === true) {
                    data.data.forEach(function(tipe) {
                        tipeHargaSelect.append(`<option value="${tipe}">${tipe.charAt(0).toUpperCase() + tipe.slice(1)}</option>`);
                    });

                    // Auto-select the first one and load harga
                    if (data.data.length > 0) {
                        tipeHargaSelect.val(data.data[0]);
                        loadHarga(index);
                    }
                }
            },
            error: function() {
                tipeHargaSelect.append('<option value="" disabled>Error loading tipe harga</option>');
            }
        });
    }
}


function calculateSubtotal(index) {
    // Prevent recursion
    if (isCalculating) {
        return;
    }

    // Clear previous timeout
    if (calculateTimeout) {
        clearTimeout(calculateTimeout);
    }

    // Update paket harga for all rows before calculation
    updateAllPaketHarga();

    // Debounce the calculation by 500ms
    calculateTimeout = setTimeout(function() {
        performCalculation();
    }, 500);
}

function performCalculation() {
    // Set calculating flag
    isCalculating = true;

    // Get all current details
    const details = getAllDetails();

    // If no valid details, set totals to 0
    if (details.length === 0) {
        updateTotals({ subtotal: 0, pembulatan: 0, grand_total: 0 });
        isCalculating = false;
        return;
    }


    // Call backend to calculate
    $.ajax({
        url: '/penjualan/calculate-subtotal',
        method: 'POST',
        data: {
            details: details,
            pelanggan_id: $('#pelanggan_id').val(),
            _token: $('input[name="_token"]').val()
        },
        success: function(response) {
            console.log('Backend response:', response); // Debug log
            if (response.success === true) {
                updateTotals(response.data);
            } else {
                console.error('Backend calculation failed:', response);
                // Fallback: set totals to 0 if backend fails
                updateTotals({ subtotal: 0, pembulatan: 0, grand_total: 0 });
            }
            isCalculating = false;
        },
        error: function(xhr) {
            console.error('Error calculating subtotal:', xhr.responseText);
            // Fallback: set totals to 0 if request fails
            updateTotals({ subtotal: 0, pembulatan: 0, grand_total: 0 });
            isCalculating = false;
        }
    });
}



function roundTotal(total) {
    if (total >= 1 && total <= 499) {
        return 500;
    } else if (total >= 501 && total <= 999) {
        return 1000;
    } else {
        return total;
    }
}



function updateRowDisplay(index) {
    console.log(`Updating row display for index ${index}`); // Debug log
    
    const barangId = $(`.barang-id-input[data-index="${index}"]`).val();
    if (!barangId || !barangInfoCache[barangId]) {
        console.log(`No barang data for row ${index}`); // Debug log
        return;
    }

    const barangInfo = barangInfoCache[barangId];
    const qty = parseFloat($(`.qty-input[data-index="${index}"]`).val()) || 0;
    const tipeHarga = $(`.tipe-harga-select[data-index="${index}"]`).val();
    const satuanId = $(`.satuan-select[data-index="${index}"]`).val();
    const pelangganId = $('#pelanggan_id').val();

    console.log(`Row ${index} data: barang=${barangId}, qty=${qty}, tipe=${tipeHarga}, satuan=${satuanId}`); // Debug log

    // Check if this barang is in an active paket
    const isInActivePaket = isBarangInActivePaket(barangId);
    console.log(`Row ${index} isInActivePaket: ${isInActivePaket}`); // Debug log

    // Check if customer is special (Modal or Antar)
    const isSpecialCustomer = $('#is_special_customer').val() === '1';
    const pelanggan = getSelectedPelanggan();
    const isModal = isSpecialCustomer; // Modal customers are marked with is_special_customer = '1'
    const isAntar = pelanggan && pelanggan.jenis === 'antar';

    let subtotalText = '';
    let keteranganText = '';

    if (!isModal && !isAntar) {
        // Normal logic for regular customers
        if (barangInfo.jenis && barangInfo.jenis.toLowerCase() === 'legal' && tipeHarga === 'grosir' && satuanId == 2) {
            // Logic for LEGAL jenis with grosir tipe_harga and satuan Bungkus (satuan_id = 2)

            const hargaJual = parseFloat($(`.harga-jual-input[data-index="${index}"]`).val()) || 0;
            console.log(`Row ${index} hargaJual: ${hargaJual}`); // Debug log
            
            const baseSubtotal = Math.round(qty * hargaJual);
            let surcharge = 0;

            if (qty >= 1 && qty <= 4) {
                surcharge = 500;
            } else if (qty >= 5) {
                surcharge = 1000;
            }

            keteranganText = 'Rokok Legal Grosir';

            if (surcharge > 0) {

                const total = baseSubtotal + surcharge;
                console.log("Row " + index + " Total before rounding:", total);

                // Only apply pembulatanSubtotal if NOT in active paket
                if (!isInActivePaket) {
                    const roundedTotal = pembulatanSubtotal(total);
                    subtotalText = `${Math.round(roundedTotal).toLocaleString('id-ID')}`;
                } else {
                    // For paket items, use the exact total without rounding
                    subtotalText = `${Math.round(total).toLocaleString('id-ID')}`;
                }

            } else {
                subtotalText = Math.round(baseSubtotal).toLocaleString('id-ID');
            }
        } else {

            const hargaJual = parseFloat($(`.harga-jual-input[data-index="${index}"]`).val()) || 0;
            console.log(`Row ${index} hargaJual: ${hargaJual}`); // Debug log
            
            let subtotal = Math.round(qty * hargaJual);

            // Apply markup for barang timbangan
            if (barangInfo.kategori && barangInfo.kategori.toLowerCase() === 'barang timbangan' && barangInfo.satuan_id == satuanId) {
                const hasilDasar = qty * hargaJual;
                subtotal = Math.ceil(hasilDasar / 1000) * 1000 + 1000;
            }

            // Only apply pembulatanSubtotal if NOT in active paket
            if (!isInActivePaket) {
                const roundedTotal = pembulatanSubtotal(subtotal);
                console.log(`Row ${index} subtotal: ${subtotal}, roundedTotal: ${roundedTotal}`); // Debug log
                subtotalText = Math.round(roundedTotal).toLocaleString('id-ID');
            } else {
                // For paket items, use the exact subtotal without rounding
                console.log(`Row ${index} paket subtotal (no rounding): ${subtotal}`); // Debug log
                subtotalText = Math.round(subtotal).toLocaleString('id-ID');
            }
            
            keteranganText = barangInfo.kategori || '-';
        }

    } else {
        // Special logic for Modal and Antar customers

        if (isModal) {
            // For Modal customers: just qty * harga_beli (already set by backend)
            const hargaBeli = parseFloat($(`.harga-jual-input[data-index="${index}"]`).val()) || 0;
            const subtotal = Math.round(qty * hargaBeli);
            subtotalText = subtotal.toLocaleString('id-ID');
            keteranganText = 'Modal';

        } else if (isAntar) {
            // For Antar customers: special pricing logic
            const hargaJual = parseFloat($(`.harga-jual-input[data-index="${index}"]`).val()) || 0;
            const ongkos = parseFloat($('#pelanggan_ongkos').val()) || 0;
            let subtotal = Math.round(qty * hargaJual);

            // If Rokok with jenis legal, add ongkos to hargaJual
            if (barangInfo.kategori && barangInfo.kategori.toLowerCase() === 'rokok & tembakau' && barangInfo.jenis && barangInfo.jenis.toLowerCase() === 'legal') {
                subtotal = Math.round(qty * (hargaJual + ongkos));
            }

            subtotalText = subtotal.toLocaleString('id-ID');
            keteranganText = barangInfo.kategori || '-';

        } else {
            // For other special customers: normal pricing but skip surcharges and markups
            const hargaJual = parseFloat($(`.harga-jual-input[data-index="${index}"]`).val()) || 0;
            const subtotal = Math.round(qty * hargaJual);
            subtotalText = subtotal.toLocaleString('id-ID');
            keteranganText = barangInfo.kategori || '-';
        }
    }

    console.log(`Row ${index} final subtotalText: ${subtotalText}`); // Debug log

    // Update displays for both desktop and mobile
    $(`.subtotal-text[data-index="${index}"]`).text(subtotalText);
    $(`.keterangan-text[data-index="${index}"]`).text(keteranganText);
}



function pembulatanSubtotal(subtotal) {
    const remainder = Math.round(subtotal % 1000); // Round remainder to nearest integer
    let pembulatan = 0;

    if (remainder === 0) {
        pembulatan = 0;
    } else if (remainder >= 1 && remainder <= 499) {
        // Bulat ke 500
        pembulatan = 500 - remainder;
    } else {
        // remainder >= 500, bulat ke 1000
        pembulatan = 1000 - remainder;
    }

    return subtotal + pembulatan; // Return final total
}

function getAllDetails() {
    const details = [];
    $('.detail-row').each(function() {
        const index = $(this).data('index');
        const barangId = $(`.barang-id-input[data-index="${index}"]`).val();
        const satuanId = $(`.satuan-select[data-index="${index}"]`).val();
        const tipeHarga = $(`.tipe-harga-select[data-index="${index}"]`).val();
        const qty = parseFloat($(`.qty-input[data-index="${index}"]`).val()) || 0;
        const hargaJual = parseFloat($(`.harga-jual-input[data-index="${index}"]`).val()) || 0;

        if (barangId && satuanId && tipeHarga && qty > 0) {
            details.push({
                barang_id: barangId,
                satuan_id: satuanId,
                tipe_harga: tipeHarga,
                qty: qty,
                harga_jual: hargaJual
            });
        }
    });
    return details;
}


/**
 * Fungsi pembulatan harga sesuai aturan:
 * remainder = subtotal % 1000
 * if remainder == 0: pembulatan = 0
 * else if remainder >= 1 && remainder <= 499: pembulatan = 500 - remainder
 * else if remainder >= 500 && remainder <= 999: pembulatan = 1000 - remainder
 */
function calculateRounding(subtotal) {
    const remainder = Math.round(subtotal % 1000);
    let pembulatan = 0;
    let grand_total = subtotal;

    if (remainder === 0) {
        pembulatan = 0;
        grand_total = subtotal;
    } else if (remainder >= 1 && remainder <= 499) {
        pembulatan = 500 - remainder;
        grand_total = subtotal + (500 - remainder);
    } else {
        // remainder >= 500
        pembulatan = 1000 - remainder;
        grand_total = subtotal + (1000 - remainder);
    }

    return { pembulatan, grand_total };
}

// Contoh pemanggilan untuk testing
// console.log(calculateRounding(18100));  // { pembulatan: -100, grand_total: 18000 }
// console.log(calculateRounding(18103));  // { pembulatan: 397, grand_total: 18500 }
// console.log(calculateRounding(293101)); // { pembulatan: 399, grand_total: 293500 }

function parseIndonesianNumber(str) {
    // Remove dots (thousands separators) and replace comma with dot for decimal
    return parseFloat(str.replace(/\./g, '').replace(',', '.')) || 0;
}


function sumSubtotalTexts() {
    let total = 0;
    console.log('Processing subtotal texts:'); // Debug log
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




function updateTotals(data) {
    console.log('Backend calculation result:', data); // Debug backend result
    
    // Use backend calculation results
    const subtotal = Math.round(data.subtotal);
    const pembulatan = Math.round(data.pembulatan);
    const grandTotal = Math.round(data.grand_total);

    console.log('Using backend values:', { subtotal, pembulatan, grandTotal }); // Debug final values

    // Update Ringkasan card (subtotal card)
    $('#subtotal').val(subtotal.toLocaleString('id-ID'));
    $('#pembulatan').val(pembulatan.toLocaleString('id-ID'));
    $('#summaryGrandTotal').val(grandTotal.toLocaleString('id-ID'));

    // Update Total Pembayaran card
    $('#grandSubtotal').val(subtotal.toLocaleString('id-ID'));
    $('#grandPembulatan').val(pembulatan.toLocaleString('id-ID'));
    $('#paymentGrandTotal').val(grandTotal.toLocaleString('id-ID'));
    $('#paymentGrandTotalValue').val(grandTotal);

    calculateKembalian();
}

function calculateTotal() {
    // This function is now called after backend calculation
    // Keep for compatibility but delegate to calculateSubtotal
    if ($('.detail-row').length > 0) {
        calculateSubtotal(0);
    } else {
        updateTotals({ subtotal: 0, pembulatan: 0, grand_total: 0 });
    }
}

function togglePaymentFields() {
    const jenis = $('#jenis_pembayaran').val();
    $('.tunai-field').hide();
    $('#campuranFields').hide();

    if (jenis === 'tunai') {
        $('.tunai-field').show();
    } else if (jenis === 'campuran') {
        $('#campuranFields').show();
    }
}

function calculateKembalian() {
    const jenis = $('#jenis_pembayaran').val();
    if (jenis === 'tunai') {
        const grandTotal = parseFloat($('#paymentGrandTotalValue').val()) || 0;
        const dibayar = parseFloat($('#dibayar').val()) || 0;
        const kembalian = dibayar - grandTotal;
        $('#kembalian').val(Math.max(0, kembalian).toLocaleString('id-ID'));
    }
}

function addPaymentRow() {
    const rowHtml = `
        <div class="row payment-row mb-2" data-index="${paymentIndex}">
            <div class="col-md-4">
                <select class="form-select" name="payments[${paymentIndex}][metode]" required>
                    <option value="">Pilih Metode</option>
                    <option value="tunai">Tunai</option>
                    <option value="transfer">Transfer</option>
                    <option value="qris">QRIS</option>
                    <option value="debit">Debit</option>
                    <option value="kredit">Kredit</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control" name="payments[${paymentIndex}][nominal]" placeholder="Nominal" min="0" step="0.01" required>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="payments[${paymentIndex}][keterangan]" placeholder="Keterangan">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm" onclick="removePaymentRow(${paymentIndex})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;

    $('#paymentContainer').append(rowHtml);
    paymentIndex++;
}

function removePaymentRow(index) {
    $(`.payment-row[data-index="${index}"]`).remove();
}

function submitForm(printAfterSave = false) {
    // Custom validation
    let isValid = true;
    let errorMessage = '';

    // Validate pelanggan
    if (!$('#pelanggan_id').val()) {
        isValid = false;
        errorMessage += 'Pelanggan harus dipilih.\n';
        $('#pelanggan_autocomplete')[0].setCustomValidity('Pelanggan harus dipilih.');
    } else {
        $('#pelanggan_autocomplete')[0].setCustomValidity('');
    }

    // Validate jenis pembayaran
    const jenis = $('#jenis_pembayaran').val();
    if (!jenis) {
        isValid = false;
        errorMessage += 'Jenis pembayaran harus dipilih.\n';
        $('#jenis_pembayaran')[0].setCustomValidity('Jenis pembayaran harus dipilih.');
    } else {
        $('#jenis_pembayaran')[0].setCustomValidity('');
    }

    // Validate dibayar if tunai
    if (jenis === 'tunai' && (!$('#dibayar').val() || parseFloat($('#dibayar').val()) <= 0)) {
        isValid = false;
        errorMessage += 'Nominal bayar harus diisi untuk pembayaran tunai.\n';
        $('#dibayar')[0].setCustomValidity('Nominal bayar harus diisi.');
    } else {
        $('#dibayar')[0].setCustomValidity('');
    }

    // Validate at least one detail
    const detailCount = $('.detail-row').length;
    if (detailCount === 0) {
        isValid = false;
        errorMessage += 'Minimal satu barang harus ditambahkan.\n';
    }

    // Validate each detail row
    $('.detail-row').each(function() {
        const index = $(this).data('index');
        const barangId = $(`.barang-id-input[data-index="${index}"]`).val();
        const satuanId = $(`.satuan-select[data-index="${index}"]`).val();
        const tipeHarga = $(`.tipe-harga-select[data-index="${index}"]`).val();
        const qty = $(`.qty-input[data-index="${index}"]`).val();

        if (!barangId) {
            isValid = false;
            errorMessage += 'Barang harus dipilih untuk semua item.\n';
            $(`.barang-autocomplete[data-index="${index}"]`)[0].setCustomValidity('Barang harus dipilih.');
        } else {
            $(`.barang-autocomplete[data-index="${index}"]`)[0].setCustomValidity('');
        }

        if (!satuanId) {
            isValid = false;
            errorMessage += 'Satuan harus dipilih untuk semua item.\n';
            $(`.satuan-select[data-index="${index}"]`)[0].setCustomValidity('Satuan harus dipilih.');
        } else {
            $(`.satuan-select[data-index="${index}"]`)[0].setCustomValidity('');
        }

        if (!tipeHarga) {
            isValid = false;
            errorMessage += 'Tipe harga harus dipilih untuk semua item.\n';
            $(`.tipe-harga-select[data-index="${index}"]`)[0].setCustomValidity('Tipe harga harus dipilih.');
        } else {
            $(`.tipe-harga-select[data-index="${index}"]`)[0].setCustomValidity('');
        }

        if (!qty || parseFloat(qty) <= 0) {
            isValid = false;
            errorMessage += 'Qty harus diisi dan lebih dari 0 untuk semua item.\n';
            $(`.qty-input[data-index="${index}"]`)[0].setCustomValidity('Qty harus diisi.');
        } else {
            $(`.qty-input[data-index="${index}"]`)[0].setCustomValidity('');
        }
    });

    if (!isValid) {
        Swal.fire('Validasi Gagal', errorMessage, 'error');
        return;
    }

    // Prepare form data
    const formData = new FormData(document.getElementById('penjualanForm'));

    // Collect details from desktop view
    let detailIndex = 0;
    $('.detail-row').each(function() {
        const index = $(this).data('index');
        if (index !== undefined) {
            const barangId = $(`.barang-id-input[data-index="${index}"]`).val();
            const satuanId = $(`.satuan-select[data-index="${index}"]`).val();
            const tipeHarga = $(`.tipe-harga-select[data-index="${index}"]`).val();
            const qty = $(`.qty-input[data-index="${index}"]`).val();
            const hargaJual = $(`.harga-jual-input[data-index="${index}"]`).val();

            if (barangId && satuanId && tipeHarga && qty && hargaJual) {
                formData.append(`details[${detailIndex}][barang_id]`, barangId);
                formData.append(`details[${detailIndex}][satuan_id]`, satuanId);
                formData.append(`details[${detailIndex}][tipe_harga]`, tipeHarga);
                formData.append(`details[${detailIndex}][qty]`, parseFloat(qty));
                formData.append(`details[${detailIndex}][harga_jual]`, parseFloat(hargaJual));
                detailIndex++;
            }
        }
    });

    $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

    $.ajax({
        url: '/penjualan',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,

        success: function(response) {
            console.log('Submit response:', response); // Debug log
            if (response.success === true) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(function() {
                    if (printAfterSave) {
                        // Open print page in new tab
                        window.open('/penjualan/' + response.data.id + '/print', '_blank');
                        // Reload the current create page
                        window.location.reload();
                    } else {
                        window.location.href = '/penjualan/' + response.data.id;
                    }
                });
            } else {
                Swal.fire('Gagal!', response.message || 'Terjadi kesalahan', 'error');
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            let errorMessage = 'Terjadi kesalahan:';
            if (errors) {
                for (const key in errors) {
                    errorMessage += '\n- ' + errors[key][0];
                }
            }
            Swal.fire('Error!', errorMessage, 'error');
        },
        complete: function() {
            $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Penjualan');
        }
    });
}



function resetForm() {
    // Clear any pending calculation timeout
    if (calculateTimeout) {
        clearTimeout(calculateTimeout);
        calculateTimeout = null;
    }

    // Reset calculation flag
    isCalculating = false;

    $('#penjualanForm')[0].reset();
    $('#detailContainer').html('');
    $('#mobileDetailContainer').html('');
    $('#paymentContainer').html('');
    barangInfoCache = {}; // Clear cache
    paketInfoCache = {}; // Clear paket cache
    rowIndex = 0;
    paymentIndex = 0;
    addNewRow();

    calculateTotal();
}


/**
 * Get selected pelanggan data
 */
function getSelectedPelanggan() {
    const pelangganId = $('#pelanggan_id').val();
    if (!pelangganId) return null;
    
    const isModal = $('#is_special_customer').val() === '1';
    const ongkos = parseFloat($('#pelanggan_ongkos').val()) || 0;
    
    // Determine customer type
    let jenis = 'normal';
    if (isModal) {
        jenis = 'modal';
    } else if (ongkos > 0) {
        jenis = 'antar';
    }
    
    return {
        id: pelangganId,
        jenis: jenis,
        ongkos: ongkos
    };
}
