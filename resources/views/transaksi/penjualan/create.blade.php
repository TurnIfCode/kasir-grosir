@section('title', 'Penjualan')
@include('layout.header')
<div class="container-fluid py-2 py-md-4">
    <!-- Header Section -->
    <div class="row mb-3 mb-md-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 fw-bold text-primary mb-1">Transaksi Penjualan</h1>
                    <p class="text-muted mb-0">Catat transaksi pelanggan dengan cepat dan akurat.</p>
                </div>
                <a href="{{ route('penjualan.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
    <!-- Informasi Penjualan -->
    <div class="row mb-3 mb-md-4">
        <div class="col-lg-8 mt-4 mt-lg-0">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informasi Penjualan</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <label for="kode_penjualan" class="form-label fw-semibold fs-6">Kode Penjualan</label>
                            <input type="text" class="form-control" id="kode_penjualan" name="kode_penjualan" value="{{ $kodePenjualan }}" readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="tanggal_penjualan" class="form-label fw-semibold fs-6">Tanggal Penjualan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_penjualan" name="tanggal_penjualan" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="pelanggan_autocomplete" class="form-label fw-semibold fs-6">Pelanggan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="pelanggan_autocomplete" placeholder="Cari pelanggan..." value="{{ $pelangganDefault->nama_pelanggan }}"  required>
                            <input type="hidden" id="pelanggan_id" name="pelanggan_id" value="{{ $pelangganDefault->id }}">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="catatan" class="form-label fw-semibold fs-6">Catatan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="2" placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan -->
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Ringkasan</h5>
                </div>
                <div class="card-body p-2 p-md-3">
                    <div class="mb-2 mb-md-3">
                        <label class="form-label fw-semibold fs-6">Subtotal</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control fw-bold fs-6 fs-md-5" id="subtotal" value="0" readonly>
                        </div>
                    </div>
                    <div class="mb-2 mb-md-3">
                        <label class="form-label fw-semibold fs-6">Pembulatan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control fw-bold fs-6 fs-md-5" id="pembulatan" value="0" readonly>
                        </div>
                    </div>
                    <div class="mb-2 mb-md-3">
                        <label class="form-label fw-semibold fs-6">Grand Total</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>

                            <input type="text" class="form-control fw-bold fs-5 fs-md-4 text-primary" id="summaryGrandTotal" value="0" readonly>
                            <input type="hidden" id="grandTotalValue">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <h5 class="card-title mb-0">Detail Barang</h5>
        </div>

        <div class="card-body p-1 p-md-3">

            <!-- HEADER STYLE TABLE -->
            <div class="row g-2 px-2 fw-semibold text-muted border-bottom pb-2">
                <div class="col-4">Barang</div>
                <div class="col-2">Satuan</div>
                <div class="col-2">Tipe Harga</div>
                <div class="col-1">Qty</div>
                <div class="col-1">Harga Satuan</div>
                <div class="col-1">Sub Total</div>
                <div class="col-1">Aksi</div>
            </div>

            <form id="detailContainer" class="mt-2">
                <input type="hidden" value="ecer" id="tipe_harga_default" name="tipe_harga_default">
                <!-- ROW 0 -->
                <div class="row align-items-center g-2 py-2 border-bottom detail-row" data-row="0">

                    <div class="col-4">
                        <input type="text" class="form-control barang-autocomplete"
                            name="detail[0][barang]" id="barang[0]" data-id="0" placeholder="Cari barang...">
                        <input type="hidden" name="detail[0][barang_id]" id="barang_id[0]" data-id="0">
                    </div>

                    <div class="col-2">
                        <select class="form-control satuan-select" name="detail[0][satuan_id]" id="satuan_id[0]" data-id="0">
                            <option value="">Pilih</option>
                        </select>
                    </div>

                    <div class="col-2">
                        <select class="form-control tipe-harga-select" name="detail[0][tipe_harga]" id="tipe_harga[0]" data-id="0">
                        </select>
                    </div>

                    <div class="col-1">
                        <input type="number" class="form-control qty-input" name="detail[0][qty]" id="qty[0]" data-id="0">
                    </div>

                    <div class="col-1">
                        <input type="number" class="form-control harga-jual-input"
                            name="detail[0][harga_jual]" id="harga_jual[0]" data-id="0" data-id="0" readonly>
                    </div>

                    <div class="col-1">
                        <input type="number" class="form-control subtotal-input"
                            name="detail[0][subtotal]" id="subtotal[0]" data-id="0" data-id="0" readonly>
                    </div>

                    <div class="col-1">
                        
                        <button type="button" class="btn btn-danger btn-sm remove-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                </div>

            </form>

        </div>
    </div>

    <!-- Total & Pembayaran -->
    <div class="row mb-3 mb-md-4">
        <div class="col-lg-8 mt-4 mt-lg-0">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Pembayaran</h5>
                </div>
                <div class="card-body p-2 p-md-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="jenis_pembayaran" class="form-label fw-semibold">Jenis Pembayaran <span class="text-danger">*</span></label>
                            <select class="form-select" id="jenis_pembayaran" name="jenis_pembayaran" required>
                                <option value="">Pilih Jenis</option>
                                <option value="tunai">Tunai</option>
                                <option value="non_tunai">Non Tunai</option>
                                <!-- <option value="campuran">Campuran</option> -->
                            </select>
                        </div>
                        <div class="col-6 tunai-field" style="display: none;">
                            <label for="dibayar" class="form-label fw-semibold">Nominal Bayar</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control fw-bold fs-5" id="dibayar" name="dibayar" min="0" required>
                            </div>
                        </div>
                        <div class="col-6 tunai-field" style="display: none;">
                            <label for="kembalian" class="form-label fw-semibold">Kembalian</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control fw-bold fs-5 text-success" id="kembalian" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Campuran payment fields -->
                    <div id="campuranFields" style="display: none;" class="mt-3">
                        <h6 class="fw-semibold">Pembayaran Campuran</h6>
                        <div id="paymentContainer">
                            <!-- Dynamic payment rows will be added here -->
                        </div>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="addPaymentRow()">
                            <i class="fas fa-plus me-2"></i>Tambah Pembayaran
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grand Total -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Total Pembayaran</h5>
                </div>
                <div class="card-body p-2 p-md-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subtotal</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control fw-bold fs-5" id="grandSubtotal" value="0" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pembulatan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control fw-bold fs-5" id="grandPembulatan" value="0" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Grand Total</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control fw-bold fs-3 text-primary" id="paymentGrandTotal" value="0" readonly>
                            <input type="hidden" id="paymentGrandTotalValue" name="grand_total">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="row mt-3 mt-md-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-12">
                    <button type="button" class="btn btn-primary btn-lg px-4 w-100 w-sm-auto" id="btnSimpanCetak">
                        <i class="fas fa-save me-2"></i>Simpan & Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layout.footer')

