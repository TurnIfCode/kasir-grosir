<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian per Supplier</title>
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
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
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
        }
    </style>
</head>
<body>
    @include('layout.header')

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4 fw-bold text-success">Laporan Pembelian per Supplier</h1>

                <!-- Filter Section -->
                <div class="filter-section">
                    <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Data</h5>
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-4">
                            <label for="supplier_id" class="form-label">Pilih Supplier</label>
                            <input type="text" class="form-control" id="supplier_name" name="supplier_name" placeholder="Ketik nama supplier..." required>
                            <input type="hidden" id="supplier_id" name="supplier_id">
                        </div>
                        <div class="col-md-4">
                            <label for="tanggal_dari" class="form-label">Tanggal Dari</label>
                            <input type="date" class="form-control" id="tanggal_dari" name="tanggal_dari" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="tanggal_sampai" class="form-label">Tanggal Sampai</label>
                            <input type="date" class="form-control" id="tanggal_sampai" name="tanggal_sampai" value="{{ date('Y-m-d') }}">
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
                <div class="row mb-4" id="summaryCards" style="display: none;">
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="card summary-card">
                            <div class="card-body text-center">
                                <i class="fas fa-receipt fa-2x mb-2"></i>
                                <h6 class="card-title">Total Pembelian</h6>
                                <p class="card-value" id="total_pembelian">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="card summary-card">
                            <div class="card-body text-center">
                                <i class="fas fa-calculator fa-2x mb-2"></i>
                                <h6 class="card-title">Jumlah Transaksi</h6>
                                <p class="card-value" id="jumlah_transaksi">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="card summary-card">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-2x mb-2"></i>
                                <h6 class="card-title">Rata-rata Nilai</h6>
                                <p class="card-value" id="rata_rata_nilai">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-table me-2"></i>Tabel Detail Pembelian per Supplier</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="laporanTable" class="table table-striped table-hover">
                                <thead class="table-success">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nomor Transaksi</th>
                                        <th>Nama Barang</th>
                                        <th>Jumlah</th>
                                        <th>Satuan</th>
                                        <th>Harga</th>
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
        </div>
    </div>

    @include('layout.footer')

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            let table;

            // Initialize autocomplete for supplier
            $('#supplier_name').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '{{ route("laporan.pembelian-per-supplier.autocomplete-supplier") }}',
                        dataType: 'json',
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    label: item.nama_supplier,
                                    value: item.nama_supplier,
                                    id: item.id
                                };
                            }));
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    $('#supplier_id').val(ui.item.id);
                    $('#supplier_name').val(ui.item.value);
                    return false;
                }
            });

            // Initialize DataTable
            function initTable() {
                if (table) table.destroy();

                table = $('#laporanTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route("laporan.pembelian-per-supplier.data") }}',
                        data: function(d) {
                            d.supplier_id = $('#supplier_id').val();
                            d.tanggal_dari = $('#tanggal_dari').val();
                            d.tanggal_sampai = $('#tanggal_sampai').val();
                        }
                    },
                    columns: [
                        { data: 'tanggal_pembelian_formatted' },
                        { data: 'kode_pembelian' },
                        { data: 'nama_barang' },
                        { data: 'qty_formatted' },
                        { data: 'satuan' },
                        { data: 'harga_formatted' },
                        { data: 'total_formatted' }
                    ],
                    language: {
                        emptyTable: "Pilih supplier terlebih dahulu untuk melihat data pembelian."
                    },
                    responsive: true
                });
            }

            // Load summary data
            function loadRingkasan() {
                const supplierId = $('#supplier_id').val();
                if (!supplierId) {
                    $('#summaryCards').hide();
                    return;
                }

                $.ajax({
                    url: '{{ route("laporan.pembelian.ringkasan") }}',
                    data: {
                        supplier_id: supplierId,
                        tanggal_dari: $('#tanggal_dari').val(),
                        tanggal_sampai: $('#tanggal_sampai').val()
                    },
                    success: function(data) {
                        $('#total_pembelian').text(data.total_nilai);
                        $('#jumlah_transaksi').text(data.total_transaksi);

                        // Calculate rata-rata
                        const totalNilai = parseFloat(data.total_nilai.replace(/[^\d]/g, ''));
                        const jumlahTransaksi = parseInt(data.total_transaksi);
                        const rataRata = jumlahTransaksi > 0 ? totalNilai / jumlahTransaksi : 0;
                        $('#rata_rata_nilai').text('Rp ' + rataRata.toLocaleString('id-ID'));

                        $('#summaryCards').show();
                    }
                });
            }

            // Filter button
            $('#btnFilter').on('click', function() {
                if (!$('#supplier_id').val()) {
                    alert('Silakan pilih supplier terlebih dahulu.');
                    return;
                }
                initTable();
                loadRingkasan();
            });

            // Reset button
            $('#btnReset').on('click', function() {
                $('#supplier_id').val('');
                $('#supplier_name').val('');
                $('#tanggal_dari').val('{{ date('Y-m-d', strtotime('-30 days')) }}');
                $('#tanggal_sampai').val('{{ date('Y-m-d') }}');
                $('#summaryCards').hide();
                if (table) table.clear().draw();
            });

            // Export PDF
            $('#btnExportPDF').on('click', function() {
                if (!$('#supplier_id').val()) {
                    alert('Silakan pilih supplier terlebih dahulu.');
                    return;
                }
                const params = {
                    supplier_id: $('#supplier_id').val(),
                    tanggal_dari: $('#tanggal_dari').val(),
                    tanggal_sampai: $('#tanggal_sampai').val()
                };
                const queryString = $.param(params);
                window.open('{{ route("laporan.pembelian-per-supplier.export_pdf") }}?' + queryString, '_blank');
            });

            // Supplier change (now triggered by autocomplete select)
            $('#supplier_name').on('autocompletechange', function() {
                if ($('#supplier_id').val()) {
                    initTable();
                    loadRingkasan();
                } else {
                    $('#summaryCards').hide();
                    if (table) table.clear().draw();
                }
            });
        });
    </script>
</body>
</html>
