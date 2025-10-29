<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Barang Hampir Habis - GrosirIndo</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">

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
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }

        .badge-habis {
            background-color: #dc3545;
            color: white;
        }

        .badge-hampir-habis {
            background-color: #ffc107;
            color: black;
        }

        .badge-normal {
            background-color: #28a745;
            color: white;
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

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        @media (max-width: 768px) {
            .filter-section .row > div {
                margin-bottom: 1rem;
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
                <li class="breadcrumb-item active" aria-current="page">Barang Hampir Habis</li>
            </ol>
        </nav>

        <h1 class="page-title">
            <i class="fas fa-exclamation-triangle me-3"></i>
            ⚠️ Laporan Barang Hampir Habis
        </h1>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row align-items-end">
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="kategori_id" class="form-label fw-semibold">Kategori Barang</label>
                    <select class="form-select" id="kategori_id" name="kategori_id">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="batas_minimum" class="form-label fw-semibold">Batas Stok Minimum</label>
                    <input type="number" class="form-control" id="batas_minimum" name="batas_minimum" value="5" min="0">
                </div>
                <div class="col-lg-4 col-md-12 mb-3">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-filter" id="btn-filter">
                            <i class="fas fa-search me-2"></i>🔍 Filter
                        </button>
                        <button type="button" class="btn btn-export" onclick="exportPDF()">
                            <i class="fas fa-file-pdf me-2"></i>Export PDF
                        </button>
                        <button type="button" class="btn btn-export" onclick="exportExcel()">
                            <i class="fas fa-file-excel me-2"></i>Export Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-responsive">
                <table id="barang-hampir-habis-table" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Stok Sekarang</th>
                            <th>Batas Minimum</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('layout.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#barang-hampir-habis-table').DataTable({
                ajax: {
                    url: '{{ route("laporan.barang-hampir-habis.data") }}',
                    data: function(d) {
                        d.kategori_id = $('#kategori_id').val();
                        d.batas_minimum = $('#batas_minimum').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'kode_barang' },
                    { data: 'nama_barang' },
                    { data: 'nama_kategori' },
                    { data: 'nama_satuan' },
                    { data: 'stok_sekarang' },
                    { data: 'batas_minimum' },
                    { data: 'badge_status', orderable: false, searchable: false }
                ],
                responsive: true,
                pageLength: 25,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                }
            });

            // Filter button click
            $('#btn-filter').on('click', function() {
                table.ajax.reload();
            });

            // Auto reload on filter change
            $('#kategori_id, #batas_minimum').on('change', function() {
                table.ajax.reload();
            });
        });

        function exportPDF() {
            const kategoriId = $('#kategori_id').val();
            const batasMinimum = $('#batas_minimum').val();

            window.open('{{ route("laporan.barang-hampir-habis.export_pdf") }}?kategori_id=' + kategoriId + '&batas_minimum=' + batasMinimum, '_blank');
        }

        function exportExcel() {
            const kategoriId = $('#kategori_id').val();
            const batasMinimum = $('#batas_minimum').val();

            window.open('{{ route("laporan.barang-hampir-habis.export_excel") }}?kategori_id=' + kategoriId + '&batas_minimum=' + batasMinimum, '_blank');
        }
    </script>
</body>
</html>
