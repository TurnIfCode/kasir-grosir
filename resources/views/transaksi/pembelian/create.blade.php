@include('layout.header')

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h2 fw-bold text-primary mb-0">Tambah Pembelian</h1>
                <a href="{{ route('pembelian.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Left Column: Form -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form id="pembelianForm">
                        @csrf

                        <!-- Informasi Pembelian -->
                        <div class="mb-4">
                            <h5 class="card-title fw-semibold mb-3">Informasi Pembelian</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="supplier_autocomplete" class="form-label fw-medium">Supplier <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="supplier_autocomplete" name="supplier_nama" placeholder="Ketik nama supplier" required>
                                    <input type="hidden" id="supplier_id" name="supplier_id" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="tanggal_pembelian" class="form-label fw-medium">Tanggal Pembelian <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tanggal_pembelian" name="tanggal_pembelian" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="diskon" class="form-label fw-medium">Diskon</label>
                                    <input type="number" class="form-control" id="diskon" name="diskon" value="0" min="0" step="0.01">
                                </div>
                                <div class="col-md-6">
                                    <label for="ppn" class="form-label fw-medium">PPN</label>
                                    <input type="number" class="form-control" id="ppn" name="ppn" value="0" min="0" step="0.01">
                                </div>
                                <div class="col-12">
                                    <label for="catatan" class="form-label fw-medium">Catatan</label>
                                    <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Barang -->
                        <div class="mb-4">
                            <!-- Default form row -->
                            <div class="detail-row mb-3 border rounded p-3 bg-light" id="defaultRow">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium">Barang <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control barang-autocomplete" name="details[0][barang_nama]" placeholder="Ketik nama atau kode barang">
                                        <input type="hidden" class="barang-id" name="details[0][barang_id]">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-medium">Satuan <span class="text-danger">*</span></label>
                                        <select class="form-select satuan-select" name="details[0][satuan_id]" disabled>
                                            <option value="">Pilih Satuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-medium">Qty <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control qty-input" name="details[0][qty]" min="0.01" step="0.01">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-medium">Harga Beli <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control harga-input" name="details[0][harga_beli]" min="0" step="1">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-medium">Subtotal</label>
                                        <input type="number" class="form-control subtotal-input fw-semibold" step="any" readonly>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-success btn-sm add-to-list-btn w-100">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <label class="form-label fw-medium">Pilih Kas</label>
                                        <input type="text" class="form-control" name="details[0][keterangan]" placeholder="Tambahkan keterangan jika diperlukan">
                                    </div>
                                </div>
                            </div>

                            <!-- List of added items -->
                            <div id="addedItemsContainer" style="display: none;">
                                <h6 class="fw-semibold mb-3 text-primary">
                                    <i class="fas fa-list me-2"></i>Barang yang Ditambahkan
                                </h6>
                                <div id="addedItemsList">
                                    <!-- Added items will appear here -->
                                </div>
                            </div>
                        </div>

                        <!-- Pembayaran -->
                        <div class="mb-4">
                            <h5 class="card-title fw-semibold mb-3">Pembayaran</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="metode_pembayaran" class="form-label fw-medium">Metode Pembayaran <span class="text-danger">*</span></label>
                                    <select class="form-select" id="metode_pembayaran" name="metode_pembayaran">
                                        <option value="">Pilih Metode</option>
                                        <option value="tunai">Tunai</option>
                                        <option value="transfer">Transfer</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="nominal_pembayaran" class="form-label fw-medium">Nominal <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="nominal_pembayaran" name="nominal_pembayaran" min="0" step="0.01">
                                </div>
                                <div class="col-md-4" id="keterangan_pembayaran_container" style="display: none;">
                                    <label for="keterangan_pembayaran" class="form-label fw-medium">Keterangan</label>
                                    <select class="form-select" id="keterangan_pembayaran" name="keterangan_pembayaran">
                                        <option value="">Pilih Keterangan</option>
                                        @foreach($kasSaldo as $kas)
                                            <option value="{{ $kas->kas }}">{{ $kas->kas }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-success btn-sm" id="addPembayaranBtn">
                                    <i class="fas fa-plus me-1"></i>Tambah Pembayaran
                                </button>
                            </div>
                            <div id="pembayaranList" class="mt-3" style="display: none;">
                                <h6 class="fw-semibold mb-3 text-primary">
                                    <i class="fas fa-credit-card me-2"></i>Pembayaran yang Ditambahkan
                                </h6>
                                <div id="pembayaranItems"></div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo me-1"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-1"></i>Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Ringkasan -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-body p-4">
                    <h5 class="card-title fw-semibold mb-3">Ringkasan</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span id="totalSubtotal" class="fw-medium">Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Diskon:</span>
                        <span id="totalDiskon" class="fw-medium">Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">PPN:</span>
                        <span id="totalPpn" class="fw-medium">Rp 0</span>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold fs-5">Total:</span>
                        <span id="totalAkhir" class="fw-bold fs-5 text-primary">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let rowIndex = 1;

$(document).ready(function() {
    // Supplier autocomplete
    $('#supplier_autocomplete').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '{{ route("supplier.search") }}',
                dataType: 'json',
                data: { q: request.term },
                success: function(data) {
                    if (data.status === 'success') {
                        response($.map(data.data, function(item) {
                            return {
                                label: item.nama_supplier,
                                value: item.nama_supplier,
                                id: item.id
                            };
                        }));
                    }
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $('#supplier_id').val(ui.item.id);
            $('#supplier_autocomplete').val(ui.item.value);
            return false;
        }
    });

    // Add new row (disabled - now using individual + buttons)
    $('#addRowBtn').click(function() {
        // addNewRow();
    });

    // Add to list button
    $(document).on('click', '.add-to-list-btn', function() {
        var rowElement = $(this).closest('.detail-row');
        addToList(rowElement);
    });

    // Remove from list
    $(document).on('click', '.remove-from-list-btn', function() {
        var itemElement = $(this).closest('.added-item');
        removeFromList(itemElement);
    });

    // Update save button when barang or satuan changes
    $(document).on('change', '.barang-autocomplete, .satuan-select', function() {
        updateRemoveButtons();
    });

    // Calculate subtotal when qty or harga changes
    $(document).on('input', '.qty-input, .harga-input', function() {
        calculateSubtotal($(this).closest('.detail-row'));
        calculateTotal();
        updateRemoveButtons(); // Update save button state
    });

    // Prevent decimal input in harga-input fields
    $(document).on('keypress', '.harga-input', function(e) {
        // Allow only numbers, backspace, delete, tab, escape, enter, and arrow keys
        if (e.which != 8 && e.which != 0 && e.which != 9 && e.which != 27 && e.which != 13 && e.which != 37 && e.which != 38 && e.which != 39 && e.which != 40 && (e.which < 48 || e.which > 57)) {
            return false;
        }
    });

    // Autocomplete barang
    $(document).on('focus', '.barang-autocomplete', function() {
        $(this).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '{{ route("barang.search") }}',
                    dataType: 'json',
                    data: { q: request.term },
                    success: function(data) {
                        if (data.status === 'success') {
                            response($.map(data.data, function(item) {
                                return {
                                    label: item.kode_barang + ' - ' + item.nama_barang + (item.barcode ? ' (' + item.barcode + ')' : ''),
                                    value: item.nama_barang,
                                    id: item.id,
                                    barcode: item.barcode
                                };
                            }));
                        }
                    }
                });
            },
            minLength: 1, // Allow single character for barcode scanning
            select: function(event, ui) {
                var row = $(this).closest('.detail-row');
                row.find('.barang-id').val(ui.item.id);
                row.find('.barang-autocomplete').val(ui.item.value);

                // Load satuan untuk barang ini
                loadSatuan(row, ui.item.id);

                return false;
            }
        });
    });

    // Handle barcode scanning - auto trigger search on enter
    $(document).on('keypress', '.barang-autocomplete', function(e) {
        if (e.which === 13) { // Enter key
            var input = $(this);
            var term = input.val().trim();

            if (term.length > 0) {
                // Search for exact barcode match first
                $.ajax({
                    url: '{{ route("barang.search") }}',
                    dataType: 'json',
                    data: { q: term },
                    success: function(data) {
                        if (data.status === 'success' && data.data.length > 0) {
                            // Find exact barcode match
                            var exactMatch = data.data.find(function(item) {
                                return item.barcode === term || item.kode_barang === term;
                            });

                            if (exactMatch) {
                                var row = input.closest('.detail-row');
                                row.find('.barang-id').val(exactMatch.id);
                                row.find('.barang-autocomplete').val(exactMatch.nama_barang);

                                // Load satuan untuk barang ini
                                loadSatuan(row, exactMatch.id);
                            } else if (data.data.length === 1) {
                                // If only one result, auto-select it
                                var row = input.closest('.detail-row');
                                row.find('.barang-id').val(data.data[0].id);
                                row.find('.barang-autocomplete').val(data.data[0].nama_barang);

                                // Load satuan untuk barang ini
                                loadSatuan(row, data.data[0].id);
                            }
                        }
                    }
                });
            }
        }
    });

    // Load satuan berdasarkan barang
    function loadSatuan(row, barangId) {
        $.ajax({
            url: '{{ route("barang.satuan", ":id") }}'.replace(':id', barangId),
            type: 'GET',
            success: function(data) {
                if (data.status === 'success') {
                    var satuanSelect = row.find('.satuan-select');
                    satuanSelect.empty().append('<option value="">Pilih Satuan</option>');

                    $.each(data.data, function(index, satuan) {
                        satuanSelect.append('<option value="' + satuan.satuan_id + '" data-harga="' + satuan.harga_beli + '">' + satuan.nama_satuan + '</option>');
                    });

                    satuanSelect.prop('disabled', false);

                    // Auto-select first satuan if available
                    if (data.data.length > 0) {
                        satuanSelect.val(data.data[0].satuan_id);
                        // Auto-fill harga if available
                        if (data.data[0].harga_beli > 0) {
                            row.find('.harga-input').val(data.data[0].harga_beli);
                            calculateSubtotal(row);
                            calculateTotal();
                        }
                    }
                }
            }
        });
    }

    // Update harga when satuan changes
    $(document).on('change', '.satuan-select', function() {
        var row = $(this).closest('.detail-row');
        var selectedOption = $(this).find('option:selected');
        var harga = selectedOption.data('harga');

        if (harga !== undefined && harga > 0) {
            row.find('.harga-input').val(harga);
        } else {
            // Jika tidak ada harga default, kosongkan field harga
            row.find('.harga-input').val('');
        }

        calculateSubtotal(row);
        calculateTotal();
    });

    // Update diskon and ppn
    $('#diskon, #ppn').on('input', function() {
        calculateTotal();
    });

    // Show/hide keterangan_pembayaran based on metode_pembayaran
    $('#metode_pembayaran').on('change', function() {
        var metode = $(this).val();
        if (metode === 'transfer') {
            $('#keterangan_pembayaran_container').show();
        } else {
            $('#keterangan_pembayaran_container').hide();
            $('#keterangan_pembayaran').val(''); // Clear the value when hidden
        }
    });

    // Add pembayaran to list
    $('#addPembayaranBtn').click(function() {
        addPembayaranToList();
    });

    // Remove pembayaran from list
    $(document).on('click', '.remove-pembayaran-btn', function() {
        removePembayaranFromList($(this).closest('.pembayaran-item'));
    });

    // Form validation and submit
    $('#pembelianForm').validate({
        rules: {
            supplier_id: 'required',
            tanggal_pembelian: 'required'
        },
        messages: {
            supplier_id: 'Supplier harus dipilih',
            tanggal_pembelian: 'Tanggal pembelian harus diisi'
        },
        submitHandler: function(form) {
            // Check if there are items in the list
            var hasItems = $('.added-item').length > 0;

            if (hasItems) {
                // If there are items in the list, save directly without validation
                submitForm();
            } else {
                // If no items in list, validate the form fields
                var isValid = true;
                var errorMessages = [];

                // Check supplier
                if (!$('#supplier_id').val()) {
                    isValid = false;
                    errorMessages.push('Supplier harus dipilih');
                }

                // Check tanggal
                if (!$('#tanggal_pembelian').val()) {
                    isValid = false;
                    errorMessages.push('Tanggal pembelian harus diisi');
                }

                // Check if there's at least one item in the form
                var hasFormItem = false;
                $('.detail-row').each(function(index) {
                    var row = $(this);
                    var barangId = row.find('.barang-id').val();
                    var satuanId = row.find('.satuan-select').val();
                    var qty = parseFloat(row.find('.qty-input').val());
                    var harga = parseFloat(row.find('.harga-input').val());

                    if (barangId && satuanId && qty > 0 && harga >= 0) {
                        hasFormItem = true;
                        return false; // break loop
                    }
                });

                if (!hasFormItem) {
                    isValid = false;
                    errorMessages.push('Minimal harus ada satu barang yang diisi lengkap');
                }

                if (!isValid) {
                    Swal.fire('Validasi Error!', errorMessages.join('<br>'), 'error');
                    return false;
                }

                submitForm();
            }
        }
    });
});

