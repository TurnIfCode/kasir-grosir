<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Minimum Stok Barrang per Supplier</title>
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
        .table-danger td {
            background-color: #f8d7da !important;
        }

        .table-success td {
            background-color: #d1e7dd !important;
        }
    </style>
</head>
<body>
@include('layout.header')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4 fw-bold text-success">Laporan Minimum Stok Barang per Supplier</h1>

            <!-- Filter Section -->
            <div class="filter-section">
                <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Data</h5>
                <form id="filterForm" class="row g-3">
                    <div class="col-md-4">
                        <label for="supplier_id" class="form-label">Pilih Supplier</label>
                        <input type="text" class="form-control" id="supplier_name" name="supplier_name" placeholder="Ketik nama supplier..." required>
                        <input type="hidden" id="supplier_id" name="supplier_id">
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

            <!-- Data Table -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-table me-2"></i>Tabel Detail Pembelian per Supplier</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="laporanTable" class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Stok Saat Ini</th>
                                    <th>Minimum Stok</th>
                                    <th>Harus Order</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layout.footer')
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

        // Filter button
        $('#btnFilter').on('click', function() {
            if (!$('#supplier_id').val()) {
                alert('Silakan pilih supplier terlebih dahulu.');
                return;
            }
            initTable();
        });

        function initTable() {

            if (table) {
                table.destroy();
                $('#laporanTable tbody').empty();
            }

            table = $('#laporanTable').DataTable({
                processing: true,
                destroy: true,
                searching: false,
                paging: false,
                info: false,
                ordering: false,

                ajax: {
                    url: '{{ route("laporan.stok-minimum-barang-jenis-supplier.data") }}',
                    type: 'GET',
                    data: function(d) {
                        d.supplier_id = $('#supplier_id').val();
                    },
                    dataSrc: function(json) {

                        $('#total_item').text(
                            json.total_item ?? 0
                        );

                        return json.data;
                    }
                },

                createdRow: function(row, data) {

                    if (data.row_class) {
                        $(row).addClass(data.row_class);
                    }

                },

                columns: [
                    {
                        data: 'kode_barang',
                        title: 'Kode Barang'
                    },
                    {
                        data: 'nama_barang',
                        title: 'Nama Barang'
                    },
                    {
                        data: 'stok_text',
                        title: 'Stok Saat Ini'
                    },
                    {
                        data: 'minimum_text',
                        title: 'Minimum Stok'
                    },
                    {
                        data: 'kekurangan_text',
                        title: 'Harus Order'
                    },
                    {
                        data: 'badge',
                        title: 'Status'
                    }
                ],

                columnDefs: [
                    {
                        targets: [5],
                        orderable: false,
                        searchable: false
                    }
                ],

                language: {
                    emptyTable: "Tidak ada data barang."
                },

                responsive: true
            });

        }

        // Export PDF
        $('#btnExportPDF').on('click', function() {
            if (!$('#supplier_id').val()) {
                alert('Silakan pilih supplier terlebih dahulu.');
                return;
            }
            const params = {
                supplier_id: $('#supplier_id').val()
            };
            const queryString = $.param(params);
            window.open('{{ route("laporan.stok-minimum-barang-jenis-supplier.export_pdf") }}?' + queryString, '_blank');
        });
    });
</script>
</body>
</html>