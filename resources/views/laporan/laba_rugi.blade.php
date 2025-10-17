<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Laba Rugi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    @include('layout.header')

    <div class="container mt-4">
        <h1 class="mb-4">Laporan Laba Rugi</h1>

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
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" id="btnFilter">Filter</button>
                        <button type="button" class="btn btn-secondary" id="btnReset">Reset</button>
                        <button type="button" class="btn btn-success" id="btnExportPDF">Export PDF</button>
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
                            <th>Tanggal</th>
                            <th>Total Penjualan</th>
                            <th>Total Pembelian</th>
                            <th>Laba Kotor</th>
                            <th>Laba Bersih</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals -->
        <div class="card mt-4">
            <div class="card-body">
                <h5>Ringkasan Total</h5>
                <div class="row">
                    <div class="col-md-3">
                        <strong>Total Penjualan:</strong> <span id="totalPenjualan">Rp 0</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Total Pembelian:</strong> <span id="totalPembelian">Rp 0</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Laba Kotor:</strong> <span id="totalLabaKotor">Rp 0</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Laba Bersih:</strong> <span id="totalLabaBersih">Rp 0</span>
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
                    url: '{{ route("laporan.laba-rugi.data") }}',
                    data: function(d) {
                        d.tanggal_dari = $('#tanggal_dari').val();
                        d.tanggal_sampai = $('#tanggal_sampai').val();
                    },
                    dataSrc: function(json) {
                        // Calculate totals
                        var totalPenjualan = 0;
                        var totalPembelian = 0;
                        var totalLabaKotor = 0;
                        var totalLabaBersih = 0;

                        json.data.forEach(function(row) {
                            totalPenjualan += parseFloat(row.total_penjualan) || 0;
                            totalPembelian += parseFloat(row.total_pembelian) || 0;
                            totalLabaKotor += parseFloat(row.laba_kotor) || 0;
                            totalLabaBersih += parseFloat(row.laba_bersih) || 0;
                        });

                        $('#totalPenjualan').text('Rp ' + totalPenjualan.toLocaleString('id-ID'));
                        $('#totalPembelian').text('Rp ' + totalPembelian.toLocaleString('id-ID'));
                        $('#totalLabaKotor').text('Rp ' + totalLabaKotor.toLocaleString('id-ID'));
                        $('#totalLabaBersih').text('Rp ' + totalLabaBersih.toLocaleString('id-ID'));

                        return json.data;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'tanggal_formatted' },
                    { data: 'total_penjualan_formatted' },
                    { data: 'total_pembelian_formatted' },
                    { data: 'laba_kotor_formatted' },
                    { data: 'laba_bersih_formatted' }
                ],
                language: {
                    emptyTable: "Tidak ada transaksi pada periode ini."
                }
            });

            $('#btnFilter').on('click', function() {
                table.ajax.reload();
            });

            $('#btnReset').on('click', function() {
                $('#tanggal_dari').val('');
                $('#tanggal_sampai').val('');
                table.ajax.reload();
            });

            $('#btnExportPDF').on('click', function() {
                var params = {
                    tanggal_dari: $('#tanggal_dari').val(),
                    tanggal_sampai: $('#tanggal_sampai').val()
                };
                var queryString = $.param(params);
                window.open('{{ route("laporan.laba-rugi.export_pdf") }}?' + queryString, '_blank');
            });
        });
    </script>
</body>
</html>