function addNewRow() {
    var newRow = `
        <div class="detail-row mb-3 border rounded p-3 bg-light">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-medium">Barang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control barang-autocomplete" name="details[${rowIndex}][barang_nama]" placeholder="Ketik nama atau kode barang" required>
                    <input type="hidden" class="barang-id" name="details[${rowIndex}][barang_id]" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-medium">Satuan <span class="text-danger">*</span></label>
                    <select class="form-select satuan-select" name="details[${rowIndex}][satuan_id]" required disabled>
                        <option value="">Pilih Satuan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-medium">Qty <span class="text-danger">*</span></label>
                    <input type="number" class="form-control qty-input" name="details[${rowIndex}][qty]" min="0.01" step="0.01" required>
                </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-medium">Harga Beli <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control harga-input" name="details[${rowIndex}][harga_beli]" min="0" step="1" required>
                                    </div>
                <div class="col-md-2">
                    <label class="form-label fw-medium">Subtotal</label>
                    <input type="number" class="form-control subtotal-input fw-semibold" step="any" readonly>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-success btn-sm add-to-list-btn w-100">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <label class="form-label fw-medium">Keterangan</label>
                    <input type="text" class="form-control" name="details[${rowIndex}][keterangan]" placeholder="Tambahkan keterangan jika diperlukan">
                </div>
            </div>
        </div>
    `;
    // Insert new row after the default row (at the top)
    $('#defaultRow').after(newRow);
    rowIndex++;
    updateRemoveButtons();
}

