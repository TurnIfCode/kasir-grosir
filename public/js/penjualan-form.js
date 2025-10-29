let rowIndex = 0;
let paymentIndex = 0;

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
    const rowHtml = `
        <tr class="detail-row" data-index="${rowIndex}">
            <td>
                <input type="text" class="form-control barang-autocomplete" placeholder="Cari barang..." data-index="${rowIndex}" required>
                <input type="hidden" name="details[${rowIndex}][barang_id]" class="barang-id-input" data-index="${rowIndex}">
            </td>
            <td>
                <select class="form-select satuan-select" name="details[${rowIndex}][satuan_id]" data-index="${rowIndex}" disabled required>
                    <option value="">Pilih Satuan</option>
                </select>
            </td>
            <td>
                <select class="form-select tipe-harga-select" name="details[${rowIndex}][tipe_harga]" data-index="${rowIndex}" onchange="onTipeHargaChange(${rowIndex})">
                    <option value="ecer">Ecer</option>
                    <option value="grosir">Grosir</option>
                    <option value="reseller">Reseller</option>
                </select>
            </td>
            <td>
                <input type="number" class="form-control qty-input" name="details[${rowIndex}][qty]" data-index="${rowIndex}" min="0.01" step="0.01" onchange="calculateSubtotal(${rowIndex})" onkeyup="calculateSubtotal(${rowIndex})" required>
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" class="form-control harga-jual-input" name="details[${rowIndex}][harga_jual]" data-index="${rowIndex}" min="0" step="0.01" onchange="calculateSubtotal(${rowIndex})" readonly>
                </div>
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" class="form-control subtotal-input" readonly>
                </div>
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
            loadSatuanOptions(index, ui.item.id);
            return false;
        }
    });
}

function loadSatuanOptions(index, barangId) {
    $.ajax({
        url: '/barang/' + barangId + '/satuan',
        success: function(data) {
            if (data.status === 'success') {
                const select = $(`.satuan-select[data-index="${index}"]`);
                select.empty().append('<option value="">Pilih Satuan</option>');

                data.data.forEach(satuan => {
                    select.append(`<option value="${satuan.satuan_id}">${satuan.nama_satuan}</option>`);
                });

                select.prop('disabled', false);
                select.change(function() {
                    onSatuanChange(index, barangId, $(this).val());
                });
            }
        }
    });
}

function loadTipeHargaOptions(index, barangId, satuanId) {
    $.ajax({
        url: '/harga-barang/get-tipe-harga',
        data: { barang_id: barangId, satuan_id: satuanId },
        success: function(data) {
            if (data.status === 'success') {
                const select = $(`.tipe-harga-select[data-index="${index}"]`);
                select.empty().append('<option value="">Pilih Tipe Harga</option>');

                data.data.forEach(tipe => {
                    select.append(`<option value="${tipe.tipe_harga}">${tipe.tipe_harga}</option>`);
                });

                select.prop('disabled', false);
                select.change(function() {
                    onTipeHargaChange(index);
                });
            }
        }
    });
}

function onSatuanChange(index, barangId, satuanId) {
    loadTipeHargaOptions(index, barangId, satuanId);
}

function onTipeHargaChange(index) {
    const barangId = $(`.barang-id-input[data-index="${index}"]`).val();
    const satuanId = $(`.satuan-select[data-index="${index}"]`).val();
    if (barangId && satuanId) {
        const tipeHarga = $(`.tipe-harga-select[data-index="${index}"]`).val();
        loadHarga(index, barangId, satuanId, tipeHarga);
    }
}

function loadHarga(index, barangId, satuanId, tipeHarga) {
    $.ajax({
        url: '/harga-barang/get-harga',
        data: { barang_id: barangId, satuan_id: satuanId, tipe_harga: tipeHarga },
        success: function(data) {
            if (data.status === 'success') {
                $(`.harga-jual-input[data-index="${index}"]`).val(Math.round(data.data.harga));
                calculateSubtotal(index);
            } else {
                // Fallback to default ecer price if specific tipe not found
                loadDefaultHarga(index, barangId, satuanId);
            }
        },
        error: function() {
            // Fallback to default ecer price if error
            loadDefaultHarga(index, barangId, satuanId);
        }
    });
}

function loadDefaultHarga(index, barangId, satuanId) {
    $.ajax({
        url: `/penjualan/barang/${barangId}/harga/${satuanId}/default`,
        success: function(data) {
            if (data.status === 'success') {
                $(`.harga-jual-input[data-index="${index}"]`).val(Math.round(data.data.harga));
                calculateSubtotal(index);
            }
        }
    });
}

function calculateSubtotal(index) {
    const qty = parseFloat($(`.qty-input[data-index="${index}"]`).val()) || 0;
    const harga = parseFloat($(`.harga-jual-input[data-index="${index}"]`).val()) || 0;
    const subtotal = Math.round(qty * harga);
    $(`.detail-row[data-index="${index}"] .subtotal-input`).val(subtotal);
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    $('.subtotal-input').each(function() {
        total += parseFloat($(this).val()) || 0;
    });

    const diskon = parseFloat($('#diskon').val()) || 0;
    const ppn = parseFloat($('#ppn').val()) || 0;
    const subtotal = total - diskon;
    const grandTotal = subtotal + ppn;

    $('#subtotal').val(total.toLocaleString('id-ID'));
    $('#total').val(total.toLocaleString('id-ID'));
    $('#totalValue').val(total);
    $('#grandSubtotal').val(subtotal.toLocaleString('id-ID'));
    $('#grandTotal').val(grandTotal.toLocaleString('id-ID'));
    $('#grandTotalValue').val(grandTotal);

    calculateKembalian();
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
        $('#kembalian').val(Math.max(0, kembalian));
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

function submitForm() {
    // Validate form
    if (!$('#penjualanForm')[0].checkValidity()) {
        $('#penjualanForm')[0].reportValidity();
        return;
    }

    // Prepare form data
    const formData = new FormData(document.getElementById('penjualanForm'));

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
                    window.location.href = '/penjualan/' + response.data.id;
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
    $('#paymentContainer').html('');
    rowIndex = 0;
    paymentIndex = 0;
    addNewRow();
    calculateTotal();
}
