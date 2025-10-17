<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekap Bulanan - {{ $bulanFormatted }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 5px; text-align: left; }
        th { background-color: #f0f0f0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .summary { margin-bottom: 20px; }
        .summary table { width: 100%; }
        .summary td { padding: 3px; }
        .section-title { font-weight: bold; margin-top: 20px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Rekap Bulanan</h2>
        <p>Bulan: {{ $bulanFormatted }}</p>
        <p>Periode: {{ $tanggalAwal }} - {{ $tanggalAkhir }}</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <h4>Ringkasan</h4>
        <table>
            <tr>
                <td width="30%">Total Penjualan</td>
                <td width="20%">{{ $ringkasan['total_penjualan'] }}</td>
                <td width="30%">Jumlah Transaksi Penjualan</td>
                <td width="20%">{{ $ringkasan['jumlah_transaksi_penjualan'] }}</td>
            </tr>
            <tr>
                <td>Total Pembelian</td>
                <td>{{ $ringkasan['total_pembelian'] }}</td>
                <td>Jumlah Transaksi Pembelian</td>
                <td>{{ $ringkasan['jumlah_transaksi_pembelian'] }}</td>
            </tr>
            <tr>
                <td>Total Barang Terjual</td>
                <td>{{ $ringkasan['total_barang_terjual'] }}</td>
                <td>Laba Kotor</td>
                <td>{{ $ringkasan['laba_kotor'] }}</td>
            </tr>
            <tr>
                <td>Total Barang Dibeli</td>
                <td>{{ $ringkasan['total_barang_dibeli'] }}</td>
                <td>Barang Terlaris</td>
                <td>{{ $ringkasan['barang_terlaris'] }}</td>
            </tr>
            <tr>
                <td>Rata-rata Penjualan Harian</td>
                <td>{{ $ringkasan['rata_penjualan_harian'] }}</td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>

    <div class="section-title">Rincian Penjualan</div>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode Barang</th>
                <th width="25%">Nama Barang</th>
                <th width="10%">Satuan</th>
                <th width="15%">Jumlah Terjual</th>
                <th width="15%">Harga</th>
                <th width="15%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataPenjualan as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->nama_satuan }}</td>
                <td class="text-right">{{ number_format($item->jumlah_terjual, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Rincian Pembelian</div>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode Barang</th>
                <th width="25%">Nama Barang</th>
                <th width="10%">Satuan</th>
                <th width="15%">Jumlah Dibeli</th>
                <th width="15%">Harga</th>
                <th width="15%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataPembelian as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->nama_satuan }}</td>
                <td class="text-right">{{ number_format($item->jumlah_dibeli, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 50px; text-align: right;">
        <p>Kasir</p>
        <br><br><br>
        <p>____________________</p>
        <p>{{ auth()->user()->name }}</p>
    </div>
</body>
</html>