function addToList(rowElement) {
    var barangNama = rowElement.find('.barang-autocomplete').val();
    var barangId = rowElement.find('.barang-id').val();
    var satuanText = rowElement.find('.satuan-select option:selected').text();
    var satuanId = rowElement.find('.satuan-select').val();
    var qty = rowElement.find('.qty-input').val();
    var harga = rowElement.find('.harga-input').val();
    var subtotal = rowElement.find('.subtotal-input').val();
    var keterangan = rowElement.find('input[name*="[keterangan]"]').val();

    // Validate required fields
    if (!barangId || !satuanId || !qty || harga === '') {
        Swal.fire('Error!', 'Harap lengkapi semua field yang diperlukan sebelum menambah ke daftar.', 'error');
        return;
    }

    // Create list item with data attributes for saving
    var listItem = `
        <div class="added-item card mb-2 border-left-primary"
             data-barang-id="${barangId}"
             data-satuan-id="${satuanId}"
             data-qty="${qty}"
             data-harga-beli="${harga}"
             data-keterangan="${keterangan || ''}"
             data-subtotal="${subtotal}">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <strong class="text-primary">${barangNama}</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted">${satuanText}</small>
                    </div>
                    <div class="col-md-1">
                        <span class="badge bg-secondary">${qty}</span>
                    </div>
                    <div class="col-md-2">
                        <span class="fw-medium">Rp ${parseFloat(harga).toLocaleString('id-ID')}</span>
                    </div>
                    <div class="col-md-2">
                        <span class="fw-bold text-success">Rp ${parseFloat(subtotal).toLocaleString('id-ID')}</span>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-from-list-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                ${keterangan ? `<div class="row mt-2"><div class="col-12"><small class="text-muted"><i class="fas fa-sticky-note me-1"></i>${keterangan}</small></div></div>` : ''}
            </div>
        </div>
    `;

    // Add to list
    $('#addedItemsList').append(listItem);
    $('#addedItemsContainer').show();

    // Clear the form row
    rowElement.find('.barang-autocomplete').val('');
    rowElement.find('.barang-id').val('');
    rowElement.find('.satuan-select').html('<option value="">Pilih Satuan</option>').prop('disabled', true);
    rowElement.find('.qty-input').val('');
    rowElement.find('.harga-input').val('');
    rowElement.find('.subtotal-input').val('');
    rowElement.find('input[name*="[keterangan]"]').val('');

    // Update calculations
    calculateTotal();
    updateRemoveButtons();
}

