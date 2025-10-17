<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stok Barang</title>

    <!-- Bootstrap & DataTables -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- jQuery UI for autocomplete -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
</head>
<body>
    @include('layout.header')

    <div class="container mt-4">
        <h1 class="mb-4">Laporan Stok Barang</h1>

        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-6">
                        <label for="barang_search" class="form-label">Pencarian Barang</label>
                        <input type="text" class="form-control" id="barang_search" name="barang_search" placeholder="Cari barang...">
                        <input type="hidden" id="barang_id" name="barang_id">
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
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Stok Akhir</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- Total Ringkasan -->
        <div class="card mt-4">
            <div class="card-body">
                <h5>Ringkasan Total</h5>
                <div class="row">
                    <div class="col-md-12">
                        <strong>Total Stok Akhir:</strong> <span id="totalStokAkhir">0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layout.footer')

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Autocomplete barang
            $('#barang_search').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '{{ route("laporan.stok-barang.search") }}',
                        dataType: 'json',
                        data: { term: request.term },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    label: item.kode_barang + ' - ' + item.nama_barang,
                                    value: item.nama_barang,
                                    id: item.id
                                };
                            }));
                        }
                    });
                },
                select: function(event, ui) {
                    $('#barang_id').val(ui.item.id);
                },
                minLength: 2
            });

            // Inisialisasi DataTable
            var table = $('#laporanTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("laporan.stok-barang.data") }}',
                    data: function(d) {
                        d.barang_id = $('#barang_id').val();
                    },
                    dataSrc: function(json) {
                        // Hitung total stok
                        var total = 0;
                        json.data.forEach(function(row) {
                            total += parseFloat(row.stok_akhir.replace(/\./g, '').replace(',', '.')) || 0;
                        });
                        $('#totalStokAkhir').text(total.toLocaleString('id-ID'));
                        return json.data;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'kode_barang' },
                    { data: 'nama_barang' },
                    { data: 'nama_kategori' },
                    { data: 'nama_satuan' },
                    { data: 'stok_akhir' }
                ],
                language: {
                    emptyTable: "Tidak ada data stok barang."
                }
            });

            // Filter dan Reset
            $('#btnFilter').on('click', function() {
                table.ajax.reload();
            });

            $('#btnReset').on('click', function() {
                $('#barang_search').val('');
                $('#barang_id').val('');
                table.ajax.reload();
            });

            // Export PDF
            $('#btnExportPDF').on('click', function() {
                var params = { barang_id: $('#barang_id').val() };
                window.open('{{ route("laporan.stok-barang.export_pdf") }}?' + $.param(params), '_blank');
            });
        });
    </script>
</body>
</html>
