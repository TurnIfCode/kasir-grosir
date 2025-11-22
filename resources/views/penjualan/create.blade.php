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

    <form id="penjualanForm">
        @csrf

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
                                <input type="text" class="form-control" id="kode_penjualan" name="kode_penjualan" readonly>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="tanggal_penjualan" class="form-label fw-semibold fs-6">Tanggal Penjualan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_penjualan" name="tanggal_penjualan" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="pelanggan_autocomplete" class="form-label fw-semibold fs-6">Pelanggan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="pelanggan_autocomplete" placeholder="Cari pelanggan..." value="{{ $defaultPelanggan ? $defaultPelanggan->nama_pelanggan : '' }}" required>
                                <input type="hidden" id="pelanggan_id" name="pelanggan_id" value="{{ $defaultPelanggan ? $defaultPelanggan->id : '' }}">
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
                                <input type="hidden" id="grandTotalValue" name="grand_total">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Barang -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                <h5 class="card-title mb-0">Detail Barang</h5>
                <button type="button" class="btn btn-primary w-100 w-sm-auto" onclick="addNewRow()">
                    <i class="fas fa-plus me-2"></i>Tambah Barang
                </button>
            </div>
            <div class="card-body p-1 p-md-3">
                <div class="table-responsive">
                    <table class="table table-hover table-sm" id="detailTable">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold fs-7">Barang</th>
                                <th class="fw-semibold fs-7">Satuan</th>
                                <th class="fw-semibold fs-7">Tipe Harga</th>
                                <th class="fw-semibold fs-7">Qty</th>
                                <th class="fw-semibold fs-7">Harga Satuan</th>
                                <th class="fw-semibold fs-7">Subtotal</th>
                                <th class="fw-semibold fs-7">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="detailContainer">
                            <!-- Dynamic rows will be added here -->
                        </tbody>
                    </table>
                </div>
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
                                <select class="form-select" id="jenis_pembayaran" name="jenis_pembayaran" required onchange="togglePaymentFields()">
                                    <option value="">Pilih Jenis</option>
                                    <option value="tunai">Tunai</option>
                                    <option value="non_tunai">Non Tunai</option>
                                    <option value="campuran">Campuran</option>
                                </select>
                            </div>
                            <div class="col-6 tunai-field" style="display: none;">
                                <label for="dibayar" class="form-label fw-semibold">Nominal Bayar</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control fw-bold fs-5" id="dibayar" name="dibayar" min="0" step="0.01" onchange="calculateKembalian()" onkeyup="calculateKembalian()" required>
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
                                <input type="hidden" id="grandTotalValue" name="grand_total">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="row mt-3 mt-md-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                    <button type="button" class="btn btn-primary btn-lg px-4 w-100 w-sm-auto" onclick="submitForm(true)">
                        <i class="fas fa-save me-2"></i>Simpan & Cetak
                    </button>
                    <button type="button" class="btn btn-secondary btn-lg px-4 w-100 w-sm-auto" onclick="submitForm(false)">
                        <i class="fas fa-save me-2"></i>Simpan Saja
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg px-4 w-100 w-sm-auto" onclick="resetForm()">
                        <i class="fas fa-undo me-2"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="{{ asset('js/penjualan-form.js') }}"></script>

<script>
$(document).ready(function() {
    // Initialize pelanggan autocomplete
    $('#pelanggan_autocomplete').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '/pelanggan/search',
                data: { q: request.term },
                success: function(data) {
                    if (data.status === 'success') {
                        response(data.data.map(item => ({
                            label: `${item.kode_pelanggan} - ${item.nama_pelanggan}`,
                            value: item.nama_pelanggan,
                            id: item.id
                        })));
                    }
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $(this).val(ui.item.value);
            $('#pelanggan_id').val(ui.item.id);
            return false;
        }
    });
});
</script>

@include('layout.footer')