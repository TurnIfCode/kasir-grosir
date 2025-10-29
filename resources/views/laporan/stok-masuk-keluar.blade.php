<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stok Masuk & Keluar - GrosirIndo</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">

    <!-- jQuery UI CSS -->
    <link href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }

        .btn-filter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            color: white;
            font-weight: 500;
        }

        .btn-filter:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
        }

        .btn-export {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            color: white;
            font-weight: 500;
            margin-left: 0.5rem;
        }

        .btn-export:hover {
            background: linear-gradient(135deg, #218838 0%, #1aa085 100%);
            color: white;
        }

        .page-title {
            color: #333;
            font-weight: 700;
            margin-bottom: 2rem;
        }

        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 2rem;
        }

        .breadcrumb-item a {
            color: #667eea;
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: #6c757d;
        }

        .nav-tabs .nav-link {
            border: none;
            border-radius: 10px 10px 0 0;
            color: #6c757d;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
        }

        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .tab-content {
            background: white;
            border-radius: 0 15px 15px 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .nav-tabs .nav-link {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    @include('layout.header')

    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('laporan.stok') }}">Laporan Stok</a></li>
                <li class="breadcrumb-item active" aria-current="page">Stok Masuk & Keluar</li>
            </ol>
        </nav>

        <h1 class="page-title">
            <i class="fas fa-exchange-alt me-3"></i>
            🔄 Laporan Stok Masuk & Keluar
        </h1>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row align-items-end">
                <div class="col-lg-3 col-md-6 mb-3">
                    <label for="tanggal_awal" class="form-label fw-semibold">Tanggal Awal</label>
                    <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <label for="tanggal_akhir" class="form-label fw-semibold">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <label for="barang_autocomplete" class="form-label fw-semibold">Pilih Barang</label>
                    <input type="text" class="form-control" id="barang_autocomplete" name="barang_nama" placeholder="Ketik nama atau kode barang">
                    <input type="hidden" id="barang_id" name="barang_id">
                </div>
                <div class="col-lg-3 col-md-12 mb-3">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-filter" id="btn-filter">
                            <i class="fas fa-search me-2"></i>🔍 Filter
                        </button>
                        <button type="button" class="btn btn-export" onclick="exportPDF()">
                            <i class="fas fa-file-pdf me-2"></i>Export PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="stokTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="masuk-tab" data-bs-toggle="tab" data-bs-target="#masuk" type="button" role="tab" aria-controls="masuk" aria-selected="true">
                    📥 Stok Masuk
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="keluar-tab" data-bs-toggle="tab" data-bs-target="#keluar" type="button" role="tab" aria-controls="keluar" aria-selected="false">
                    📤 Stok Keluar
                </button>
            </li>
        </ul>

        <div class="tab-content" id="stokTabsContent">
            <!-- Stok Masuk Tab -->
            <div class="tab-pane fade show active" id="masuk" role="tabpanel" aria-labelledby="masuk-tab">
                <div class="table-container">
                    <div class="table-responsive">
                        <table id="stok-masuk-table" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Jenis Transaksi</th>
                                    <th>Nomor Transaksi</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah Masuk</th>
                                    <th>Satuan</th>
                                    <th>Harga Beli</th>
                                    <th>Supplier</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Stok Keluar Tab -->
            <div class="tab-pane fade" id="keluar" role="tabpanel" aria-labelledby="keluar-tab">
                <div class="table-container">
                    <div class="table-responsive">
                        <table id="stok-keluar-table" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Jenis Transaksi</th>
                                    <th>Nomor Transaksi</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah Keluar</th>
                                    <th>Satuan</th>
                                    <th>Harga Jual</th>
                                    <th>Pelanggan</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="chart-container">
            <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i>Grafik Pergerakan Stok Harian</h5>
            <canvas id="stokChart" width="400" height="100"></canvas>
        </div>
    </div>

    @include('layout.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- jQuery UI JS -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTables
            const stokMasukTable = $('#stok-masuk-table').DataTable({
                ajax: {
                    url: '{{ route("laporan.stok-masuk-keluar.data") }}',
                    data: function(d) {
                        d.tanggal_awal = $('#tanggal_awal').val();
                        d.tanggal_akhir = $('#tanggal_akhir').val();
                        d.barang_id = $('#barang_id').val();
                        d.tipe = 'masuk';
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'tanggal' },
                    { data: 'jenis_transaksi' },
                    { data: 'nomor_transaksi' },
                    { data: 'nama_barang' },
                    { data: 'jumlah' },
                    { data: 'nama_satuan' },
                    { data: 'harga_beli' },
                    { data: 'supplier' },
                    { data: 'subtotal' }
                ],
                responsive: true,
                pageLength: 25,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                }
            });

            const stokKeluarTable = $('#stok-keluar-table').DataTable({
                ajax: {
                    url: '{{ route("laporan.stok-masuk-keluar.data") }}',
                    data: function(d) {
                        d.tanggal_awal = $('#tanggal_awal').val();
                        d.tanggal_akhir = $('#tanggal_akhir').val();
                        d.barang_id = $('#barang_id').val();
                        d.tipe = 'keluar';
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'tanggal' },
                    { data: 'jenis_transaksi' },
                    { data: 'nomor_transaksi' },
                    { data: 'nama_barang' },
                    { data: 'jumlah' },
                    { data: 'nama_satuan' },
                    { data: 'harga_jual' },
                    { data: 'pelanggan' },
                    { data: 'subtotal' }
                ],
                responsive: true,
                pageLength: 25,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                }
            });

            // Filter button click
            $('#btn-filter').on('click', function() {
                stokMasukTable.ajax.reload();
                stokKeluarTable.ajax.reload();
                loadChart();
            });

            // Autocomplete barang
            $('#barang_autocomplete').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '{{ route("laporan.stok.search-barang") }}',
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
                minLength: 2,
                select: function(event, ui) {
                    $('#barang_id').val(ui.item.id);
                    $('#barang_autocomplete').val(ui.item.value);
                    return false;
                }
            });

            // Clear barang_id when autocomplete is cleared
            $('#barang_autocomplete').on('input', function() {
                if ($(this).val() === '') {
                    $('#barang_id').val('');
                }
            });

            // Auto reload on filter change
            $('#tanggal_awal, #tanggal_akhir').on('change', function() {
                stokMasukTable.ajax.reload();
                stokKeluarTable.ajax.reload();
                loadChart();
            });

            // Auto reload when autocomplete changes
            $('#barang_autocomplete').on('autocompletechange', function() {
                stokMasukTable.ajax.reload();
                stokKeluarTable.ajax.reload();
                loadChart();
            });

            // Initialize chart
            loadChart();
        });

        function loadChart() {
            // This would typically fetch data from an API endpoint
            // For now, we'll create a placeholder chart
            const ctx = document.getElementById('stokChart').getContext('2d');

            // Destroy existing chart if it exists
            if (window.stokChartInstance) {
                window.stokChartInstance.destroy();
            }

            window.stokChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'],
                    datasets: [{
                        label: 'Stok Masuk',
                        data: [12, 19, 3, 5, 2, 3, 9],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }, {
                        label: 'Stok Keluar',
                        data: [2, 3, 20, 5, 1, 4, 7],
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Pergerakan Stok Harian'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function exportPDF() {
            const tanggalAwal = $('#tanggal_awal').val();
            const tanggalAkhir = $('#tanggal_akhir').val();
            const barangId = $('#barang_id').val();

            // Get active tab
            const activeTab = $('.nav-tabs .nav-link.active').attr('id');
            let tipe = 'masuk';
            if (activeTab === 'keluar-tab') {
                tipe = 'keluar';
            }

            window.open('{{ route("laporan.stok-masuk-keluar.export_pdf") }}?tanggal_awal=' + tanggalAwal + '&tanggal_akhir=' + tanggalAkhir + '&barang_id=' + barangId + '&tipe=' + tipe, '_blank');
        }
    </script>
</body>
</html>
