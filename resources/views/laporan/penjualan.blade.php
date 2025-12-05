<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    @include('layout.header')

    <div class="container mt-4">
        <h1 class="mb-4">Laporan Penjualan</h1>

        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-3">
                        <label for="tanggal_dari" class="form-label">Tanggal Dari</label>
                        <input type="date" class="form-control" id="tanggal_dari" name="tanggal_dari" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="tanggal_sampai" class="form-label">Tanggal Sampai</label>
                        <input type="date" class="form-control" id="tanggal_sampai" name="tanggal_sampai" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="pelanggan_id" class="form-label">Pelanggan</label>
                        <select class="form-control" id="pelanggan_id" name="pelanggan_id">
                            <option value="all">Semua Pelanggan</option>
                            @foreach($pelanggans as $pelanggan)
                                <option value="{{ $pelanggan->id }}">{{ $pelanggan->nama_pelanggan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="all">Semua Status</option>
                            <option value="selesai">Selesai</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                        <select class="form-control" id="metode_pembayaran" name="metode_pembayaran">
                            <option value="all">Semua Metode</option>
                            <option value="tunai">Tunai</option>
                            <option value="non_tunai">Non-Tunai</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" id="btnFilter">Filter</button>
                        <button type="button" class="btn btn-secondary" id="btnReset">Reset</button>
                        <!-- <button type="button" class="btn btn-success" id="btnExportPDF">Export PDF</button> -->
                        <button type="button" class="btn btn-info" id="btnExportExcel">Export Excel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-body">
                <table id="laporanTable" class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Penjualan</th>
                            <th>Tanggal Penjualan</th>
                            <th>Nama Pelanggan</th>
                            <th>Jumlah Item</th>
                            <th>Total Modal</th>
                            <th>Total Penjualan</th>
                            <th>Pembulatan</th>
                            <th>Grand Total</th>
                            <th>Dibayar</th>
                            <th>Kembalian</th>
                            <th>Metode Pembayaran</th>
                            <th>Kasir</th>
                            <th>Laba</th>
                            <th>Laba Bersih</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Ringkasan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Total Transaksi</h6>
                                <h4 id="total-transaksi">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>Total Penjualan</h6>
                                <h4 id="total-penjualan">Rp 0</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="card bg-secondary text-white">
                            <div class="card-body">
                                <h6>Total Pembulatan</h6>
                                <h4 id="total-pembulatan">Rp 0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h6>Total Laba Kotor</h6>
                                <h4 id="total-grand-total">Rp 0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h6>Total Modal (HPP)</h6>
                                <h4 id="total-modal">Rp 0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>Laba Bersih</h6>
                                <h4 id="total-laba">Rp 0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Penjualan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="detailContent">
                        <!-- Detail content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layout.footer')

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#laporanTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("laporan.penjualan.data") }}',
                    data: function(d) {
                        d.tanggal_dari = $('#tanggal_dari').val();
                        d.tanggal_sampai = $('#tanggal_sampai').val();
                        d.pelanggan_id = $('#pelanggan_id').val();
                        d.status = $('#status').val();
                        d.metode_pembayaran = $('#metode_pembayaran').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'kode_penjualan' },
                    { data: 'tanggal_penjualan_formatted' },
                    { data: 'nama_pelanggan' },
                    { data: 'jumlah_item' },
                    { data: 'total_modal_formatted' },
                    { data: 'total_formatted' },
                    { data: 'pembulatan_formatted' },
                    { data: 'grand_total_formatted' },
                    { data: 'dibayar_formatted' },
                    { data: 'kembalian_formatted' },
                    { data: 'metode_pembayaran' },
                    { data: 'kasir_name' },
                    { data: 'laba_kotor_formatted' },
                    { data: 'laba_bersih_formatted' },
                    { data: 'status_badge', orderable: false },
                    { data: 'action', orderable: false, searchable: false }
                ],
                language: {
                    emptyTable: "Tidak ada data penjualan pada periode ini."
                },
                drawCallback: function() {
                    updateSummary();
                }
            });

            function updateSummary() {
                var info = table.page.info();
                $('#total-transaksi').text(info.recordsTotal);

                // Calculate totals from current page data
                var pageData = table.rows({ page: 'current' }).data();
                var totals = {
                    penjualan: 0,
                    pembulatan: 0,
                    grand_total: 0,
                    modal: 0,
                    laba_kotor: 0,
                    laba_bersih: 0
                };

                pageData.each(function(row) {
                    totals.penjualan += parseFloat(row.total) || 0;
                    totals.pembulatan += parseFloat(row.pembulatan) || 0;
                    totals.grand_total += parseFloat(row.grand_total) || 0;
                    totals.modal += parseFloat(row.total_hpp) || 0;
                    totals.laba_kotor += parseFloat(row.laba) || 0;
                    totals.laba_bersih += parseFloat(row.laba) || 0;
                });

                $('#total-penjualan').text('Rp ' + totals.penjualan.toLocaleString('id-ID'));
                $('#total-pembulatan').text('Rp ' + totals.pembulatan.toLocaleString('id-ID'));
                $('#total-grand-total').text('Rp ' + totals.grand_total.toLocaleString('id-ID'));
                $('#total-modal').text('Rp ' + totals.modal.toLocaleString('id-ID'));
                $('#total-laba').text('Rp ' + (totals.grand_total - totals.modal).toLocaleString('id-ID'));
                $('#total-laba-bersih').text('Rp ' + totals.laba_bersih.toLocaleString('id-ID'));
            }

            $('#btnFilter').on('click', function() {
                table.ajax.reload();
            });

            $('#btnReset').on('click', function() {
                $('#tanggal_dari').val('');
                $('#tanggal_sampai').val('');
                $('#pelanggan_id').val('all');
                $('#status').val('all');
                $('#metode_pembayaran').val('all');
                table.ajax.reload();
            });

            $('#btnExportPDF').on('click', function() {
                var params = {
                    tanggal_dari: $('#tanggal_dari').val(),
                    tanggal_sampai: $('#tanggal_sampai').val(),
                    pelanggan_id: $('#pelanggan_id').val(),
                    status: $('#status').val(),
                    metode_pembayaran: $('#metode_pembayaran').val()
                };
                var queryString = $.param(params);
                window.open('{{ route("laporan.penjualan.export_pdf") }}?' + queryString, '_blank');
            });

            $('#btnExportExcel').on('click', function() {
                var params = {
                    tanggal_dari: $('#tanggal_dari').val(),
                    tanggal_sampai: $('#tanggal_sampai').val(),
                    pelanggan_id: $('#pelanggan_id').val(),
                    status: $('#status').val(),
                    metode_pembayaran: $('#metode_pembayaran').val()
                };
                var queryString = $.param(params);
                window.open('{{ route("laporan.penjualan.export_excel") }}?' + queryString, '_blank');
            });

            // Handle detail button click
            $(document).on('click', '.detail-btn', function() {
                var penjualanId = $(this).data('id');
                loadDetail(penjualanId);
            });

            function loadDetail(penjualanId) {
                $.ajax({
                    url: '{{ route("laporan.penjualan.detail", ":id") }}'.replace(':id', penjualanId),
                    method: 'GET',
                    success: function(response) {
                        $('#detailContent').html(response);
                        $('#detailModal').modal('show');
                    },
                    error: function() {
                        alert('Gagal memuat detail penjualan');
                    }
                });
            }
        });
    </script>
</body>
</html>
