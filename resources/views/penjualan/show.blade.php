@include('layout.header')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Detail Penjualan: {{ $penjualan->kode_penjualan }}</h5>
                    <a href="{{ route('penjualan.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <!-- Header Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Kode Penjualan</th>
                                    <td>: {{ $penjualan->kode_penjualan }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td>: {{ $penjualan->tanggal_penjualan->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Pelanggan</th>
                                    <td>: {{ $penjualan->pelanggan ? $penjualan->pelanggan->nama_pelanggan : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>: <span class="badge bg-success">{{ $penjualan->status }}</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Jenis Pembayaran</th>
                                    <td>: {{ ucfirst($penjualan->jenis_pembayaran) }}</td>
                                </tr>
                                @if($penjualan->jenis_pembayaran == 'tunai')
                                <tr>
                                    <th>Dibayar</th>
                                    <td>: Rp {{ number_format($penjualan->dibayar, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Kembalian</th>
                                    <td>: Rp {{ number_format($penjualan->kembalian, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Catatan</th>
                                    <td>: {{ $penjualan->catatan ?: '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Detail Barang -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Detail Barang</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kode Barang</th>
                                            <th>Nama Barang</th>
                                            <th>Satuan</th>
                                            <th>Qty</th>
                                            <th>Harga Jual</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($penjualan->details as $index => $detail)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $detail->barang->kode_barang }}</td>
                                            <td>{{ $detail->barang->nama_barang }}</td>
                                            <td>{{ $detail->satuan->nama_satuan }}</td>
                                            <td>{{ number_format($detail->qty, 2) }}</td>
                                            <td>Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pembayaran Campuran -->
                    @if($penjualan->jenis_pembayaran == 'campuran' && $penjualan->pembayarans->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Detail Pembayaran</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Metode</th>
                                            <th>Nominal</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($penjualan->pembayarans as $index => $pembayaran)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ ucfirst($pembayaran->metode) }}</td>
                                            <td>Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</td>
                                            <td>{{ $pembayaran->keterangan ?: '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Summary -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row text-end">
                                <div class="col-md-8 offset-md-4">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="150">Total</th>
                                            <td class="text-end">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Diskon</th>
                                            <td class="text-end">Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Subtotal</th>
                                            <td class="text-end">Rp {{ number_format($penjualan->total - $penjualan->diskon, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <th>PPN</th>
                                            <td class="text-end">Rp {{ number_format($penjualan->ppn, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr class="border-top">
                                            <th><strong>Grand Total</strong></th>
                                            <td class="text-end"><strong>Rp {{ number_format($penjualan->grand_total, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layout.footer')