function removeFromList(itemElement) {
    itemElement.remove();

    // Hide container if no items left
    if ($('#addedItemsList').children().length === 0) {
        $('#addedItemsContainer').hide();
    }

    // Update calculations
    calculateTotal();
    updateRemoveButtons();
}

function calculateSubtotal(row) {
    var qty = parseFloat(row.find('.qty-input').val()) || 0;
    var harga = parseFloat(row.find('.harga-input').val()) || 0;
    var subtotal = qty * harga;
    row.find('.subtotal-input').val(Math.round(subtotal));
}

function calculateTotal() {
    var totalSubtotal = 0;
    // Calculate from added items list using data-subtotal attribute
    $('.added-item').each(function() {
        var subtotal = parseFloat($(this).data('subtotal')) || 0;
        totalSubtotal += subtotal;
    });

    var diskon = parseFloat($('#diskon').val()) || 0;
    var ppn = parseFloat($('#ppn').val()) || 0;
    var totalAkhir = totalSubtotal - diskon + ppn;

    $('#totalSubtotal').text('Rp ' + totalSubtotal.toLocaleString('id-ID', {maximumFractionDigits: 2}));
    $('#totalDiskon').text('Rp ' + diskon.toLocaleString('id-ID', {maximumFractionDigits: 2}));
    $('#totalPpn').text('Rp ' + ppn.toLocaleString('id-ID', {maximumFractionDigits: 2}));
    $('#totalAkhir').text('Rp ' + totalAkhir.toLocaleString('id-ID', {maximumFractionDigits: 2}));
}

