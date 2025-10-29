<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan Harian / Periodik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .summary-card {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }
        .summary-card .card-body {
            padding: 1.5rem;
        }
        .summary-card .card-title {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }
        .summary-card .card-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
        }
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        @media (max-width: 768px) {
            .summary-card .card-value {
                font-size: 1.4rem;
            }
            .chart-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    @include('layout.header')

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4 fw-bold text-primary">Laporan Penjualan Harian / Periodik</h1>

                <!-- Filter Section -->
                <div class="filter-section">
                    <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Data</h5>
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="tanggal_dari" class="form-label">Tanggal Dari</label>
                            <input type="date" class="form-control" id="tanggal_dari" name="tanggal_dari" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                        </div>
                        <div class="col-md-3">
                            <label for="tanggal_sampai" class="form-label">Tanggal Sampai</label>
                            <input type="date" class="form-control" id="tanggal_sampai" name="tanggal_sampai" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="kasir_id" class="form-label">Pilihan Kasir</label>
                            <select class="form-control" id="kasir_id" name="kasir_id">
                                <option value="all">Semua Kasir</option>
                                @foreach($kasirs as $kasir)
                                    <option value="{{ $kasir->id }}">{{ $kasir->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="kategori_id" class="form-label">Pilihan Kategori</label>
                            <select class="form-control" id="kategori_id" name="kategori_id">
                                <option value="all">Semua Kategori</option>
                                @foreach($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-primary me-2" id="btnFilter">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <button type="button" class="btn btn-secondary me-2" id="btnReset">
                                <i class="fas fa-undo me-2"></i>Reset
                            </button>
                            <button type="button" class="btn btn-success" id="btnExportPDF">
                                <i class="fas fa-file-pdf me-2"></i>Export PDF
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4" id="summaryCards">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card summary-card">
                            <div class="card-body text-center">
                                <i class="fas fa-receipt fa-2x mb-2"></i>
                                <h6 class="card-title">Total Transaksi</h6>
                                <p class="card-value" id="total_transaksi">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card summary-card">
                            <div class="card-body text-center">
                                <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                                <h6 class="card-title">Total Omzet</h6>
                                <p class="card-value" id="total_omzet">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card summary-card">
                            <div class="card-body text-center">
                                <i class="fas fa-boxes fa-2x mb-2"></i>
                                <h6 class="card-title">Total Barang Terjual</h6>
                                <p class="card-value" id="total_barang_terjual">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card summary-card">
                            <div class="card-body text-center">
                                <i class="fas fa-percent fa-2x mb-2"></i>
                                <h6 class="card-title">Total Diskon</h6>
                                <p class="card-value" id="total_diskon">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row mb-4">
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Grafik Penjualan per Hari</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="lineChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Penjualan per Kategori</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="barChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-table me-2"></i>Tabel Detail Penjualan</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="laporanTable" class="table table-striped table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Nama Kasir</th>
                                        <th>Kategori</th>
                                        <th>Jumlah Transaksi</th>
                                        <th>Total Barang</th>
                                        <th>Total Omzet</th>
                                        <th>Diskon</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            let lineChart, barChart;

            // Initialize DataTable
            const table = $('#laporanTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("laporan.penjualan-harian.data") }}',
                    data: function(d) {
                        d.tanggal_dari = $('#tanggal_dari').val();
                        d.tanggal_sampai = $('#tanggal_sampai').val();
                        d.kasir_id = $('#kasir_id').val();
                        d.kategori_id = $('#kategori_id').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'tanggal_penjualan_formatted' },
                    { data: 'nama_kasir' },
                    { data: 'kategori' },
                    { data: 'jumlah_transaksi' },
                    { data: 'total_barang_formatted' },
                    { data: 'total_omzet_formatted' },
                    { data: 'diskon_formatted' }
                ],
                language: {
                    emptyTable: "Tidak ada data penjualan pada periode ini."
                },
                responsive: true
            });

            // Load summary data
            function loadRingkasan() {
                $.ajax({
                    url: '{{ route("laporan.penjualan-harian.ringkasan") }}',
                    data: {
                        tanggal_dari: $('#tanggal_dari').val(),
                        tanggal_sampai: $('#tanggal_sampai').val(),
                        kasir_id: $('#kasir_id').val(),
                        kategori_id: $('#kategori_id').val()
                    },
                    success: function(data) {
                        $('#total_transaksi').text(data.total_transaksi);
                        $('#total_omzet').text(data.total_omzet);
                        $('#total_barang_terjual').text(data.total_barang_terjual);
                        $('#total_diskon').text(data.total_diskon);
                    }
                });
            }

            // Load chart data
            function loadChartData() {
                $.ajax({
                    url: '{{ route("laporan.penjualan-harian.chart") }}',
                    data: {
                        tanggal_dari: $('#tanggal_dari').val(),
                        tanggal_sampai: $('#tanggal_sampai').val()
                    },
                    success: function(data) {
                        // Line Chart
                        if (lineChart) lineChart.destroy();
                        const ctxLine = document.getElementById('lineChart').getContext('2d');
                        lineChart = new Chart(ctxLine, {
                            type: 'line',
                            data: {
                                labels: data.line_chart.labels,
                                datasets: [{
                                    label: 'Omzet',
                                    data: data.line_chart.omzet,
                                    borderColor: '#007bff',
                                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }, {
                                    label: 'Diskon',
                                    data: data.line_chart.diskon,
                                    borderColor: '#28a745',
                                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return 'Rp ' + value.toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                }
                            }
                        });

                        // Bar Chart
                        if (barChart) barChart.destroy();
                        const ctxBar = document.getElementById('barChart').getContext('2d');
                        barChart = new Chart(ctxBar, {
                            type: 'bar',
                            data: {
                                labels: data.bar_chart.labels,
                                datasets: [{
                                    label: 'Omzet',
                                    data: data.bar_chart.data,
                                    backgroundColor: '#007bff',
                                    borderColor: '#0056b3',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return 'Rp ' + value.toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return 'Omzet: Rp ' + context.parsed.y.toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
            }

            // Filter button
            $('#btnFilter').on('click', function() {
                table.ajax.reload();
                loadRingkasan();
                loadChartData();
            });

            // Reset button
            $('#btnReset').on('click', function() {
                $('#tanggal_dari').val('{{ date('Y-m-d', strtotime('-30 days')) }}');
                $('#tanggal_sampai').val('{{ date('Y-m-d') }}');
                $('#kasir_id').val('all');
                $('#kategori_id').val('all');
                table.ajax.reload();
                loadRingkasan();
                loadChartData();
            });

            // Export PDF
            $('#btnExportPDF').on('click', function() {
                const params = {
                    tanggal_dari: $('#tanggal_dari').val(),
                    tanggal_sampai: $('#tanggal_sampai').val(),
                    kasir_id: $('#kasir_id').val(),
                    kategori_id: $('#kategori_id').val()
                };
                const queryString = $.param(params);
                window.open('{{ route("laporan.penjualan-harian.export_pdf") }}?' + queryString, '_blank');
            });

            // Initial load
            loadRingkasan();
            loadChartData();
        });
    </script>
</body>
</html>
