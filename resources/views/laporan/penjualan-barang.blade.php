<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan per Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    @include('layout.header')

    <div class="container mt-4">
        <h1 class="mb-4">Laporan Penjualan per Barang</h1>

        <!-- Ringkasan Cards -->
        <div class="row mb-4" id="ringkasanCards">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total Produk Terjual</h5>
                        <h3 id="totalProdukTerjual">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Total Nilai Penjualan</h5>
                        <h3 id="totalNilaiPenjualan">Rp 0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Total Laba Bersih</h5>
                        <h3 id="totalLabaBersih">Rp 0</h3>
                    </div>
                </div>
            </div>
        </div>

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
                        <label for="kategori_id" class="form-label">Kategori</label>
                        <select class="form-control" id="kategori_id" name="kategori_id">
                            <option value="all">Semua Kategori</option>
                            @foreach($kategoris as $kategori)
                                <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="order_column" class="form-label">Urutkan Berdasarkan</label>
                        <select class="form-control" id="order_column" name="order_column">
                            <option value="jumlah_terjual">Jumlah Terjual</option>
                            <option value="margin_keuntungan">Margin Keuntungan</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" id="btnFilter">Filter</button>
                        <button type="button" class="btn btn-secondary" id="btnReset">Reset</button>
                        <!-- <button type="button" class="btn btn-success" id="btnExportPDF">Export PDF</button> -->
                        <button type="button" class="btn btn-success" id="btnExportExcel">Export Excel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Chart -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Grafik Penjualan per Barang (Top 10)</h5>
                <canvas id="penjualanChart" width="400" height="200"></canvas>
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
                            <th>Jumlah Terjual</th>
                            <th>Total Modal (HPP)</th>
                            <th>Total Penjualan</th>
                            <th>Laba Bersih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('layout.footer')

    <script>
        $(document).ready(function() {
            var table = $('#laporanTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("laporan.penjualan-barang.data") }}',
                    data: function(d) {
                        d.tanggal_dari = $('#tanggal_dari').val();
                        d.tanggal_sampai = $('#tanggal_sampai').val();
                        d.kategori_id = $('#kategori_id').val();
                        d.order_column = $('#order_column').val();
                        d.order_direction = 'desc';
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'kode_barang', searchable: false },
                    { data: 'nama_barang', searchable: false },
                    { data: 'nama_kategori', searchable: false },
                    { data: 'jumlah_terjual_formatted', searchable: false },
                    { data: 'total_modal_formatted', searchable: false },
                    { data: 'total_penjualan_formatted', searchable: false },
                    { data: 'laba_bersih_formatted', searchable: false }
                ],
                language: {
                    emptyTable: "Tidak ada data penjualan pada periode ini."
                }
            });

            // Load ringkasan dan chart saat halaman dimuat
            loadRingkasan();
            loadChart();

            $('#btnFilter').on('click', function() {
                table.ajax.reload();
                loadRingkasan();
                loadChart();
            });

            $('#btnReset').on('click', function() {
                $('#tanggal_dari').val('{{ date("Y-m-d") }}');
                $('#tanggal_sampai').val('{{ date("Y-m-d") }}');
                $('#kategori_id').val('all');
                $('#order_column').val('jumlah_terjual');
                table.ajax.reload();
                loadRingkasan();
                loadChart();
            });

            $('#btnExportPDF').on('click', function() {
                var params = {
                    tanggal_dari: $('#tanggal_dari').val(),
                    tanggal_sampai: $('#tanggal_sampai').val(),
                    kategori_id: $('#kategori_id').val()
                };
                var queryString = $.param(params);
                window.open('{{ route("laporan.penjualan-barang.export_pdf") }}?' + queryString, '_blank');
            });

            $('#btnExportExcel').on('click', function() {
                var params = {
                    tanggal_dari: $('#tanggal_dari').val(),
                    tanggal_sampai: $('#tanggal_sampai').val(),
                    kategori_id: $('#kategori_id').val()
                };
                var queryString = $.param(params);
                window.open('{{ route("laporan.penjualan-barang.export_excel") }}?' + queryString, '_blank');
            });

            function loadRingkasan() {
                var params = {
                    tanggal_dari: $('#tanggal_dari').val(),
                    tanggal_sampai: $('#tanggal_sampai').val(),
                    kategori_id: $('#kategori_id').val()
                };
                $.get('{{ route("laporan.penjualan-barang.ringkasan") }}', params)
                    .done(function(data) {
                        $('#totalProdukTerjual').text(data.total_produk_terjual);
                        $('#totalNilaiPenjualan').text(data.total_nilai_penjualan);
                        $('#totalLabaBersih').text(data.total_laba_bersih);
                    });
            }

            function loadChart() {
                var params = {
                    tanggal_dari: $('#tanggal_dari').val(),
                    tanggal_sampai: $('#tanggal_sampai').val(),
                    kategori_id: $('#kategori_id').val()
                };
                $.get('{{ route("laporan.penjualan-barang.chart") }}', params)
                    .done(function(data) {
                        var ctx = document.getElementById('penjualanChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: data.labels,
                                datasets: [{
                                    label: 'Jumlah Terjual',
                                    data: data.jumlah_terjual,
                                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1,
                                    yAxisID: 'y'
                                }, {
                                    label: 'Total Nilai Penjualan',
                                    data: data.total_nilai,
                                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                                    borderColor: 'rgba(255, 99, 132, 1)',
                                    borderWidth: 1,
                                    yAxisID: 'y1'
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        type: 'linear',
                                        display: true,
                                        position: 'left',
                                        title: {
                                            display: true,
                                            text: 'Jumlah Terjual'
                                        }
                                    },
                                    y1: {
                                        type: 'linear',
                                        display: true,
                                        position: 'right',
                                        title: {
                                            display: true,
                                            text: 'Total Nilai (Rp)'
                                        },
                                        grid: {
                                            drawOnChartArea: false,
                                        },
                                    }
                                }
                            }
                        });
                    });
            }
        });
    </script>
</body>
</html>