function updateRemoveButtons() {
    // Save button is always enabled - validation happens on submit
    $('#submitBtn').prop('disabled', false);
}

function addPembayaranToList() {
    var metode = $('#metode_pembayaran').val();
    var nominal = parseFloat($('#nominal_pembayaran').val());
    var keterangan = $('#keterangan_pembayaran').val();

    // Validate
    if (!metode || !nominal || nominal <= 0) {
        Swal.fire('Error!', 'Harap lengkapi metode dan nominal pembayaran.', 'error');
        return;
    }

    // Create list item
    var metodeText = metode === 'tunai' ? 'Tunai' : 'Transfer';
    var listItem = `
        <div class="pembayaran-item card mb-2 border-left-info"
             data-metode="${metode}"
             data-nominal="${nominal}"
             data-keterangan="${keterangan || ''}">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <strong class="text-info">${metodeText}</strong>
                    </div>
                    <div class="col-md-3">
                        <span class="fw-medium">Rp ${nominal.toLocaleString('id-ID')}</span>
                    </div>
                    <div class="col-md-5">
                        <small class="text-muted">${keterangan || '-'}</small>
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-pembayaran-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Add to list
    $('#pembayaranItems').append(listItem);
    $('#pembayaranList').show();

    // Clear form
    $('#metode_pembayaran').val('');
    $('#nominal_pembayaran').val('');
    $('#keterangan_pembayaran').val('');
}

function removePembayaranFromList(itemElement) {
    itemElement.remove();

    // Hide container if no items left
    if ($('#pembayaranItems').children().length === 0) {
        $('#pembayaranList').hide();
    }
}

function submitForm() {
    var formData = new FormData(document.getElementById('pembelianForm'));

    // If there are items in the list, collect data from the list instead of form
    var hasItems = $('.added-item').length > 0;
    if (hasItems) {
        var details = [];
        $('.added-item').each(function(index) {
            var item = $(this);
            details.push({
                barang_id: item.data('barang-id'),
                satuan_id: item.data('satuan-id'),
                qty: item.data('qty'),
                harga_beli: item.data('harga-beli'),
                keterangan: item.data('keterangan') || ''
            });
        });

        // Add details to formData
        formData.append('details', JSON.stringify(details));
    }

    // Collect pembayaran data
    var pembayaran = [];
    $('.pembayaran-item').each(function(index) {
        var item = $(this);
        pembayaran.push({
            metode: item.data('metode'),
            nominal: item.data('nominal'),
            keterangan: item.data('keterangan') || ''
        });
    });

    // If no items in list, collect from form fields
    if (pembayaran.length === 0) {
        var metode = $('#metode_pembayaran').val();
        var nominal = parseFloat($('#nominal_pembayaran').val());
        var keterangan = $('#keterangan_pembayaran').val();

        if (metode && nominal > 0) {
            pembayaran.push({
                metode: metode,
                nominal: nominal,
                keterangan: keterangan || ''
            });
        }
    }

    if (pembayaran.length > 0) {
        formData.append('pembayaran', JSON.stringify(pembayaran));
    }

    $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

    $.ajax({
        url: '{{ route("pembelian.store") }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(function() {
                    window.location.href = '{{ route("pembelian.show", ":id") }}'.replace(':id', response.data.id);
                });
            } else {
                Swal.fire('Gagal!', response.message, 'error');
            }
        },
        error: function(xhr) {
            var errors = xhr.responseJSON.errors;
            var errorMessage = 'Terjadi kesalahan:';
            if (errors) {
                for (var key in errors) {
                    errorMessage += '\n- ' + errors[key][0];
                }
            }
            Swal.fire('Error!', errorMessage, 'error');
        },
        complete: function() {
            $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
        }
    });
}

function resetForm() {
    $('#pembelianForm')[0].reset();
    $('#supplier_autocomplete').val('');
    $('#supplier_id').val('');
    $('#addedItemsList').html('');
    $('#addedItemsContainer').hide();
    $('#pembayaranItems').html('');
    $('#pembayaranList').hide();
    $('#keterangan_pembayaran_container').hide(); // Hide keterangan field on reset
    calculateTotal();
    updateRemoveButtons();
}
</script>

@include('layout.footer')
