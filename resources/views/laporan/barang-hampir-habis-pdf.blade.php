<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Barang Hampir Habis - PDF</title>
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

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
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
        <h1>Laporan Barang Hampir Habis</h1>
        <p>Batas Minimum Stok: {{ $batasMinimum ?? 5 }}</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    @if($data->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="12%">Kode Barang</th>
                    <th width="25%">Nama Barang</th>
                    <th width="15%">Kategori</th>
                    <th width="10%">Satuan</th>
                    <th width="10%">Stok</th>
                    <th width="10%">Batas Min</th>
                    <th width="13%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->kode_barang }}</td>
                        <td>{{ $item->nama_barang }}</td>
                        <td>{{ $item->nama_kategori }}</td>
                        <td>{{ $item->nama_satuan }}</td>
                        <td class="text-right">{{ number_format($item->stok_sekarang, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item->batas_minimum, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($item->status == 'Habis')
                                <span class="badge badge-habis">{{ $item->status }}</span>
                            @elseif($item->status == 'Hampir Habis')
                                <span class="badge badge-hampir-habis">{{ $item->status }}</span>
                            @else
                                <span class="badge badge-normal">{{ $item->status }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            Tidak ada barang yang hampir habis atau habis stok.
        </div>
    @endif

    <div class="footer">
        <p>Dicetak oleh: {{ auth()->user()->name ?? 'System' }}</p>
    </div>
</body>
</html>
