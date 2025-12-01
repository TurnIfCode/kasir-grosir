<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Saldo Kas - PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
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
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .summary {
            margin-top: 30px;
            border-top: 2px solid #000;
            padding-top: 10px;
        }
        .summary table {
            margin-top: 10px;
        }
        .no-data {
            text-align: center;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Saldo Kas</h1>
        <p>Periode: {{ $tanggalDari }} - {{ $tanggalSampai }}</p>
    </div>

    @if(count($data) > 0)
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kas</th>
                    <th>Saldo Awal</th>
                    <th>Total Masuk</th>
                    <th>Total Keluar</th>
                    <th>Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['nama_kas'] }}</td>
                    <td class="text-right">Rp {{ number_format($row['saldo_awal'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['total_masuk'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['total_keluar'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['saldo_akhir'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <h4>Ringkasan</h4>
            <table>
                <tr>
                    <td width="25%"><strong>Total Saldo Awal:</strong></td>
                    <td class="text-right">Rp {{ number_format($ringkasan['total_saldo_awal'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Total Masuk:</strong></td>
                    <td class="text-right">Rp {{ number_format($ringkasan['total_masuk'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Total Keluar:</strong></td>
                    <td class="text-right">Rp {{ number_format($ringkasan['total_keluar'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Total Saldo Akhir:</strong></td>
                    <td class="text-right">Rp {{ number_format($ringkasan['total_saldo_akhir'], 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    @else
        <div class="no-data">
            <p>Tidak ada data kas pada periode ini.</p>
        </div>
    @endif
</body>
</html>
