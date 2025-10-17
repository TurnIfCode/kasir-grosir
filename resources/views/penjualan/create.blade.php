@include('layout.header')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Tambah Penjualan</h5>
                    <a href="{{ route('penjualan.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <form id="penjualanForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kode_penjualan" class="form-label">Kode Penjualan</label>
                                    <input type="text" class="form-control" id="kode_penjualan" name="kode_penjualan" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tanggal_penjualan" class="form-label">Tanggal Penjualan <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tanggal_penjualan" name="tanggal_penjualan" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pelanggan_id" class="form-label">Pelanggan</label>
                                    <select class="form-select" id="pelanggan_id" name="pelanggan_id">
                                        <option value="">Pilih Pelanggan</option>
                                        @foreach($pelanggans as $pelanggan)
                                            <option value="{{ $pelanggan->id }}">{{ $pelanggan->nama_pelanggan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="catatan" class="form-label">Catatan</label>
                                    <textarea class="form-control" id="catatan" name="catatan" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Barang -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Detail Barang</h6>
                            </div>
                            <div class="card-body">
                                <div id="detailContainer">
                                    <!-- Dynamic rows will be added here -->
                                </div>
                                <button type="button" class="btn btn-success btn-sm" onclick="addNewRow()">
                                    <i class="fas fa-plus"></i> Tambah Barang
                                </button>
                            </div>
                        </div>



                        <!-- Diskon -->
                        <div class="card mt-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="diskon" class="form-label">Diskon</label>
                                            <input type="number" class="form-control" id="diskon" name="diskon" value="0" min="0" step="0.01" onchange="calculateTotal()">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ppn" class="form-label">PPN</label>
                                            <input type="number" class="form-control" id="ppn" name="ppn" value="0" min="0" step="0.01" onchange="calculateTotal()">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="card mt-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Total</label>
                                            <input type="text" class="form-control" id="total" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Grand Total</label>
                                            <input type="text" class="form-control" id="grandTotal" readonly>
                                            <input type="hidden" id="grandTotalValue" name="grand_total">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Jenis Pembayaran -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Jenis Pembayaran</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="jenis_pembayaran" class="form-label">Jenis Pembayaran <span class="text-danger">*</span></label>
                                            <select class="form-select" id="jenis_pembayaran" name="jenis_pembayaran" required onchange="togglePaymentFields()">
                                                <option value="">Pilih Jenis</option>
                                                <option value="tunai">Tunai</option>
                                                <option value="non_tunai">Non Tunai</option>
                                                <option value="campuran">Campuran</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3 tunai-field" style="display: none;">
                                            <label for="dibayar" class="form-label">Dibayar</label>
                                            <input type="number" class="form-control" id="dibayar" name="dibayar" min="0" step="0.01" onchange="calculateKembalian()" onkeyup="calculateKembalian()">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3 tunai-field" style="display: none;">
                                            <label for="kembalian" class="form-label">Kembalian</label>
                                            <input type="number" class="form-control" id="kembalian" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Campuran payment fields -->
                                <div id="campuranFields" style="display: none;">
                                    <h6>Pembayaran Campuran</h6>
                                    <div id="paymentContainer">
                                        <!-- Dynamic payment rows will be added here -->
                                    </div>
                                    <button type="button" class="btn btn-info btn-sm" onclick="addPaymentRow()">
                                        <i class="fas fa-plus"></i> Tambah Pembayaran
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="button" class="btn btn-primary" onclick="submitForm()">
                                <i class="fas fa-save"></i> Simpan Penjualan
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/penjualan-form.js') }}"></script>

@include('layout.footer')
