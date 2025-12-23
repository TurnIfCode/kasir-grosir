
@section('title', 'Tambah Pembelian')
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
                                    <label for="diskon" class="form-label fw-medium">Diskon(Rp.)</label>
                                    <input type="number" class="form-control" id="diskon" name="diskon" value="0" min="0">
                                </div>
                                <div class="col-md-6">
                                    <label for="ppn" class="form-label fw-medium">PPN(Rp.)</label>
                                    <input type="number" class="form-control" id="ppn" name="ppn" value="0" min="0">
                                </div>
                                <div class="col-8">
                                    <label for="catatan" class="form-label fw-medium">Catatan</label>
                                    <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                                </div>
                                <div class="col-4">
                                    <button type="submit" class="btn btn-primary" id="btnSavePembelianMaster" style="margin-top: 32px;">
                                        <i class="fas fa-save me-1"></i>Simpan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- Detail Barang -->
                    <div class="mb-4" style="display: none;" id="dtlPembelian">
                        <div class="detail-row mb-3 border rounded p-3 bg-light" id="defaultRow">
                            <input type="hidden" id="pembelian_id" name="pembelian_id">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Barang <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control barang-autocomplete" name="nama_barang" id="nama_barang[0]" data-id="0" placeholder="Ketik nama atau kode barang">
                                    <input type="hidden" class="barang-id" name="barang_id">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-medium">Satuan <span class="text-danger">*</span></label>
                                    <select class="form-select satuan-select" name="satuan_id" id="satuan_id[0]" data-id="0" disabled>
                                        <option value="">Pilih Satuan</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-medium">Qty <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control qty-input" name="qty" id="qty[0]">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-medium">Harga Beli <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control harga-input" name="harga_beli" id="harga_beli[0]">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-medium">Subtotal</label>
                                    <input type="number" class="form-control subtotal-input fw-semibold" name="subtotal" id="subtotal[0]" readonly>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" id="btnSaveDtl" class="btn btn-outline-success btn-sm add-to-list-btn w-100">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- List of added items -->
                            <div id="addedItemsContainer" style="display: none; margin-top: 15px;">
                                <h6 class="fw-semibold mb-3 text-primary">
                                    <i class="fas fa-list me-2"></i>Barang yang Ditambahkan
                                </h6>
                                <div id="detailContainer">
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
                                    <input type="number" class="form-control" id="nominal_pembayaran" name="nominal_pembayaran" value="0">
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
                        </div>
                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-primary" id="btnSaveAll">
                                <i class="fas fa-save me-1"></i>Simpan
                            </button>
                        </div>
                    </div>
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
@include('layout.footer')

