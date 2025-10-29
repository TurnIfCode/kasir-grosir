<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan Harian / Periodik - PDF</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            color: #007bff;
        }
        .periode {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .summary {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .summary td {
            width: 25%;
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }
        .summary .label {
            font-weight: bold;
            color: #007bff;
            font-size: 10px;
        }
        .summary .value {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
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
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
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
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Penjualan Harian / Periodik</h2>
        <p>Sistem Kasir Grosir</p>
    </div>

    <div class="periode">
        Periode: {{ $tanggalDari }} - {{ $tanggalSampai }}
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="label">Total Transaksi</div>
                <div class="value">{{ $ringkasan['total_transaksi'] ?? 0 }}</div>
            </td>
            <td>
                <div class="label">Total Omzet</div>
                <div class="value">{{ $ringkasan['total_omzet'] ?? 'Rp 0' }}</div>
            </td>
            <td>
                <div class="label">Total Barang Terjual</div>
                <div class="value">{{ $ringkasan['total_barang_terjual'] ?? '0' }}</div>
            </td>
            <td>
                <div class="label">Total Diskon</div>
                <div class="value">{{ $ringkasan['total_diskon'] ?? 'Rp 0' }}</div>
            </td>
        </tr>
    </table>

    @if($data->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Kasir</th>
                    <th>Kategori</th>
                    <th>Jumlah Transaksi</th>
                    <th>Total Barang</th>
                    <th>Total Omzet</th>
                    <th>Diskon</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->tanggal_penjualan->format('d/m/Y') }}</td>
                        <td>{{ $item->creator->name ?? '-' }}</td>
                        <td>{{ $item->details->pluck('barang.kategori.nama_kategori')->unique()->implode(', ') ?: '-' }}</td>
                        <td>1</td>
                        <td>{{ number_format($item->details->sum('qty'), 2, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->diskon, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            Tidak ada data penjualan pada periode ini.
        </div>
    @endif

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
