<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Laba Rugi PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
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
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Laba Rugi</h1>
        <p>Periode: {{ $tanggalDariFormatted }} - {{ $tanggalSampaiFormatted }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Total Penjualan</th>
                <th>Total Pembelian</th>
                <th>Laba Kotor</th>
                <th>Laba Bersih</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                    <td>Rp {{ number_format($item->total_penjualan, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->total_pembelian, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->laba_kotor, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->laba_bersih, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada transaksi pada periode ini.</td>
                </tr>
            @endforelse
            @if($data->count() > 0)
                <tr class="total-row">
                    <td colspan="2" style="text-align: right;"><strong>Total Keseluruhan:</strong></td>
                    <td><strong>Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</strong></td>
                    <td><strong>Rp {{ number_format($totalPembelian, 0, ',', '.') }}</strong></td>
                    <td><strong>Rp {{ number_format($totalLabaKotor, 0, ',', '.') }}</strong></td>
                    <td><strong>Rp {{ number_format($totalLabaBersih, 0, ',', '.') }}</strong></td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
