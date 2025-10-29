<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Barang Hampir Habis - Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
        }
        .status-habis {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-hampir-habis {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-normal {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Barang Hampir Habis</h2>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
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
            @foreach($data as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->nama_kategori }}</td>
                <td>{{ $item->nama_satuan }}</td>
                <td>{{ $item->stok_sekarang }}</td>
                <td>{{ $item->batas_minimum }}</td>
                <td class="status-{{ strtolower(str_replace(' ', '-', $item->status)) }}">
                    {{ $item->status }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
