let rowIndex = 0;
let paymentIndex = 0;
let barangInfoCache = {}; // Cache for barang information
let paketInfoCache = {}; // Cache for paket data per barang

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
                data: { q: request.term },
                success: function(data) {
                    if (data.status === 'success') {
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
                if (data.status === 'success') {
                    barangInfoCache[barangId] = data.data;
                    updateBarangInfo(index, data.data);
                }
            }
        });
    }

    // Fetch paket info for the barang
    if (paketInfoCache[barangId]) {
        // Paket info cached, no action needed here
    } else {
        $.ajax({
            url: '/penjualan/get-paket-barang/' + barangId,
            success: function(data) {
                if (data.status === 'success') {
                    paketInfoCache[barangId] = data.data;
                }
            }
        });
    }
}

function loadHarga(index) {
    const barangId = $(`.barang-id-input[data-index="${index}"]`).val();
    const satuanId = $(`.satuan-select[data-index="${index}"]`).val();
    const tipeHarga = $(`.tipe-harga-select[data-index="${index}"]`).val();

    if (!barangId || !satuanId || !tipeHarga) {
        return;
    }

    $.ajax({
        url: `/penjualan/barang/${barangId}/harga/${satuanId}`,
        data: { tipe: tipeHarga },
        success: function(data) {
            if (data.status === 'success') {
                $(`.harga-jual-input[data-index="${index}"]`).val(data.data.harga);
                calculateSubtotal(index);
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

            if (hargaData.status === 'success' && hargaData.data.length > 0) {
                // Get unique satuan from harga_barang
                const uniqueSatuan = [];
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
                if (data.status === 'success') {
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
    // Update subtotal and keterangan display for desktop only
    updateRowDisplay(index);

    // Get all current details
    const details = getAllDetails();

    // If no valid details, set totals to 0
    if (details.length === 0) {
        updateTotals({ subtotal: 0, pembulatan: 0, grand_total: 0 });
        return;
    }

    // Call backend to calculate
    $.ajax({
        url: '/penjualan/calculate-subtotal',
        method: 'POST',
        data: {
            details: details,
            _token: $('input[name="_token"]').val()
        },
        success: function(response) {
            if (response.status === 'success') {
                updateTotals(response.data);
            }
        },
        error: function(xhr) {
            console.error('Error calculating subtotal:', xhr.responseText);
        }
    });
}

function updateRowDisplay(index) {
    const barangId = $(`.barang-id-input[data-index="${index}"]`).val();
    if (!barangId || !barangInfoCache[barangId]) {
        return;
    }

    const barangInfo = barangInfoCache[barangId];
    const qty = parseFloat($(`.qty-input[data-index="${index}"]`).val()) || 0;
    const tipeHarga = $(`.tipe-harga-select[data-index="${index}"]`).val();
    const satuanId = $(`.satuan-select[data-index="${index}"]`).val();

    let subtotalText = '';
    let keteranganText = '';

    const paketData = paketInfoCache[barangId];

    if (paketData && paketData.length > 0) {
        // Paket harga logic
        const paket = paketData[0]; // Use first paket if multiple

        // Calculate harga_per_unit and harga_per_3 from paket.harga and paket.total_qty dynamically
        const totalQty = paket.total_qty || 1;
        const hargaTotal = paket.harga || paket.harga_per_unit * totalQty || 0; // fallback if old props exist

        const hargaPerUnit = Math.round((hargaTotal / totalQty) * 100) / 100;
        const hargaPer3 = hargaPerUnit * 3;

        let hargaPaket = 0;
        if (qty >= 3) {
            hargaPaket = hargaPer3 / 3;
        } else {
            hargaPaket = hargaPerUnit;
        }

        const subtotal = qty * hargaPaket;
        subtotalText = subtotal.toLocaleString('id-ID');
        keteranganText = `Paket: ${paket.nama_paket}`;
    } else if (barangInfo.kategori && barangInfo.kategori.toLowerCase() === 'rokok' && tipeHarga === 'grosir' && satuanId == 2) {
        // Logic for ROKOK category
        const hargaJual = parseFloat($(`.harga-jual-input[data-index="${index}"]`).val()) || 0;
        const baseSubtotal = qty * hargaJual;
        let surcharge = 0;

        if (qty >= 1 && qty <= 4) {
            surcharge = 500;
            keteranganText = 'Rokok Grosir';
        } else if (qty >= 5) {
            surcharge = 1000;
            keteranganText = 'Rokok Grosir';
        }

        const total = baseSubtotal + surcharge;
        subtotalText = `${baseSubtotal.toLocaleString('id-ID')} + ${surcharge.toLocaleString('id-ID')} = ${total.toLocaleString('id-ID')}`;
    } else {
        const hargaJual = parseFloat($(`.harga-jual-input[data-index="${index}"]`).val()) || 0;
        const subtotal = qty * hargaJual;
        subtotalText = subtotal.toLocaleString('id-ID');
        keteranganText = barangInfo.kategori || '-';
    }

    // Update displays for both desktop and mobile
    $(`.subtotal-text[data-index="${index}"]`).text(subtotalText);
    $(`.keterangan-text[data-index="${index}"]`).text(keteranganText);
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
 * remainder = subtotal % 500
 * if remainder == 0: pembulatan = 0
 * else if remainder <= 100: pembulatan = -remainder  // turun ke kelipatan 500 sebelumnya
 * else: pembulatan = (500 - remainder)  // naik ke kelipatan 500 berikutnya
 */
function calculateRounding(subtotal) {
    const remainder = subtotal % 500;
    let pembulatan = 0;
    let grand_total = subtotal;

    if (remainder === 0) {
        pembulatan = 0;
        grand_total = subtotal;
    } else if (remainder <= 100) {
        pembulatan = -remainder;
        grand_total = subtotal - remainder;
    } else {
        pembulatan = (500 - remainder);
        grand_total = subtotal + (500 - remainder);
    }

    return { pembulatan, grand_total };
}

// Contoh pemanggilan untuk testing
// console.log(calculateRounding(18100));  // { pembulatan: -100, grand_total: 18000 }
// console.log(calculateRounding(18103));  // { pembulatan: 397, grand_total: 18500 }
// console.log(calculateRounding(293101)); // { pembulatan: 399, grand_total: 293500 }

function updateTotals(data) {
    // Round values to integers before formatting
    const subtotal = Math.round(data.subtotal);
    const pembulatan = Math.round(data.pembulatan);
    const grandTotal = Math.round(data.grand_total);

    $('#subtotal').val(subtotal.toLocaleString('id-ID'));
    $('#pembulatan').val(pembulatan.toLocaleString('id-ID'));
    $('#summaryGrandTotal').val(grandTotal.toLocaleString('id-ID'));
    $('#grandTotalValue').val(grandTotal);

    $('#grandSubtotal').val(subtotal.toLocaleString('id-ID'));
    $('#grandPembulatan').val(pembulatan.toLocaleString('id-ID'));
    $('#paymentGrandTotal').val(grandTotal.toLocaleString('id-ID'));

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
        const grandTotal = parseFloat($('#grandTotalValue').val()) || 0;
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
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(function() {
                    if (printAfterSave) {
                        window.location.href = '/penjualan/' + response.data.id + '/print';
                    } else {
                        window.location.href = '/penjualan/' + response.data.id;
                    }
                });
            } else {
                Swal.fire('Gagal!', response.message, 'error');
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
    $('#penjualanForm')[0].reset();
    $('#detailContainer').html('');
    $('#mobileDetailContainer').html('');
    $('#paymentContainer').html('');
    barangInfoCache = {}; // Clear cache
    rowIndex = 0;
    paymentIndex = 0;
    addNewRow();
    calculateTotal();
}