<script>
$(document).ready(function () {

    toggleRemoveButton();

    // =====================================================
    // EVENT LISTENER GLOBAL BARANG (input / keyup / change)
    // =====================================================
    $(document).on("input keyup change", ".barang-autocomplete", function (e) {
        let row = $(this).data("id");
        let val = $(this).val();
        
    });

    // =====================================================
    // SCAN BARCODE → ENTER
    // =====================================================
    $(document).on("keypress", ".barang-autocomplete", function (e) {

        if (e.which === 13) { // ENTER
            e.preventDefault();

            let row = $(this).data("id");
            let barcode = $(this).val().trim();

            if (barcode === "") return;

            

            // Gunakan route barang.search
            $.ajax({
                url: "{{ route('barang.search') }}",
                type: "GET",
                data: { term: barcode },    // <--- SCAN BARCODE DISINI
                success: function (res) {

                    if (res.success && res.data.length > 0) {

                        // Ambil barang pertama yang match barcode
                        let barang = res.data[0];
                        //disini ambil dulu value qty, harga_jual, subtotal
                        if ($("#qty\\[" + row + "\\]").val() === 0 || $("#qty\\[" + row + "\\]").val() === '' || $("#qty\\[" + row + "\\]").val() === null) {
                            $("#qty\\[" + row + "\\]").val(1);
                        }
                        if ($("#harga_jual\\[" + row + "\\]").val() === 0) {
                            $("#harga_jual\\[" + row + "\\]").val(0);
                        }
                        if ($("#subtotal\\[" + row + "\\]").val() === 0) {
                            $("#subtotal\\[" + row + "\\]").val(0);
                        }

                        pilihBarangAuto(row, barang);

                    } else {
                        alert("Barcode tidak ditemukan!");
                        $("#barang\\[" + row + "\\]").select();
                    }
                }
            });
        }

    });

    // =====================================================
    //   AUTOCOMPLETE — ROW DINAMIS
    // =====================================================
    $(document).on("focus", ".barang-autocomplete", function () {

        let input = $(this);
        let dataId = input.data("id");

        input.autocomplete({
            minLength: 2,
            delay: 200,
            source: function (request, response) {
                $.ajax({
                    url: "{{ route('barang.search') }}",
                    type: "GET",
                    data: { term: request.term },
                    success: function (res) {

                        if (!res.success) return response([]);

                        let formatted = res.data.map(item => ({
                            label: item.text,
                            value: item.text,
                            id: item.id,
                            nama: item.nama_barang
                        }));

                        response(formatted);
                    }
                });
            },


            select: function (event, ui) {

                // Set hidden barang_id[row]
                $("#barang_id\\[" + dataId + "\\]").val(ui.item.id);
                if ($("#qty\\[" + dataId + "\\]").val() === 0 || $("#qty\\[" + dataId + "\\]").val() === '' || $("#qty\\[" + dataId + "\\]").val() === null) {
                    $("#qty\\[" + dataId + "\\]").val(1);
                }
                if ($("#harga_jual\\[" + dataId + "\\]").val() === 0) {
                    $("#harga_jual\\[" + dataId + "\\]").val(0);
                }
                if ($("#subtotal\\[" + dataId + "\\]").val() === 0) {
                    $("#subtotal\\[" + dataId + "\\]").val(0);
                }
                
                
                pilihBarangAuto(dataId, ui.item);
                // Pindah ke satuan_id
                $("#satuan_id\\[" + dataId + "\\]").focus().select();

                // 🔥 CEK PAKET SETELAH BARANG DIPILIH
                setTimeout(() => {
                    cekPaketBarang();
                }, 200);
            }
        });
    });


    $(document).on('keyup', '.qty-input', function() {
        var dataId = $(this).attr('data-id');
        let barangId = $("#barang_id\\[" + dataId + "\\]").val();
        let satuanId = $("#satuan_id\\[" + dataId + "\\]").val();
        let tipeHarga = $("#tipe_harga\\[" + dataId + "\\]").val();
        let hargaJual = $("#harga_jual\\[" + dataId + "\\]").val();
        var qty = $(this).val();

        loadCalculateSubtotalDtl(dataId, barangId, satuanId, tipeHarga, hargaJual, qty);

        // 🔥 PANGGIL CEK PAKET SETIAP QTY BERUBAHA
        setTimeout(() => {
            cekPaketBarang();
        }, 100);

        if (qty != null || qty != '' || qty > 0) {
            setTimeout(() => {
                addNewRow();
            }, 1000);
        }
    });

    $(document).on('change', '.tipe-harga-select', function() {
        var dataId = $(this).attr('data-id');
        // Ambil value barang_id dan satuan_id dengan escape karakter []
        let barang_id = $("#barang_id\\[" + dataId + "\\]").val();
        let satuan_id = $("#satuan_id\\[" + dataId + "\\]").val();
        var tipe_harga = $(this).val();
        var qty = $("#qty\\[" + dataId + "\\]").val();
        qty = Math.round(qty);

        //ambil dulu value dari = tipe_harga_default
        
        
        
        

        if ($("[name=tipe_harga_default]").val() != tipe_harga) {
            $("[name=tipe_harga_default]").val(tipe_harga);
        }
        let tipeHargaDefault = $("[name=tipe_harga_default]").val();
        

        if (!barang_id || !satuan_id) return;
        let harga = 0;

        $.ajax({
            url: "/penjualan/barang/" + barang_id + "/" + satuan_id + "/" + tipeHargaDefault + "/get-harga-jual",
            type: 'get',
            success: function(result) {
                if (result.success) {
                    harga = result.data.harga;
                    harga = Math.round(harga);

                    loadCalculateSubtotalDtl(dataId, barang_id, satuan_id, tipe_harga, harga, qty);
                }
                
            }
        });

    })

    $(document).on('change', '.satuan-select', function() {
        var dataId = $(this).data('id');

        // Ambil value barang_id dan satuan_id dengan escape karakter []
        let barang_id = $("#barang_id\\[" + dataId + "\\]").val();
        let satuan_id = $("#satuan_id\\[" + dataId + "\\]").val();
        let qty = $("#qty\\[" + dataId + "\\]").val();
        

        if (!barang_id || !satuan_id) return;

        $.ajax({
            url: "/penjualan/barang/" + barang_id + "/" + satuan_id + "/get-type-harga-jual",
            type: 'get',
            success: function(result) {
                if (result.success && result.datas && result.datas.length > 0) {

                    // Selector untuk select type_harga
                    const typeSelect = $('#tipe_harga\\[' + dataId + '\\]');
                    typeSelect.empty();

                    // Cari default: ecer dulu, kalau tidak ada ambil pertama
                    let defaultTipe = result.datas.find(d => d.tipe_harga === 'ecer')?.tipe_harga || result.datas[0].tipe_harga;

                    // Tambahkan semua opsi
                    result.datas.forEach(function(item) {
                        typeSelect.append(`<option value="${item.tipe_harga}">${item.tipe_harga}</option>`);
                    });

                    // Set default value
                    typeSelect.val(defaultTipe);

                    // Set harga jual sesuai tipe default
                    let hargaDefault = Math.round(result.datas.find(d => d.tipe_harga === defaultTipe).harga);
                    $("#harga_jual\\[" + dataId + "\\]").val(hargaDefault);

                    $("#tipe_harga\\[" + dataId + "\\]").focus().select();

                    let tipeHargaDefault = $("[name=tipe_harga_default]").val();

                    if (defaultTipe != $("[name=tipe_harga_default]").val()) {
                        $("[name=tipe_harga_default]").val(defaultTipe);
                    }

                    loadCalculateSubtotalDtl(dataId, barang_id, satuan_id, defaultTipe, hargaDefault, qty)
                }
            },
            error: function(err) {
                console.error("Error load type_harga:", err);
            }
        });
    });

    // =====================================================
    // FUNGSI AUTO PILIH BARANG SETELAH SCAN
    // =====================================================
    function pilihBarangAuto(row, item) {
        $("#barang\\[" + row + "\\]").val(item.text);
        $("#barang_id\\[" + row + "\\]").val(item.id);

        loadSatuanBarang(row, item.id);

        $("#satuan_id\\[" + row + "\\]").focus().select();
    }

    // =====================================================
    // FOCUS otomatis ke barang[0]
    // =====================================================
    setTimeout(() => {
        $("#barang\\[0\\]").focus().select();
    }, 300);

    function loadSatuanBarang(row, barangId) {
        $.ajax({
            url: "/penjualan/barang/" + barangId + "/get-satuan-harga-jual",
            type: 'get',
            success: function(result) {
                
                const satuanSelect = $('select[id="satuan_id['+row+']"]');
                satuanSelect.empty();
                if (result.datas && result.datas.length > 0) {

                    let uniqueSatuan = [];
                    const seen = new Set();

                    result.datas.forEach(function(harga) {
                        if (!seen.has(harga.satuan_id)) {
                            seen.add(harga.satuan_id);
                            uniqueSatuan.push({
                                satuan_id: harga.satuan_id,
                                nama_satuan: harga.nama_satuan,
                                tipe_harga: harga.tipe_harga
                            });
                        }
                    });

                    // Tambahkan opsi
                    uniqueSatuan.forEach(function(satuan) {
                        satuanSelect.append(`<option value="${satuan.satuan_id}">${satuan.nama_satuan}</option>`);
                    });

                    // 🚀 AUTO SELECT satuan default (jika ada)
                    if (result.default_satuan_id) {
                        satuanSelect.val(uniqueSatuan[0].satuan_id).trigger("change");
                    }

                    loadTypeHarga(row, barangId, uniqueSatuan[0].satuan_id);
                    updateRingkasan();
                    
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

    function loadTypeHarga(dataId, barangId, satuanId) {
        $.ajax({
            url: "/penjualan/barang/" + barangId + "/" + satuanId + "/get-type-harga-jual",
            type: 'get',
            success: function (result) {

                if (!result.success || !result.datas || result.datas.length === 0) {
                    return;
                }

                // ambil default global (hidden input)
                let tipeHargaDefault = $("[name=tipe_harga_default]").val();

                const typeSelect = $('#tipe_harga\\[' + dataId + '\\]');
                typeSelect.empty();

                // ===============================
                // TENTUKAN DEFAULT TIPE HARGA
                // ===============================
                let defaultTipe;

                // 1️⃣ pakai default global jika valid
                if (
                    tipeHargaDefault &&
                    result.datas.some(d => d.tipe_harga === tipeHargaDefault)
                ) {
                    defaultTipe = tipeHargaDefault;
                }
                // 2️⃣ fallback ke ecer
                else if (result.datas.some(d => d.tipe_harga === 'ecer')) {
                    defaultTipe = 'ecer';
                }
                // 3️⃣ terakhir ambil data pertama
                else {
                    defaultTipe = result.datas[0].tipe_harga;
                }

                // ===============================
                // RENDER OPTION
                // ===============================
                result.datas.forEach(item => {
                    typeSelect.append(
                        `<option value="${item.tipe_harga}">${item.tipe_harga}</option>`
                    );
                });

                // set default terpilih
                typeSelect.val(defaultTipe);

                // simpan default terakhir (biar konsisten)
                $("[name=tipe_harga_default]").val(defaultTipe);

                // ===============================
                // SET HARGA & SUBTOTAL
                // ===============================
                let hargaObj = result.datas.find(d => d.tipe_harga === defaultTipe);
                let harga = Math.round(hargaObj.harga);

                let qty = $("#qty\\[" + dataId + "\\]").val();
                qty = qty ? Math.round(qty) : 1;

                let subtotal = Math.round(qty * harga);

                $("#harga_jual\\[" + dataId + "\\]").val(harga);
                $("#subtotal\\[" + dataId + "\\]").val(subtotal);
                updateRingkasan();
            },
            error: function (err) {
                console.error("Error loadTypeHarga:", err);
            }
        });
    }


    function loadCalculateSubtotalDtl(dataId, barangId, satuanId, tipeHarga, hargaJual, qty) {
        var subtotal = 0;

        //disini ambil dulu data barangnya
        $.ajax({
            url: "/penjualan/barang/" + barangId + "/get-detail-barang",
            type: 'get',
            success: function(result) {
                if (result.success) {
                    console.log("INI NAMA KATEGORINYA DUDE==>", result.data.kategori.nama_kategori);
                    
                    var subtotal = 0;
                    if (
                        result.data.kategori.kode_kategori.toLowerCase() == 'rokok' &&
                        result.data.jenis == 'legal' &&
                        tipeHarga == 'grosir' &&
                        satuanId == 2
                    ) {
                        if (qty > 0 && qty <=4) {
                            subtotal = (qty*hargaJual)+500;
                        } else {
                            subtotal = (qty*hargaJual)+1000;
                        }
                        subtotal = Math.round(subtotal);
                        const subTotalDetail = loadPembulatanSubtotalDetail(subtotal);
                        $("#subtotal\\[" + dataId + "\\]").val(subTotalDetail);
                    } else if (
                        tipeHarga == 'grosir' &&
                        result.data.kategori.nama_kategori.toLowerCase() == 'barang timbangan'
                    ) {
                        var namaSatuan = result.data.satuan.nama_satuan;
                        if (satuanId == 35 || satuanId == 37) {
                            subtotal = (qty*hargaJual);
                            subtotal = Math.ceil(subtotal / 1000) * 1000;
                            subtotal = subtotal+1000;
                            console.log("INI SUBTOTAL BARANG TIMBANGANNYA DUDE==>", subtotal);
                        } else {
                            subtotal = qty*hargaJual;
                        }
                        subtotal = Math.round(subtotal);
                        $("#subtotal\\[" + dataId + "\\]").val(subtotal);
                    } else {
                        subtotal = qty*hargaJual;
                        subtotal = Math.round(subtotal);
                        $("#subtotal\\[" + dataId + "\\]").val(subtotal);
                    }
                    
                    // const subTotalDetail = loadPembulatanSubtotalDetail(subtotal);
                    // $("#subtotal\\[" + dataId + "\\]").val(subTotalDetail);
                    // 🔥 UPDATE RINGKASAN
                    updateRingkasan();
                    
                }
            }
        })

        $("#harga_jual\\[" + dataId + "\\]").val(hargaJual);
    }

    function loadPembulatanSubtotalDetailTimbangan(subtotal) {
        const remainder = Math.round(subtotal % 1000); // Round remainder to nearest integer
        let pembulatan = 0;
        let subTotalDetail = 0;
        
        if (remainder === 0) {
            pembulatan = 0;
        } else {
            pembulatan = 1000-remainder;
        }
        subTotalDetail = subtotal+pembulatan;
        subTotalDetail = Math.round(subTotalDetail);
        return subTotalDetail;
    }

    function loadPembulatanSubtotalDetail(subtotal) {
        const remainder = Math.round(subtotal % 1000); // Round remainder to nearest integer
        let pembulatan = 0;
        let subTotalDetail = 0;

        if (remainder === 0) {
            pembulatan = 0;
        } else if (remainder >= 1 && remainder <= 499) {
            // Bulat ke 500
            pembulatan = 500 - remainder;
        } else {
            // remainder >= 500, bulat ke 1000
            pembulatan = 1000 - remainder;
        }
        subTotalDetail = subtotal+pembulatan;
        subTotalDetail = Math.round(subTotalDetail);

        return subTotalDetail;
    }

    function addNewRow() {
        let lastRow = $(".detail-row").last();
        let lastIndex = parseInt(lastRow.data("row")) || 0;
        let newIndex = lastIndex + 1;

        let lastBarangId = $("#barang_id\\[" + lastIndex + "\\]").val();
        if (!lastBarangId) return;

        let newRow = `
        <div class="row align-items-center g-2 py-2 border-bottom detail-row" data-row="${newIndex}">

            <div class="col-4">
                <input type="text" class="form-control barang-autocomplete"
                    id="barang[${newIndex}]" data-id="${newIndex}">
                <input type="hidden" name="detail[${newIndex}][barang_id]"
                    id="barang_id[${newIndex}]" data-id="${newIndex}">
            </div>

            <div class="col-2">
                <select class="form-control satuan-select"
                    name="detail[${newIndex}][satuan_id]"
                    id="satuan_id[${newIndex}]" data-id="${newIndex}">
                </select>
            </div>

            <div class="col-2">
                <select class="form-control tipe-harga-select"
                    name="detail[${newIndex}][tipe_harga]"
                    id="tipe_harga[${newIndex}]" data-id="${newIndex}">
                </select>
            </div>

            <div class="col-1">
                <input type="number" class="form-control qty-input"
                    name="detail[${newIndex}][qty]"
                    id="qty[${newIndex}]" data-id="${newIndex}">
            </div>

            <div class="col-1">
                <input type="number" class="form-control harga-jual-input"
                    name="detail[${newIndex}][harga_jual]"
                    id="harga_jual[${newIndex}]" readonly>
            </div>

            <div class="col-1">
                <input type="number" class="form-control subtotal-input"
                    name="detail[${newIndex}][subtotal]"
                    id="subtotal[${newIndex}]" readonly>
            </div>

            <div class="col-1">
                <button type="button" class="btn btn-danger btn-sm remove-row">
                    <i class="fas fa-trash"></i>
                </button>
            </div>

        </div>
        `;

        $("#detailContainer").append(newRow);

        setTimeout(() => {
            $("#barang\\[" + newIndex + "\\]").focus().select();
        }, 150);
    }


    function hitungGrandSubtotal() {
        let total = 0;

        $(".subtotal-input").each(function () {
            let val = parseFloat($(this).val());
            if (!isNaN(val)) {
                total += val;
            }
        });

        return Math.round(total);
    }

    function updateRingkasan() {
        let subtotal = hitungGrandSubtotal();

        // pembulatan global (pakai logic yang sama)
        let remainder = subtotal % 1000;
        let pembulatan = 0;

        if (remainder >= 1 && remainder <= 500) {
            pembulatan = 500 - remainder;
        } else if (remainder >= 501) {
            pembulatan = 1000 - remainder;
        }

        let grandTotal = subtotal + pembulatan;

        // === RINGKASAN ATAS ===
        $("#subtotal").val(subtotal);
        $("#pembulatan").val(pembulatan);
        $("#summaryGrandTotal").val(grandTotal);

        // === RINGKASAN BAWAH ===
        $("#grandSubtotal").val(subtotal);
        $("#grandPembulatan").val(pembulatan);
        $("#paymentGrandTotal").val(grandTotal);
        $("#paymentGrandTotalValue").val(grandTotal);

        console.log("INI LIST BARANG IDNYA DUDE==>", getListBarangId());
    }

    $(document).on("click", ".remove-row", function () {
        $(this).closest(".detail-row").remove();
        updateRingkasan();
        toggleRemoveButton();
    });

    $(document).on('click', '#btnSimpanCetak', function () {
        console.log("TOMBOL SIMPAN & CETAK DUDE==>");

        // ================= DEFAULT CATATAN =================
        if ($("#catatan").val() === null || $("#catatan").val() === '') {
            $("#catatan").val('-');
        }

        let formData = new FormData();

        // ================= HEADER =================
        let grandTotal = parseFloat($("#paymentGrandTotalValue").val()) || 0;
        let dibayar    = parseFloat($("#dibayar").val()) || 0;
        let jenisPembayaran = $("#jenis_pembayaran").val();

        if (!jenisPembayaran) {
            alert("Pilih jenis pembayaran");
            return;
        }

        if (jenisPembayaran == 'tunai' && dibayar < grandTotal) {
            alert("Total pembayaran kurang dari Grand Total");
            return;
        }

        let kembalian = dibayar - grandTotal;

        formData.append("kode_penjualan", $("#kode_penjualan").val());
        formData.append("tanggal_penjualan", $("#tanggal_penjualan").val());
        formData.append("pelanggan_id", $("#pelanggan_id").val());
        formData.append("catatan", $("#catatan").val());
        formData.append("grand_total", grandTotal);

        // ================= PEMBAYARAN =================
        formData.append("jenis_pembayaran", jenisPembayaran);
        formData.append("dibayar", dibayar);
        formData.append("kembalian", kembalian);

        // ================= DETAIL =================
        $(".detail-row").each(function (i) {
            let idx = $(this).data("row");

            let barangId = $("#barang_id\\[" + idx + "\\]").val();
            if (!barangId) return;

            formData.append(`detail[${i}][barang_id]`, barangId);
            formData.append(`detail[${i}][satuan_id]`, $("#satuan_id\\[" + idx + "\\]").val());
            formData.append(`detail[${i}][tipe_harga]`, $("#tipe_harga\\[" + idx + "\\]").val());
            formData.append(`detail[${i}][qty]`, $("#qty\\[" + idx + "\\]").val());
            formData.append(`detail[${i}][harga_jual]`, $("#harga_jual\\[" + idx + "\\]").val());
            formData.append(`detail[${i}][subtotal]`, $("#subtotal\\[" + idx + "\\]").val());
        });

        // ================= AJAX =================
        $.ajax({
            url: "{{ route('penjualan.store') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            },
            beforeSend: function () {
                $("#btnSimpanCetak")
                    .prop("disabled", true)
                    .text("Menyimpan...");
            },
            success: function (response) {
                console.log("RESPONSE==>", response.success);
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1000
                    }).then(function() {
                        window.open('/penjualan/' + response.data.id + '/print', '_blank');
                        // Reload the current create page
                        window.location.reload();
                    });
                }
            },
            error: function (err) {
                console.error(err);
                alert("Gagal simpan penjualan");
            },
            complete: function () {
                $("#btnSimpanCetak")
                    .prop("disabled", false)
                    .text("Simpan & Cetak");
            }
        });
    });





    function getListBarangId() {
        let barangIds = [];

        $(".detail-row").each(function () {
            let idx = $(this).data("row");
            let barangId = $("#barang_id\\[" + idx + "\\]").val();

            if (barangId) {
                barangIds.push(parseInt(barangId));
            }
        });

        return barangIds;
    }

    function getBarangQtyMap() {
        let qtyMap = {};

        $(".detail-row").each(function () {
            let idx = $(this).data("row");
            let barangId = $("#barang_id\\[" + idx + "\\]").val();
            let qty = $("#qty\\[" + idx + "\\]").val();

            if (barangId && qty > 0) {
                qtyMap[barangId] = parseInt(qty);
            }
        });

        return qtyMap;
    }

    function cekPaketBarang() {
        let barangIds = getListBarangId();
        let qtyMap = getBarangQtyMap();

        if (barangIds.length === 0) return;


        $.ajax({
            url: "{{ route('penjualan.get-paket-barang') }}",
            type: "GET",
            data: {
                barang_ids: barangIds,
                qty_map: qtyMap
            },
            success: function (result) {
                console.log("INI PAKET BARANGNYA DUDE==>", result);
                
                if (result.success && result.paket_details.length > 0) {
                    applyPaketToItems(result.paket_details);
                    updateRingkasan();
                }
            },
            error: function (err) {
                console.error("Error cek paket barang:", err);
            }
        });
    }

    function applyPaketToItems(paketDetails) {
        // Loop through each paket detail
        paketDetails.forEach(function(paket) {
            paket.items.forEach(function(item) {
                // Update harga dan subtotal di UI untuk barang yang masuk paket
                updateItemPriceFromPaket(item.barang_id, item.harga_setelah_paket, item.subtotal_setelah_paket);
            });
        });
    }

    function updateItemPriceFromPaket(barangId, hargaPaket, subtotalPaket) {
        // Cari semua row yang memiliki barang_id tersebut dan update harga serta subtotal
        $(".detail-row").each(function() {
            let idx = $(this).data("row");
            let rowBarangId = $("#barang_id\\[" + idx + "\\]").val();

            if (rowBarangId == barangId) {
                // Update harga jual dengan harga paket
                $("#harga_jual\\[" + idx + "\\]").val(hargaPaket);
                
                // Update subtotal dengan subtotal paket
                $("#subtotal\\[" + idx + "\\]").val(subtotalPaket);
                
                console.log("Updated barang ID " + barangId + " dengan harga paket " + hargaPaket);
            }
        });
    }

    function toggleRemoveButton() {
        let totalRows = $(".detail-row").length;

        if (totalRows <= 1) {
            $(".remove-row").hide();
        } else {
            $(".remove-row").show();
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

    $(document).on('change', '#jenis_pembayaran', function () {
        togglePaymentFields();
    });

    function calculateKembalian() {
        const jenis = $('#jenis_pembayaran').val();
        if (jenis === 'tunai') {
            const grandTotal = parseFloat($('#paymentGrandTotalValue').val()) || 0;
            const dibayar = parseFloat($('#dibayar').val()) || 0;
            const kembalian = dibayar - grandTotal;
            $('#kembalian').val(Math.max(0, kembalian).toLocaleString('id-ID'));
        }
    }

    $(document).on('keyup', '#dibayar', function() {
        calculateKembalian()
    });


});
</script>



</body>
</html>