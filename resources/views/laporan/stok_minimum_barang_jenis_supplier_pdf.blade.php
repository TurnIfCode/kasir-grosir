<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Stok Minimum</title>

    <style>
        body{
            font-family: DejaVu Sans;
            font-size:12px;
        }

        h2{
            text-align:center;
            margin-bottom:5px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th,td{
            border:1px solid #000;
            padding:6px;
        }

        th{
            background:#e9ecef;
            text-align:center;
        }

        .text-center{
            text-align:center;
        }
    </style>
</head>
<body>

<h2>LAPORAN STOK MINIMUM BARANG</h2>

<p>
    <strong>Supplier :</strong>
    {{ $supplier->nama_supplier ?? '-' }}
</p>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Stok Saat Ini</th>
            <th>Minimum Stok</th>
            <th>Harus Order</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>

        @forelse($data as $index => $item)

        <tr style="background-color: {{ $item['warna'] }}">
            <td class="text-center">
                {{ $index + 1 }}
            </td>

            <td>
                {{ $item['kode_barang'] }}
            </td>

            <td>
                {{ $item['nama_barang'] }}
            </td>

            <td>
                {{ $item['stok_text'] }}
            </td>

            <td>
                {{ $item['minimum_text'] }}
            </td>

            <td>
                {{ $item['kekurangan_text'] }}
            </td>

            <td class="text-center">
                {{ $item['status'] }}
            </td>
        </tr>

        @empty

        <tr>
            <td colspan="7" class="text-center">
                Tidak ada data
            </td>
        </tr>

        @endforelse

    </tbody>
</table>

</body>
</html>