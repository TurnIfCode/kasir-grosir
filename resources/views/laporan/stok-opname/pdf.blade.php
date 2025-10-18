<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stok Opname - {{ $stokOpname->kode_opname }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .info-table .label {
            font-weight: bold;
            width: 150px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-success {
            color: #28a745;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
        }
        .bg-success {
            background-color: #d4edda;
            color: #155724;
        }
        .bg-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .bg-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN STOK OPNAME</h1>
        <p>Sistem Kasir Grosir</p>
    </div>

    <!-- Header Info -->
    <table class="info-table">
        <tr>
            <td class="label">Kode Opname</td>
            <td>: {{ $stokOpname->kode_opname }}</td>
            <td class="label">Status</td>
            <td>: <span class="badge {{ $stokOpname->status == 'selesai' ? 'bg-success' : ($stokOpname->status == 'batal' ? 'bg-danger' : 'bg-secondary') }}">{{ ucfirst($stokOpname->status) }}</span></td>
        </tr>
        <tr>
            <td class="label">Tanggal Opname</td>
            <td>: {{ \Carbon\Carbon::parse($stokOpname->tanggal_opname)->format('d/m/Y H:i') }}</td>
            <td class="label">Petugas</td>
            <td>: {{ $stokOpname->user ? $stokOpname->user->name : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Catatan</td>
            <td colspan="3">: {{ $stokOpname->catatan ?: '-' }}</td>
        </tr>
    </table>

    <!-- Detail Barang -->
    <h4>Detail Barang</h4>
    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="10%">Kode Barang</th>
                <th width="20%">Nama Barang</th>
                <th width="10%">Kategori</th>
                <th width="8%">Satuan</th>
                <th class="text-center" width="10%">Stok Sistem</th>
                <th class="text-center" width="10%">Stok Fisik</th>
                <th class="text-center" width="10%">Selisih</th>
                <th width="17%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stokOpname->details as $index => $detail)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $detail->barang->kode_barang }}</td>
                <td>{{ $detail->barang->nama_barang }}</td>
                <td>{{ $detail->barang->kategori->nama_kategori }}</td>
                <td>{{ $detail->barang->satuan->nama_satuan }}</td>
                <td class="text-center">{{ number_format($detail->stok_sistem, 2) }}</td>
                <td class="text-center">{{ number_format($detail->stok_fisik, 2) }}</td>
                <td class="text-center {{ $detail->selisih < 0 ? 'text-danger' : ($detail->selisih > 0 ? 'text-success' : '') }}">
                    {{ number_format($detail->selisih, 2) }}
                </td>
                <td>{{ $detail->keterangan ?: '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 50px;">
        <div style="width: 200px; float: right; text-align: center;">
            <p>Dibuat oleh,</p>
            <br><br><br>
            <p><strong>{{ $stokOpname->user ? $stokOpname->user->name : '-' }}</strong></p>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