<script>
    $(document).ready(function() {
        $("#supplier_autocomplete").focus().select();
        
        $('#supplier_autocomplete').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '{{ route("supplier.search") }}',
                    dataType: 'json',
                    data: { q: request.term },
            success: function(data) {
                if (data.success === true) {
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
            minLength: 3,
            select: function(event, ui) {
                $('#supplier_id').val(ui.item.id);
                $('#supplier_autocomplete').val(ui.item.value);
                return false;
            }
        });
        
        $(document).on('click', '#btnSavePembelianMaster', function() {
            if ($('[name=catatan]').val() === '') {
                $('[name=catatan]').val('-');
            }
            $("#pembelianForm").validate({
                rules: {
                    supplier_id: {
                        required: true
                    },
                    supplier_nama: {
                        required: true,
                    }
                },
                messages: {
                    supplier_id: {
                        required: 'Supplier harus diisi'
                    },
                    supplier_nama: {
                        required: 'Supplier harus diisi',
                    }
                },
                submitHandler: function(form) {
                    $("#btnSavePembelianMaster").prop("disabled", true);
                    $("#btnSavePembelianMaster").html('<i class="fas fa-spinner fa-spin me-1"></i> Loading...');
                    $.ajax({
                        url: "{{ route('pembelian.store') }}",
                        type: "POST",
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.success) {
                                $("[name=supplier_nama]").prop("disabled", true);
                                $("[name=tanggal_pembelian]").prop("disabled", true);
                                $("[name=diskon]").prop("disabled", true);
                                $("[name=ppn]").prop("disabled", true);
                                $("[name=pembelian_id]").val(response.pembelian_id);
                                $("[name=catatan]").prop("disabled", true);
                                $("#btnSavePembelianMaster").hide();
                                $('#dtlPembelian').show();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.message
                                }).then(function() {
                                    setTimeout(() => {
                                    $(`#${response.form}`).focus().select();
                                    }, 500);
                                });
                            }
                        },
                        error: function(xhr) {
                            var errors = xhr.responseJSON.errors;
                            if (errors) {
                            var errorMessage = '';
                            for (var key in errors) {
                                errorMessage += errors[key][0] + '\n';
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: errorMessage
                            });
                            } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Terjadi kesalahan'
                            });
                            }
                        }
                    });
                }
            });
        });

        $(document).on('focus', '.barang-autocomplete', function () {
            var dataId = $(this).attr('data-id');
            var element = this; // simpan element autocomplete
            $(this).autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: '{{ route("barang.search") }}',
                        dataType: 'json',
                        data: { q: request.term },
                        success: function (data) {
                            if (data.success) {

                                // -------------------------------------------------------
                                // AUTO SELECT BARCODE
                                // -------------------------------------------------------
                                let exactBarcodeItem = data.data.find(function (item) {
                                    return item.barcode && item.barcode.toString() === request.term.toString();
                                });

                                // Jika input dari scanner EXACT sama dengan barcode item
                                if (exactBarcodeItem) {

                                    var ui = {
                                        item: {
                                            id: exactBarcodeItem.id,
                                            value: exactBarcodeItem.nama_barang,
                                            barcode: exactBarcodeItem.barcode
                                        }
                                    };

                                    // Trigger autocomplete "select"
                                    $(element).autocomplete("instance")._trigger("select", null, ui);

                                    return; // Stop autocomplete dropdown
                                }

                                // -------------------------------------------------------
                                // AUTOCOMPLETE NORMAL
                                // -------------------------------------------------------
                                response($.map(data.data, function (item) {
                                    return {
                                        label: item.text,
                                        value: item.nama_barang,
                                        id: item.id,
                                        barcode: item.barcode
                                    };
                                }));
                            }
                        }
                    });
                },
                minLength: 3,
                select: function (event, ui) {
                    $("[name=nama_barang]").val(ui.item.value);
                    $("[name=barang_id]").val(ui.item.id);
                    // Load satuan untuk barang ini
                    loadSatuan(ui.item.id);

                    return false;
                }
            });
        });

        function loadSatuan(barangId) {
            $.ajax({
                url: '{{ route("barang.satuan", ":id") }}'.replace(':id', barangId),
                type: 'GET',
                success: function(data) {
                    if (data.success === true) {

                        // PERBAIKAN PENTING DI SINI
                        var satuanSelect = $('[name="satuan_id"]');

                        satuanSelect.empty().append('<option value="">Pilih Satuan</option>');

                        $.each(data.data, function(index, satuan) {
                            satuanSelect.append(
                                '<option value="' + satuan.satuan_id + '" data-harga="' + satuan.harga_beli + '">'
                                + satuan.nama_satuan +
                                '</option>'
                            );
                        });

                        satuanSelect.prop('disabled', false);


                        if (data.data.length > 0) {
                            // Cari satuan dengan nilai_konversi tertinggi
                            let highestKonversi = data.data.reduce(function(max, current) {
                                return (current.nilai_konversi > max.nilai_konversi) ? current : max;
                            });
                            
                            // Set select ke satuan dengan nilai konversi tertinggi
                            satuanSelect.val(highestKonversi.satuan_id);
                            $('[name="qty"]').val(1);
                            $('[name="harga_beli"]').val(highestKonversi.harga_beli);

                            $('[name="subtotal"]').val(highestKonversi.harga_beli);
                        }
                    }
                }
            });
        }


        function calculateSubtotal(qty,harga_beli) {
            var subtotal = qty*harga_beli;
            $('[name="subtotal"]').val(subtotal);
        }

        // update harga by satuan
        $(document).on('change', 'select[name="satuan_id"]', function() {
            var row = $(this).closest('.detail-row');
            var selectedOption = $(this).find('option:selected');
            var harga = selectedOption.data('harga');

            var hargaInput = row.find('.harga-input');

            if (harga !== undefined && harga > 0) {
                hargaInput.val(harga);
            } else {
                hargaInput.val(0);
            }

            var qty = $("[name=qty]").val();
            calculateSubtotal(qty,harga);
        });

        $(document).on('keyup', '[name="qty"]', function() {

            var qty = parseFloat($(this).val()) || 0;
            var harga_beli = parseFloat($("[name=harga_beli]").val()) || 0;
            calculateSubtotal(qty,harga_beli);
        });

        $(document).on('keyup', '[name="harga_beli"]', function() {

            var harga_beli = parseFloat($(this).val()) || 0;
            var qty = parseFloat($("[name=qty]").val()) || 0;
            calculateSubtotal(qty,harga_beli);
        });

        $(document).on('click', '#btnSaveDtl', function() {
            var pembelian_id = $("[name=pembelian_id]").val();
            var barang_id = $("[name=barang_id]").val();
            var satuan_id = $("[name=satuan_id]").val();
            var qty = $("[name=qty]").val();
            var harga_beli = $("[name=harga_beli]").val();

            $.ajax({
                url: "{{ route('pembelian.save-dtl') }}",
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    pembelian_id: pembelian_id,
                    barang_id: barang_id,
                    satuan_id: satuan_id,
                    qty: qty,
                    harga_beli: harga_beli
                },

                success: function(response) {
                    if (response.success) {

                        setTimeout(() => {

                            // ===============================
                            // 🔥 UPDATE TOTAL
                            // ===============================
                            let totalSubtotal = parseFloat(response.data.subtotal) || 0;
                            let totalDiskon   = parseFloat(response.data.diskon) || 0;
                            let totalPpn      = parseFloat(response.data.ppn) || 0;
                            let totalAkhir    = parseFloat(response.data.total) || 0;

                            $('#totalSubtotal').text('Rp ' + totalSubtotal.toLocaleString('id-ID'));
                            $('#totalDiskon').text('Rp ' + totalDiskon.toLocaleString('id-ID'));
                            $('#totalPpn').text('Rp ' + totalPpn.toLocaleString('id-ID'));
                            $('#totalAkhir').text('Rp ' + totalAkhir.toLocaleString('id-ID'));

                            // 🔄 Refresh detail list
                            getDetail(response.data.id);


                            // =====================================================
                            // 🔥 RESET FORM INPUT DETAIL (sesuai permintaan kamu)
                            // =====================================================

                            $("[name=barang_id]").val('');
                            $("[name=barang_nama]").val(''); // kalau pakai autocomplete

                            // Reset select satuan
                            let satuanSelect = $("[name=satuan_id]");
                            satuanSelect.empty()
                                .append('<option value="">Pilih Satuan</option>')
                                .prop("disabled", true);

                            $("[name=qty]").val('');
                            $("[name=harga_beli]").val('');
                            $("[name=keterangan]").val('');
                            $("[name=nama_barang]").val('');
                            $("[name=subtotal]").val('')

                        }, 300);
                    }
                },

                // ❌ Error handler pakai Swal
                error: function(xhr) {
                    let msg = "Terjadi kesalahan saat memproses data.";

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: msg
                    });
                }
            });

        });



        $(document).on('click', '#btnDltDtl', function() {
            var pembelianId = $(this).attr("data-mst-id");
            var pembelianDetailId = $(this).attr("data-id");

            let url = "{{ route('pembelian.delete-dtl', ['pembelian_id' => ':pid', 'pembelian_detail_id' => ':did']) }}"
                .replace(':pid', pembelianId)
                .replace(':did', pembelianDetailId);

            $.ajax({
                url: url,
                type: "DELETE",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        }).then(function() {
                            setTimeout(() => {
                                var totalSubtotal = response.data.subtotal;
                                totalSubtotal = parseFloat(totalSubtotal) || 0;
                                var totalDiskon = response.data.diskon;
                                totalDiskon = parseFloat(totalDiskon) || 0;
                                var totalPpn = response.data.ppn;
                                totalPpn = parseFloat(totalPpn) || 0;
                                var totalAkhir = response.data.total;
                                totalAkhir = parseFloat(totalAkhir) || 0;

                                $('#totalSubtotal').text('Rp ' + totalSubtotal.toLocaleString('id-ID', {maximumFractionDigits: 2}));
                                $('#totalDiskon').text('Rp ' + totalDiskon.toLocaleString('id-ID', {maximumFractionDigits: 2}));
                                $('#totalPpn').text('Rp ' + totalPpn.toLocaleString('id-ID', {maximumFractionDigits: 2}));
                                $('#totalAkhir').text('Rp ' + totalAkhir.toLocaleString('id-ID', {maximumFractionDigits: 2}));

                                getDetail(response.data.id);
                            }, 500);
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = "Terjadi kesalahan saat menghapus data.";

                    // Jika Laravel mengirim response JSON
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errorMessage
                    });
                }

            });
            
        });

        function getDetail(pembelian_id) {
            let url = "{{ route('pembelian.get-dtl', ':id') }}".replace(':id', pembelian_id);

            $.ajax({
                url: url,
                type: "GET",
                success: function(result) {

                    // Focus kembali ke input barang
                    $("[name=nama_barang]").focus().select();

                    $("#addedItemsContainer").show();

                    let container = $("#detailContainer");
                    container.empty(); // Kosongkan dulu

                    result.datas.forEach(function(item) {
                        let html = `
                        <div class="added-item card mb-2 border-left-primary"
                            data-barang-id="${item.barang_id}"
                            data-satuan-id="${item.satuan_id}"
                            data-qty="${item.qty}"
                            data-harga-beli="${item.harga_beli}"
                            data-keterangan="${item.keterangan ?? ''}"
                            data-subtotal="${item.subtotal}">
                            
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <strong class="text-primary">${item.barang.nama_barang}</strong>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted">${item.satuan.nama_satuan}</small>
                                    </div>
                                    <div class="col-md-1">
                                        <span class="badge bg-secondary">${parseFloat(item.qty)}</span>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="fw-medium">Rp ${parseFloat(item.harga_beli).toLocaleString('id-ID')}</span>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="fw-bold text-success" style="text-align: right;">Rp ${parseFloat(item.subtotal).toLocaleString('id-ID')}</span>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <button type="button" id="btnDltDtl" data-id="${item.id}" data-mst-id="${item.pembelian_id}}" class="btn btn-sm btn-outline-danger remove-from-list-btn">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                ${item.keterangan ? `
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <small class="text-muted">
                                        <i class="fas fa-sticky-note me-1"></i>${item.keterangan}
                                        </small>
                                    </div>
                                </div>
                                ` : ""}
                            </div>
                        </div>
                        `;

                        container.append(html);

                        
                    });

                }
            });
        }

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

        $(document).on('click', '#btnSaveAll', function() {
            var pembelian_id = $("[name=pembelian_id]").val();

            cekDtl(pembelian_id);

            var metode_pembayaran = $("[name=metode_pembayaran]").val();
            var nominal_pembayaran = parseFloat($("[name=nominal_pembayaran]").val());
            var keterangan_pembayaran = $("[name=keterangan_pembayaran]").val();
            let totalText = $("#totalAkhir").text();
            let totalValue = totalText.replace(/[Rp\s\.]/g, '').replace(',', '.');
            totalValue = parseFloat(totalValue) || 0;


            if (metode_pembayaran == '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Metode pembayaran belum dipilih'
                }).then(function() {
                    setTimeout(() => {
                        $("[name=metode_pembayaran]").focus().select();
                    }, 500);
                });
                return false;
            }
            
            if (nominal_pembayaran < totalValue) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Nominal pembayaran masih kurang dari total pembelian'
                }).then(function() {
                    setTimeout(() => {
                        $("[name=nominal_pembayaran]").focus().select();
                    }, 500);
                });
                return false;
            }
            let url = "{{ route('pembelian.save-all', ':id') }}".replace(':id', pembelian_id);

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    metode_pembayaran: metode_pembayaran,
                    nominal_pembayaran: nominal_pembayaran,
                    keterangan_pembayaran: keterangan_pembayaran
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        }).then(function() {
                            setTimeout(() => {
                                window.location.href = "{{ route('pembelian.index') }}";
                            }, 500);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message
                        }).then(function() {
                            setTimeout(() => {
                                $(`#${response.form}`).focus().select();
                            }, 500);
                        });
                    }
                }
            });
        });

        function cekDtl(pembelian_id) {
            let url = "{{ route('pembelian.cek-dtl', ':id') }}".replace(':id', pembelian_id);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(result) {
                    if (!result.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: result.message
                        }).then(function() {
                            setTimeout(() => {
                                getDetail(pembelian_id);
                            }, 500);
                        });
                    } else {
                        return true;
                    }
                }
            });
        }

    });
</script>
</body>
</html>