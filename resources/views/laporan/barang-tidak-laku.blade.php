<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Barang Tidak Laku - GrosirIndo</title>

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

        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .summary-card .card-body {
            padding: 1.5rem;
        }

        .summary-icon {
            font-size: 2rem;
            opacity: 0.8;
        }

        .summary-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }

        .summary-label {
            font-size: 0.9rem;
            opacity: 0.9;
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

        @media (max-width: 768px) {
            .summary-card .card-body {
                padding: 1rem;
            }

            .summary-value {
                font-size: 1.5rem;
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
                <li class="breadcrumb-item active" aria-current="page">Barang Tidak Laku</li>
            </ol>
        </nav>

        <h1 class="page-title">
            <i class="fas fa-sleep me-3"></i>
            💤 Laporan Barang Tidak Laku
        </h1>

        <!-- Summary Cards -->
        <div class="row mb-4" id="summary-cards">
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card summary-card">
                    <div class="card-body text-center">
                        <div class="summary-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="summary-value" id="jumlah-barang">0</div>
                        <div class="summary-label">📦 Jumlah Barang Tidak Laku</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card summary-card">
                    <div class="card-body text-center">
                        <div class="summary-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="summary-value" id="total-nilai-stok">Rp 0</div>
                        <div class="summary-label">💰 Total Nilai Stok</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row align-items-end">
                <div class="col-lg-3 col-md-6 mb-3">
                    <label for="tanggal_awal" class="form-label fw-semibold">Tanggal Awal</label>
                    <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal">
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <label for="tanggal_akhir" class="form-label fw-semibold">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir">
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <label for="kategori_id" class="form-label fw-semibold">Kategori Barang</label>
                    <select class="form-select" id="kategori_id" name="kategori_id">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-12 mb-3">
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
                <table id="barang-tidak-laku-table" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Terakhir Terjual</th>
                            <th>Stok Sekarang</th>
                            <th>Harga Beli</th>
                            <th>Nilai Stok</th>
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
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#barang-tidak-laku-table').DataTable({
                ajax: {
                    url: '{{ route("laporan.barang-tidak-laku.data") }}',
                    data: function(d) {
                        d.tanggal_awal = $('#tanggal_awal').val();
                        d.tanggal_akhir = $('#tanggal_akhir').val();
                        d.kategori_id = $('#kategori_id').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'kode_barang' },
                    { data: 'nama_barang' },
                    { data: 'nama_kategori' },
                    { data: 'nama_satuan' },
                    { data: 'terakhir_terjual' },
                    { data: 'stok_sekarang' },
                    { data: 'harga_beli' },
                    { data: 'nilai_stok' }
                ],
                responsive: true,
                pageLength: 25,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                initComplete: function() {
                    loadSummary();
                }
            });

            // Filter button click
            $('#btn-filter').on('click', function() {
                table.ajax.reload();
                loadSummary();
            });

            // Auto reload on filter change
            $('#tanggal_awal, #tanggal_akhir, #kategori_id').on('change', function() {
                table.ajax.reload();
                loadSummary();
            });
        });

        function loadSummary() {
            const tanggalAwal = $('#tanggal_awal').val();
            const tanggalAkhir = $('#tanggal_akhir').val();
            const kategoriId = $('#kategori_id').val();

            $.get('{{ route("laporan.barang-tidak-laku.ringkasan") }}', {
                tanggal_awal: tanggalAwal,
                tanggal_akhir: tanggalAkhir,
                kategori_id: kategoriId
            })
            .done(function(data) {
                $('#jumlah-barang').text(data.jumlah_barang);
                $('#total-nilai-stok').text(data.total_nilai_stok);
            })
            .fail(function() {
                console.error('Failed to load summary');
            });
        }

        function exportPDF() {
            const tanggalAwal = $('#tanggal_awal').val();
            const tanggalAkhir = $('#tanggal_akhir').val();
            const kategoriId = $('#kategori_id').val();

            window.open('{{ route("laporan.barang-tidak-laku.export_pdf") }}?tanggal_awal=' + tanggalAwal + '&tanggal_akhir=' + tanggalAkhir + '&kategori_id=' + kategoriId, '_blank');
        }

        function exportExcel() {
            const tanggalAwal = $('#tanggal_awal').val();
            const tanggalAkhir = $('#tanggal_akhir').val();
            const kategoriId = $('#kategori_id').val();

            window.open('{{ route("laporan.barang-tidak-laku.export_excel") }}?tanggal_awal=' + tanggalAwal + '&tanggal_akhir=' + tanggalAkhir + '&kategori_id=' + kategoriId, '_blank');
        }
    </script>
</body>
</html>
