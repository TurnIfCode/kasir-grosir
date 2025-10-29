<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Koreksi Stok - PDF</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .header p {
            margin: 5px 0;
            font-size: 12px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
        }

        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Koreksi Stok</h1>
        <p>Periode Opname: {{ $tanggalAwal ?? 'Semua Periode' }} - {{ $tanggalAkhir ?? 'Semua Periode' }}</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    @if($data->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="10%">Tanggal Opname</th>
                    <th width="12%">Kode Barang</th>
                    <th width="20%">Nama Barang</th>
                    <th width="12%">Kategori</th>
                    <th width="8%">Satuan</th>
                    <th width="8%">Stok Sistem</th>
                    <th width="8%">Stok Real</th>
                    <th width="8%">Selisih</th>
                    <th width="9%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_opname)->format('d/m/Y') }}</td>
                        <td>{{ $item->kode_barang }}</td>
                        <td>{{ $item->nama_barang }}</td>
                        <td>{{ $item->nama_kategori }}</td>
                        <td>{{ $item->nama_satuan }}</td>
                        <td class="text-right">{{ number_format($item->stok_sistem, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item->stok_real, 0, ',', '.') }}</td>
                        <td class="text-right {{ $item->selisih > 0 ? 'text-success' : ($item->selisih < 0 ? 'text-danger' : '') }}">
                            {{ $item->selisih > 0 ? '+' : '' }}{{ number_format($item->selisih, 0, ',', '.') }}
                        </td>
                        <td>{{ $item->keterangan ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            Tidak ada data koreksi stok untuk periode yang dipilih.
        </div>
    @endif

    <div class="footer">
        <p>Dicetak oleh: {{ auth()->user()->name ?? 'System' }}</p>
    </div>
</body>
</html>
