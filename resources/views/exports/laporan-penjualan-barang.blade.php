<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th>Jumlah Terjual</th>
            <th>Total Nilai Penjualan</th>
            <th>Harga Beli</th>
            <th>Harga Jual</th>
            <th>Margin Keuntungan</th>
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
            <td>Rp {{ number_format($item->total_nilai_penjualan, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item->margin_keuntungan, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
