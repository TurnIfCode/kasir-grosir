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
                            <th>Potongan</th>
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
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Total Transaksi</h6>
                                <h4 id="total-transaksi">0</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h6>Total Laba Kotor</h6>
                                <h4 id="total-laba-kotor">Rp 0</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h6>Total Modal</h6>
                                <h4 id="total-modal">Rp 0</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>Laba Bersih</h6>
                                <h4 id="total-laba-bersih">Rp 0</h4>
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
                    { data: 'tanggal_penjualan_formatted', searchable: false },
                    { data: 'nama_pelanggan', searchable: false },
                    { data: 'jumlah_item', searchable: false },
                    { data: 'total_modal_formatted', searchable: false },
                    { data: 'total_formatted', searchable: false },
                    { data: 'potongan_formatted', searchable: false },
                    { data: 'pembulatan_formatted', searchable: false },
                    { data: 'grand_total_formatted', searchable: false },

                    // nilai mentah dibutuhkan untuk ringkasan + tampil format rupiah
                    { 
                        data: 'dibayar', 
                        visible: true, 
                        searchable: false,
                        render: function(data) {
                            var n = parseFloat(data) || 0;
                            return 'Rp ' + n.toLocaleString('id-ID');
                        }
                    },
                    { 
                        data: 'kembalian', 
                        visible: true, 
                        searchable: false,
                        render: function(data) {
                            var n = parseFloat(data) || 0;
                            return 'Rp ' + n.toLocaleString('id-ID');
                        }
                    },

                    { data: 'metode_pembayaran', searchable: false },
                    { data: 'kasir_name', searchable: false },
                    { data: 'laba_kotor_formatted', searchable: false },
                    { data: 'laba_bersih_formatted', searchable: false },
                    { data: 'status_badge', orderable: false, searchable: false },
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
                // Calculate totals from current page data
                var pageData = table.rows({ page: 'current' }).data();
                var totals = {
                    transaksi: 0, // jumlah transaksi (count)
                    laba_kotor: 0,
                    modal: 0
                };

                pageData.each(function(row) {
                    totals.transaksi += 1;

                    // Total Laba Kotor = (dibayar - kembalian)
                    var dibayar = parseFloat(row.dibayar) || 0;
                    var kembalian = parseFloat(row.kembalian) || 0;
                    totals.laba_kotor += (dibayar - kembalian);

                    // Total Modal = sum(harga_beli) = total_hpp dari backend
                    totals.modal += parseFloat(row.total_hpp) || 0;
                });

                // Total transaksi = jumlah penjualan (count rows)
                $('#total-transaksi').text(totals.transaksi);

                $('#total-laba-kotor').text('Rp ' + totals.laba_kotor.toLocaleString('id-ID'));
                $('#total-modal').text('Rp ' + totals.modal.toLocaleString('id-ID'));

                // Laba Bersih = (dibayar - kembalian) - Total modal
                var laba_bersih = totals.laba_kotor - totals.modal;
                $('#total-laba-bersih').text('Rp ' + laba_bersih.toLocaleString('id-ID'));
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
