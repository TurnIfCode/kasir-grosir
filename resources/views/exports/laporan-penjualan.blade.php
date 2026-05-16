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
                <td>Rp {{ number_format((float) $item->total_hpp, 0, ',', '.') }}</td>
                <td>Rp {{ number_format((float) $item->total, 0, ',', '.') }}</td>
                <td>Rp {{ number_format((float) $item->pembulatan, 0, ',', '.') }}</td>
                <td>Rp {{ number_format((float) $item->grand_total, 0, ',', '.') }}</td>
                <td>Rp {{ number_format((float) $item->dibayar, 0, ',', '.') }}</td>
                <td>Rp {{ number_format((float) $item->kembalian, 0, ',', '.') }}</td>
                <td>{{ $item->jenis_pembayaran == 'tunai' ? 'tunai' : 'non-tunai' }}</td>
                <td>{{ $item->kasir_name }}</td>
                <td>{{ $item->laba }}</td>
                <td>{{ $item->laba }}</td>
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
<table style="margin-top: 20px;">
    <thead>
        <tr>
            <th>Total Transaksi</th>
            <th>Total Laba Kotor</th>
            <th>Total Modal (HPP)</th>
            <th>Laba Bersih</th>
        </tr>
    </thead>
    <tbody>
            <tr>
                <td>{{ $summary['total_transaksi'] }}</td>
                <td>Rp {{ number_format((float) $summary['total_laba_kotor'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format((float) $summary['total_modal'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format((float) $summary['total_laba_bersih'], 0, ',', '.') }}</td>
            </tr>
    </tbody>
</table>
@endif
