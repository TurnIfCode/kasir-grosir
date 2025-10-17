@include('layout.header')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Edit Pembelian - {{ $pembelian->kode_pembelian }}</h5>
                    <a href="{{ route('pembelian.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <form id="pembelianForm">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                                    <select class="form-select" id="supplier_id" name="supplier_id" required>
                                        <option value="">Pilih Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ $pembelian->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->nama_supplier }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tanggal_pembelian" class="form-label">Tanggal Pembelian <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tanggal_pembelian" name="tanggal_pembelian" value="{{ $pembelian->tanggal_pembelian->format('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="diskon" class="form-label">Diskon</label>
                                    <input type="number" class="form-control" id="diskon" name="diskon" value="{{ $pembelian->diskon }}" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ppn" class="form-label">PPN</label>
                                    <input type="number" class="form-control" id="ppn" name="ppn" value="{{ $pembelian->ppn }}" min="0" step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="3">{{ $pembelian->catatan }}</textarea>
                        </div>

                        <hr>
                        <h6>Detail Barang</h6>
                        <div id="detailContainer">
                            @foreach($pembelian->details as $index => $detail)
                            <div class="detail-row mb-3 border p-3 rounded">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Barang <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control barang-autocomplete" name="details[{{ $index }}][barang_nama]" value="{{ $detail->barang->nama_barang }}" placeholder="Ketik nama atau kode barang" required>
                                        <input type="hidden" class="barang-id" name="details[{{ $index }}][barang_id]" value="{{ $detail->barang_id }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                        <select class="form-select satuan-select" name="details[{{ $index }}][satuan_id]" required>
                                            <option value="">Pilih Satuan</option>
                                            <!-- Satuan akan diisi via AJAX -->
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Qty <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control qty-input" name="details[{{ $index }}][qty]" value="{{ $detail->qty }}" min="0.01" step="0.01" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Harga Beli <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control harga-input" name="details[{{ $index }}][harga_beli]" value="{{ $detail->harga_beli }}" min="0" step="0.01" required readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Subtotal</label>
                                        <input type="number" class="form-control subtotal-input" value="{{ $detail->subtotal }}" readonly>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-danger btn-sm remove-row" style="{{ count($pembelian->details) > 1 ? '' : 'display: none;' }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-11">
                                        <label class="form-label">Keterangan</label>
                                        <input type="text" class="form-control" name="details[{{ $index }}][keterangan]" value="{{ $detail->keterangan }}">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" id="addRowBtn" class="btn btn-success btn-sm mb-3">
                            <i class="fas fa-plus"></i> Tambah Barang
                        </button>

                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <strong>Subtotal:</strong>
                                            <span id="totalSubtotal">Rp {{ number_format($pembelian->subtotal, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <strong>Diskon:</strong>
                                            <span id="totalDiskon">Rp {{ number_format($pembelian->diskon, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <strong>PPN:</strong>
                                            <span id="totalPpn">Rp {{ number_format($pembelian->ppn, 0, ',', '.') }}</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total:</strong>
                                            <span id="totalAkhir">Rp {{ number_format($pembelian->total, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save"></i> Update
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let rowIndex = {{ count($pembelian->details) }};

// Load satuan untuk detail yang sudah ada
$(document).ready(function() {
    $('.detail-row').each(function(index) {
        var row = $(this);
        var barangId = row.find('.barang-id').val();
        var satuanId = '{{ $pembelian->details[$index]->satuan_id ?? "" }}';

        if (barangId) {
            loadSatuan(row, barangId, satuanId);
        }
    });

    calculateTotal();
});

$(document).ready(function() {
    // Add new row
    $('#addRowBtn').click(function() {
        addNewRow();
    });

    // Remove row
    $(document).on('click', '.remove-row', function() {
        $(this).closest('.detail-row').remove();
        calculateTotal();
        updateRemoveButtons();
    });

    // Calculate subtotal when qty or harga changes
    $(document).on('input', '.qty-input, .harga-input', function() {
        calculateSubtotal($(this).closest('.detail-row'));
        calculateTotal();
    });

    // Autocomplete barang
    $(document).on('focus', '.barang-autocomplete', function() {
        $(this).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '{{ route("pembelian.autocomplete-barang") }}',
                    dataType: 'json',
                    data: { term: request.term },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.kode_barang + ' - ' + item.nama_barang,
                                value: item.nama_barang,
                                id: item.id,
                                stok: item.stok
                            };
                        }));
                    }
                });
            },
            minLength: 2,
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

    // Update harga when satuan changes
    $(document).on('change', '.satuan-select', function() {
        var row = $(this).closest('.detail-row');
        var barangId = row.find('.barang-id').val();
        var satuanId = $(this).val();

        if (barangId && satuanId) {
            $.ajax({
                url: '/pembelian/get-harga/' + barangId + '/' + satuanId,
                type: 'GET',
                success: function(data) {
                    row.find('.harga-input').val(data.harga_beli);
                    calculateSubtotal(row);
                    calculateTotal();
                }
            });
        }
    });

    // Update diskon and ppn
    $('#diskon, #ppn').on('input', function() {
        calculateTotal();
    });

    // Form validation and submit
    $('#pembelianForm').validate({
        rules: {
            supplier_id: 'required',
            tanggal_pembelian: 'required',
            'details[0][barang_id]': 'required',
            'details[0][satuan_id]': 'required',
            'details[0][qty]': {
                required: true,
                min: 0.01
            },
            'details[0][harga_beli]': {
                required: true,
                min: 0
            }
        },
        messages: {
            supplier_id: 'Supplier harus dipilih',
            tanggal_pembelian: 'Tanggal pembelian harus diisi',
            'details[0][barang_id]': 'Barang harus dipilih',
            'details[0][satuan_id]': 'Satuan harus dipilih',
            'details[0][qty]': {
                required: 'Qty harus diisi',
                min: 'Qty minimal 0.01'
            },
            'details[0][harga_beli]': {
                required: 'Harga beli harus diisi',
                min: 'Harga beli minimal 0'
            }
        },
        submitHandler: function(form) {
            submitForm();
        }
    });
});

function addNewRow() {
    var newRow = `
        <div class="detail-row mb-3 border p-3 rounded">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Barang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control barang-autocomplete" name="details[${rowIndex}][barang_nama]" placeholder="Ketik nama atau kode barang" required>
                    <input type="hidden" class="barang-id" name="details[${rowIndex}][barang_id]" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Satuan <span class="text-danger">*</span></label>
                    <select class="form-select satuan-select" name="details[${rowIndex}][satuan_id]" required disabled>
                        <option value="">Pilih Satuan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qty <span class="text-danger">*</span></label>
                    <input type="number" class="form-control qty-input" name="details[${rowIndex}][qty]" min="0.01" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Harga Beli <span class="text-danger">*</span></label>
                    <input type="number" class="form-control harga-input" name="details[${rowIndex}][harga_beli]" min="0" step="0.01" required readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Subtotal</label>
                    <input type="number" class="form-control subtotal-input" readonly>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm remove-row">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-11">
                    <label class="form-label">Keterangan</label>
                    <input type="text" class="form-control" name="details[${rowIndex}][keterangan]">
                </div>
            </div>
        </div>
    `;
    $('#detailContainer').append(newRow);
    rowIndex++;
    updateRemoveButtons();
}

function loadSatuan(row, barangId, selectedSatuanId = null) {
    $.ajax({
        url: '/pembelian/get-satuan/' + barangId,
        type: 'GET',
        success: function(data) {
            var satuanSelect = row.find('.satuan-select');
            satuanSelect.empty().append('<option value="">Pilih Satuan</option>');

            $.each(data, function(index, satuan) {
                var selected = (selectedSatuanId && satuan.id == selectedSatuanId) ? 'selected' : '';
                satuanSelect.append('<option value="' + satuan.id + '" ' + selected + '>' + satuan.nama_satuan + '</option>');
            });

            satuanSelect.prop('disabled', false);

            // Jika ada satuan yang dipilih, load harga
            if (selectedSatuanId) {
                $.ajax({
                    url: '/pembelian/get-harga/' + barangId + '/' + selectedSatuanId,
                    type: 'GET',
                    success: function(data) {
                        row.find('.harga-input').val(data.harga_beli);
                        calculateSubtotal(row);
                        calculateTotal();
                    }
                });
            }
        }
    });
}

function calculateSubtotal(row) {
    var qty = parseFloat(row.find('.qty-input').val()) || 0;
    var harga = parseFloat(row.find('.harga-input').val()) || 0;
    var subtotal = qty * harga;
    row.find('.subtotal-input').val(subtotal.toFixed(2));
}

function calculateTotal() {
    var totalSubtotal = 0;
    $('.subtotal-input').each(function() {
        totalSubtotal += parseFloat($(this).val()) || 0;
    });

    var diskon = parseFloat($('#diskon').val()) || 0;
    var ppn = parseFloat($('#ppn').val()) || 0;
    var totalAkhir = totalSubtotal - diskon + ppn;

    $('#totalSubtotal').text('Rp ' + totalSubtotal.toLocaleString('id-ID'));
    $('#totalDiskon').text('Rp ' + diskon.toLocaleString('id-ID'));
    $('#totalPpn').text('Rp ' + ppn.toLocaleString('id-ID'));
    $('#totalAkhir').text('Rp ' + totalAkhir.toLocaleString('id-ID'));
}

function updateRemoveButtons() {
    var rowCount = $('.detail-row').length;
    if (rowCount > 1) {
        $('.remove-row').show();
    } else {
        $('.remove-row').hide();
    }
}

function submitForm() {
    var formData = new FormData(document.getElementById('pembelianForm'));

    $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengupdate...');

    $.ajax({
        url: '{{ route("pembelian.update", $pembelian->id) }}',
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
                    window.location.href = '{{ route("pembelian.index") }}';
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
            $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Update');
        }
    });
}

function resetForm() {
    location.reload();
}
</script>

@include('layout.footer')
