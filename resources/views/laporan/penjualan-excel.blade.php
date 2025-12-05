<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kode Penjualan</th>
            <th>Tanggal Penjualan</th>
            <th>Nama Pelanggan</th>
            <th>Jumlah Item</th>
            <th>Total Penjualan</th>
            <th>Diskon</th>
            <th>PPN</th>
            <th>Pembulatan</th>
            <th>Grand Total</th>
            <th>Dibayar</th>
            <th>Kembalian</th>
            <th>Metode Pembayaran</th>
            <th>Kasir</th>
            <th>Laba</th>
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
                <td>{{ $item->total_modal }}</td>
                <td>{{ $item->total }}</td>
                <td>{{ $item->diskon }}</td>
                <td>{{ $item->ppn }}</td>
                <td>{{ $item->pembulatan }}</td>
                <td>{{ $item->grand_total }}</td>
                <td>{{ $item->dibayar }}</td>
                <td>{{ $item->kembalian }}</td>
                <td>{{ $item->jenis_pembayaran == 'tunai' ? 'tunai' : 'non-tunai' }}</td>
                <td>{{ $item->kasir_name }}</td>
                <td>{{ $item->laba_kotor }}</td>
                <td>{{ $item->laba_bersih }}</td>
                <td>{{ $item->status }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="16" style="text-align: center;">Tidak ada data penjualan pada periode ini.</td>
            </tr>
        @endforelse
        @if($data->count() > 0)
            <tr style="font-weight: bold; background-color: #f8f9fa;">
                <td colspan="4" style="text-align: right;">TOTAL:</td>
                <td>{{ $summary['total_transaksi'] }}</td>
                <td>{{ $summary['total_penjualan'] }}</td>
                <td>{{ $summary['total_diskon'] }}</td>
                <td>{{ $summary['total_ppn'] }}</td>
                <td>{{ $summary['total_pembulatan'] }}</td>
                <td>{{ $summary['total_grand_total'] }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{ $summary['total_laba'] }}</td>
                <td></td>
            </tr>
        @endif
    </tbody>
</table>
