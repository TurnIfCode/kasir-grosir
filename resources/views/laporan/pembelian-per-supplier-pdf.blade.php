<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian per Supplier PDF</title>
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
        <h1>Laporan Pembelian per Supplier</h1>
        <p>Supplier: {{ $supplier->nama_supplier ?? 'Semua Supplier' }}</p>
        <p>Periode: {{ $tanggalDari }} - {{ $tanggalSampai }}</p>
        @if(isset($ringkasan))
        <div style="margin-top: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
            <h3>Ringkasan</h3>
            <p><strong>Total Transaksi:</strong> {{ $ringkasan['total_transaksi'] }}</p>
            <p><strong>Total Nilai:</strong> {{ $ringkasan['total_nilai'] }}</p>
            <p><strong>Jumlah Barang Masuk:</strong> {{ $ringkasan['total_barang_masuk'] }}</p>
        </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nomor Transaksi</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Satuan</th>
                <th>Harga</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal_pembelian)->format('d/m/Y') }}</td>
                    <td>{{ $item->kode_pembelian }}</td>
                    <td>{{ $item->barang->nama_barang ?? '-' }}</td>
                    <td>{{ number_format($item->qty, 2, ',', '.') }}</td>
                    <td>{{ $item->satuan->nama_satuan ?? '-' }}</td>
                    <td>Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data pembelian pada periode ini.</td>
                </tr>
            @endforelse
            @if($data->count() > 0)
                <tr class="total-row">
                    <td colspan="6" style="text-align: right;"><strong>Total Akumulasi:</strong></td>
                    <td><strong>Rp {{ number_format($totalAkumulasi, 0, ',', '.') }}</strong></td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
