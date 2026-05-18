<div class="container">
    <h3 class="mb-3">Detail Laporan Penjualan</h3>

    @isset($penjualan)
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div><strong>Kode Penjualan:</strong> {{ $penjualan->kode_penjualan ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div><strong>Tanggal:</strong> {{ $penjualan->tanggal_penjualan ?? '-' }}</div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-6">
                        <div><strong>Kasir:</strong> {{ $penjualan->kasir_name ?? ($penjualan->created_by ?? '-') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div><strong>Pelanggan:</strong> {{ $penjualan->pelanggan->nama ?? ($penjualan->pelanggan->nama_pelanggan ?? '-') }}</div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-4">
                        <div><strong>Total:</strong> {{ $penjualan->total ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div><strong>Diskon:</strong> {{ $penjualan->diskon ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div><strong>PPN:</strong> {{ $penjualan->ppn ?? '-' }}</div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-4">
                        <div><strong>Pembulatan:</strong> {{ $penjualan->pembulatan ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div><strong>Grand Total:</strong> {{ $penjualan->grand_total ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div><strong>Status:</strong> {{ $penjualan->status ?? '-' }}</div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-6">
                        <div><strong>Jenis Pembayaran:</strong> {{ $penjualan->jenis_pembayaran ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div><strong>Dibayar:</strong> {{ $penjualan->dibayar ?? '-' }}</div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-6">
                        <div><strong>Kembalian:</strong> {{ $penjualan->kembalian ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div><strong>Catatan:</strong> {{ $penjualan->catatan ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endisset

    @isset($totalModal)
        <div class="card mb-3">
            <div class="card-body">
                <div><strong>Total Modal (HPP):</strong> {{ $totalModal }}</div>
            </div>
        </div>
    @endisset

    @isset($totalLaba)
        <div class="card mb-3">
            <div class="card-body">
                <div><strong>Total Laba:</strong> {{ $totalLaba }}</div>
            </div>
        </div>
    @endisset

    @if (isset($penjualan) && isset($penjualan->details) && count($penjualan->details))
        <div class="card">
            <div class="card-body">
                <h5>Detail Barang</h5>
                <div class="table-responsive mt-2">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($penjualan->details as $d)
                                <tr>
                                    <td>{{ $d->barang->nama_barang ?? $d->barang->nama ?? ($d->nama_barang ?? '-') }}</td>
                                    <td>{{ $d->qty_konversi ?? $d->qty ?? '-' }}</td>
                                    <td>{{ $d->harga_jual ?? $d->harga ?? '-' }}</td>
                                    <td>{{ $d->subtotal ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-3">
        <a href="{{ url('/laporan/penjualan') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
