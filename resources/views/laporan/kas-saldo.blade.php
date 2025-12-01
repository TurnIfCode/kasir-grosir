<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Saldo Kas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    @include('layout.header')

    <div class="container mt-4">
        <h1 class="mb-4">Laporan Saldo Kas</h1>

        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Tanggal Dari</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Tanggal Sampai</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="nama_kas" class="form-label">Nama Kas</label>
                        <select class="form-control" id="nama_kas" name="nama_kas">
                            <option value="all">Semua Kas</option>
                            @foreach($kasOptions as $kas)
                                <option value="{{ $kas }}">{{ $kas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" id="btnFilter">Filter</button>
                        <button type="button" class="btn btn-secondary" id="btnReset">Reset</button>
                        <button type="button" class="btn btn-success" id="btnExportPDF">Export PDF</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Ringkasan -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Ringkasan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6>Total Saldo Awal</h6>
                            <h4 id="totalSaldoAwal">-</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6>Total Masuk</h6>
                            <h4 id="totalMasuk">-</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6>Total Keluar</h6>
                            <h4 id="totalKeluar">-</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6>Total Saldo Akhir</h6>
                            <h4 id="totalSaldoAkhir">-</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-body">
                <table id="laporanTable" class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kas</th>
                            <th>Saldo Awal</th>
                            <th>Total Masuk</th>
                            <th>Total Keluar</th>
                            <th>Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
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
                    url: '{{ route("laporan.kas-saldo.data") }}',
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.nama_kas = $('#nama_kas').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nama_kas' },
                    { data: 'saldo_awal_formatted' },
                    { data: 'total_masuk_formatted' },
                    { data: 'total_keluar_formatted' },
                    { data: 'saldo_akhir_formatted' }
                ],
                language: {
                    emptyTable: "Tidak ada data kas pada periode ini."
                },
                drawCallback: function() {
                    updateRingkasan();
                }
            });

            function updateRingkasan() {
                $.ajax({
                    url: '{{ route("laporan.kas-saldo.ringkasan") }}',
                    data: {
                        start_date: $('#start_date').val(),
                        end_date: $('#end_date').val(),
                        nama_kas: $('#nama_kas').val()
                    },
                    success: function(response) {
                        $('#totalSaldoAwal').text(response.total_saldo_awal);
                        $('#totalMasuk').text(response.total_masuk);
                        $('#totalKeluar').text(response.total_keluar);
                        $('#totalSaldoAkhir').text(response.total_saldo_akhir);
                    }
                });
            }

            $('#btnFilter').on('click', function() {
                table.ajax.reload();
            });

            $('#btnReset').on('click', function() {
                $('#start_date').val('{{ date('Y-m-d') }}');
                $('#end_date').val('{{ date('Y-m-d') }}');
                $('#nama_kas').val('all');
                table.ajax.reload();
            });

            $('#btnExportPDF').on('click', function() {
                var params = {
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val(),
                    nama_kas: $('#nama_kas').val()
                };
                var queryString = $.param(params);
                window.open('{{ route("laporan.kas-saldo.export_pdf") }}?' + queryString, '_blank');
            });

            // Initial load
            updateRingkasan();
        });
    </script>
</body>
</html>
