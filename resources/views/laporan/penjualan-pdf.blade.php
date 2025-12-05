<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan PDF</title>
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
        <h1>Laporan Penjualan</h1>
        <p>Periode: {{ $tanggalDari }} - {{ $tanggalSampai }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Penjualan</th>
                <th>Tanggal Penjualan</th>
                <th>Nama Pelanggan</th>
                <th>Jumlah Item</th>
                <th>Total Modal</th>
                <th>Total Penjualan</th>
                <th>Pembulatan</th>
                <th>Grand Total</th>
                <th>Dibayar</th>
                <th>Kembalian</th>
                <th>Metode Pembayaran</th>
                <th>Kasir</th>
                <th>Laba</th>
                <th>Laba Bersih</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->kode_penjualan }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal_penjualan)->format('d/m/Y') }}</td>
                    <td>{{ $item->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
                    <td>{{ $item->jumlah_item }}</td>
                    <td>Rp {{ number_format($item->total_hpp, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->pembulatan, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->dibayar, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->kembalian, 0, ',', '.') }}</td>
                    <td>{{ $item->jenis_pembayaran == 'tunai' ? 'tunai' : 'non-tunai' }}</td>
                    <td>{{ $item->kasir_name }}</td>
                    <td>Rp {{ number_format($item->laba, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->laba, 0, ',', '.') }}</td>
                    <td>{{ $item->status == 'selesai' ? 'selesai' : 'pending' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="16" style="text-align: center;">Tidak ada data penjualan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($data->count() > 0)
    <div class="summary" style="margin-top: 20px;">
        <h3>Ringkasan</h3>
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Total Transaksi</th>
                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Total Penjualan</th>
                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Total Pembulatan</th>
                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Total Laba Kotor</th>
                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Total Modal (HPP)</th>
                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Laba Bersih</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $summary['total_transaksi'] }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">Rp {{ number_format($summary['total_penjualan'], 0, ',', '.') }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">Rp {{ number_format($summary['total_pembulatan'], 0, ',', '.') }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">Rp {{ number_format($summary['total_laba_kotor'], 0, ',', '.') }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">Rp {{ number_format($summary['total_modal'], 0, ',', '.') }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">Rp {{ number_format($summary['total_laba_bersih'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
