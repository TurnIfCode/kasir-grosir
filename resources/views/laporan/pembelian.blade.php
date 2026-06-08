@section('title', 'Laporan Pembelian Harian / Periodik - GrosirIndo')
    @include('layout.header')

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4 fw-bold text-primary">Laporan Pembelian Harian / Periodik</h1>

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
                            <label for="supplier_id" class="form-label">Supplier</label>
                            <select class="form-control" id="supplier_id" name="supplier_id">
                                <option value="all">Semua Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->nama_supplier }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="jenis_pembayaran" class="form-label">Jenis Pembayaran</label>
                            <select class="form-control" id="jenis_pembayaran" name="jenis_pembayaran" disabled>
                                <option value="tunai">Tunai</option>
                                <option value="transfer">Transfer</option>
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
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="card summary-card">
                            <div class="card-body text-center">
                                <i class="fas fa-receipt fa-2x mb-2"></i>
                                <h6 class="card-title">Total Transaksi</h6>
                                <p class="card-value" id="total_transaksi">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="card summary-card">
                            <div class="card-body text-center">
                                <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                                <h6 class="card-title">Total Nilai Pembelian</h6>
                                <p class="card-value" id="total_nilai">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="card summary-card">
                            <div class="card-body text-center">
                                <i class="fas fa-boxes fa-2x mb-2"></i>
                                <h6 class="card-title">Jumlah Barang Masuk</h6>
                                <p class="card-value" id="total_barang_masuk">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row mb-4">
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Grafik Pembelian per Hari</h6>
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
                                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Pembelian per Supplier</h6>
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
                        <h6 class="mb-0"><i class="fas fa-table me-2"></i>Tabel Detail Pembelian</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="laporanTable" class="table table-striped table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Nomor Transaksi</th>
                                        <th>Supplier</th>
                                        <th>Jenis Pembayaran</th>
                                        <th>Jumlah Item</th>
                                        <th>Total Nilai</th>
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

    <script src="{{ asset('js/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/chart.js') }}"></script>

    <script>
        $(document).ready(function() {
            let lineChart, barChart;

            // Initialize DataTable
            const table = $('#laporanTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("laporan.pembelian.data") }}',
                    data: function(d) {
                        d.tanggal_dari = $('#tanggal_dari').val();
                        d.tanggal_sampai = $('#tanggal_sampai').val();
                        d.supplier_id = $('#supplier_id').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'tanggal_pembelian_formatted' },
                    { data: 'kode_pembelian' },
                    { data: 'supplier_nama' },
                    { data: 'jenis_pembayaran' },
                    { data: 'jumlah_item', render: function(data) { return parseFloat(data).toFixed(2); } },
                    { data: 'total_formatted' }
                ],
                language: {
                    emptyTable: "Tidak ada data pembelian pada periode ini."
                },
                responsive: true
            });

            // Load summary data
            function loadRingkasan() {
                $.ajax({
                    url: '{{ route("laporan.pembelian.ringkasan") }}',
                    data: {
                        tanggal_dari: $('#tanggal_dari').val(),
                        tanggal_sampai: $('#tanggal_sampai').val(),
                        supplier_id: $('#supplier_id').val()
                    },
                    success: function(data) {
                        $('#total_transaksi').text(data.total_transaksi);
                        $('#total_nilai').text(data.total_nilai);
                        $('#total_barang_masuk').text(data.total_barang_masuk);
                    }
                });
            }

            // Load chart data
            function loadChartData() {
                $.ajax({
                    url: '{{ route("laporan.pembelian.chart") }}',
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
                                    label: 'Nilai Pembelian',
                                    data: data.line_chart.nilai,
                                    borderColor: '#007bff',
                                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
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
                                    label: 'Nilai Pembelian',
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
                                                return 'Nilai: Rp ' + context.parsed.y.toLocaleString('id-ID');
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
                $('#supplier_id').val('all');
                table.ajax.reload();
                loadRingkasan();
                loadChartData();
            });

            // Export PDF
            $('#btnExportPDF').on('click', function() {
                const params = {
                    tanggal_dari: $('#tanggal_dari').val(),
                    tanggal_sampai: $('#tanggal_sampai').val(),
                    supplier_id: $('#supplier_id').val()
                };
                const queryString = $.param(params);
                window.open('{{ route("laporan.pembelian.export_pdf") }}?' + queryString, '_blank');
            });

            // Initial load
            loadRingkasan();
            loadChartData();
        });
    </script>
</body>
</html>
