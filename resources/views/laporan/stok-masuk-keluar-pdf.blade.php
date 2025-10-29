<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stok Masuk & Keluar - PDF</title>
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
        <h1>Laporan Stok {{ ucfirst($tipe) }}</h1>
        <p>Periode: {{ $tanggalAwal ?? 'Semua Periode' }} - {{ $tanggalAkhir ?? 'Semua Periode' }}</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    @if($data->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="10%">Tanggal</th>
                    <th width="12%">Jenis Transaksi</th>
                    <th width="15%">Nomor Transaksi</th>
                    <th width="20%">Nama Barang</th>
                    <th width="8%">Jumlah</th>
                    <th width="8%">Satuan</th>
                    <th width="10%">Harga</th>
                    <th width="12%">{{ $tipe === 'masuk' ? 'Supplier' : 'Pelanggan' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                        <td>{{ $item->jenis_transaksi }}</td>
                        <td>{{ $item->nomor_transaksi }}</td>
                        <td>{{ $item->nama_barang }}</td>
                        <td class="text-right">{{ number_format($item->jumlah, 0, ',', '.') }}</td>
                        <td>{{ $item->nama_satuan }}</td>
                        <td class="text-right">Rp {{ number_format($item->{$tipe === 'masuk' ? 'harga_beli' : 'harga_jual'}, 0, ',', '.') }}</td>
                        <td>{{ $item->{$tipe === 'masuk' ? 'supplier' : 'pelanggan'} }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            Tidak ada data untuk periode yang dipilih.
        </div>
    @endif

    <div class="footer">
        <p>Dicetak oleh: {{ auth()->user()->name ?? 'System' }}</p>
    </div>
</body>
</html>
