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
            <div class="col-2">Harga Satuan</div>
            <div class="col-1">Aksi</div>
        </div>

        <form id="detailContainer" class="mt-2">

            <!-- ROW 0 -->
            <div class="row align-items-center g-2 py-2 border-bottom detail-row" data-row="0">

                <div class="col-4">
                    <input type="text" class="form-control barang-autocomplete"
                        name="barang" id="barang[0]" data-id="0" placeholder="Cari barang...">
                    <input type="hidden" name="barang_id" id="barang_id[0]" data-id="0">
                </div>

                <div class="col-2">
                    <select class="form-control satuan-select" name="satuan_id" id="satuan_id[0]" data-id="0">
                        <option value="">Pilih</option>
                    </select>
                </div>

                <div class="col-2">
                    <select class="form-control tipe-harga-select" name="type_harga" id="tipe_harga[0]" data-id="0">
                    </select>
                </div>

                <div class="col-1">
                    <input type="number" class="form-control qty-input" name="qty" id="qty[0]" data-id="0">
                </div>

                <div class="col-2">
                    <input type="number" class="form-control harga-jual-input"
                        name="harga_jual" id="harga_jual[0]" data-id="0" data-id="0" readonly>
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

<script>
    $("")
</script>