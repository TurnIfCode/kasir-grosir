<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Stok Opname - {{ $stokOpname->kode_opname }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    @include('layout.header')

    <div class="container mt-4">
        <h1 class="mb-4">Detail Stok Opname</h1>

        <!-- Header Info -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="150"><strong>Kode Opname</strong></td>
                                <td>: {{ $stokOpname->kode_opname }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Opname</strong></td>
                                <td>: {{ \Carbon\Carbon::parse($stokOpname->tanggal_opname)->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Petugas</strong></td>
                                <td>: {{ $stokOpname->user ? $stokOpname->user->name : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="150"><strong>Status</strong></td>
                                <td>: <span class="badge bg-{{ $stokOpname->status == 'selesai' ? 'success' : ($stokOpname->status == 'batal' ? 'danger' : 'secondary') }}">{{ ucfirst($stokOpname->status) }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Catatan</strong></td>
                                <td>: {{ $stokOpname->catatan ?: '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Barang -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Detail Barang</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th>Stok Sistem</th>
                                <th>Stok Fisik</th>
                                <th>Selisih</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stokOpname->details as $index => $detail)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $detail->barang->kode_barang }}</td>
                                <td>{{ $detail->barang->nama_barang }}</td>
                                <td>{{ $detail->barang->kategori->nama_kategori }}</td>
                                <td>{{ $detail->barang->satuan->nama_satuan }}</td>
                                <td class="text-end">{{ number_format($detail->stok_sistem, 2) }}</td>
                                <td class="text-end">{{ number_format($detail->stok_fisik, 2) }}</td>
                                <td class="text-end {{ $detail->selisih < 0 ? 'text-danger' : ($detail->selisih > 0 ? 'text-success' : '') }}">
                                    {{ number_format($detail->selisih, 2) }}
                                </td>
                                <td>{{ $detail->keterangan ?: '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4">
            <a href="{{ route('laporan.stok-opname.export-pdf', $stokOpname->id) }}" target="_blank" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            <a href="{{ route('laporan.stok-opname') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Laporan
            </a>
        </div>
    </div>

    @include('layout.footer')

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
