<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th>Jumlah Terjual</th>
            <th>Total Modal (HPP)</th>
            <th>Total Penjualan</th>
            <th>Laba Bersih</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->kode_barang }}</td>
            <td>{{ $item->nama_barang }}</td>
            <td>{{ $item->nama_kategori ?? '-' }}</td>
            <td>{{ number_format($item->jumlah_terjual, 2, ',', '.') }}</td>
            <td>Rp {{ number_format($item->total_modal, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item->total_penjualan, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item->laba_bersih, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
