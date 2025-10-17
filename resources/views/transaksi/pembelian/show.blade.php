@include('layout.header')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Detail Pembelian - {{ $pembelian->kode_pembelian }}</h5>
                    <a href="{{ route('pembelian.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Kode Pembelian</th>
                                    <td>: {{ $pembelian->kode_pembelian }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td>: {{ $pembelian->tanggal_pembelian->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Supplier</th>
                                    <td>: {{ $pembelian->supplier->nama_supplier }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>: 
                                        @if($pembelian->status == 'selesai')
                                            <span class="badge bg-success">Selesai</span>
                                        @elseif($pembelian->status == 'draft')
                                            <span class="badge bg-warning">Draft</span>
                                        @else
                                            <span class="badge bg-danger">Batal</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Subtotal</th>
                                    <td>: Rp {{ number_format($pembelian->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Diskon</th>
                                    <td>: Rp {{ number_format($pembelian->diskon, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>PPN</th>
                                    <td>: Rp {{ number_format($pembelian->ppn, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <td>: <strong>Rp {{ number_format($pembelian->total, 0, ',', '.') }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($pembelian->catatan)
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label"><strong>Catatan:</strong></label>
                                <p>{{ $pembelian->catatan }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <hr>
                    <h6>Detail Barang</h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th>Qty</th>
                                    <th>Harga Beli</th>
                                    <th>Subtotal</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pembelian->details as $index => $detail)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $detail->barang->nama_barang }}</td>
                                    <td>{{ $detail->satuan->nama_satuan }}</td>
                                    <td>{{ number_format($detail->qty, 2) }}</td>
                                    <td>Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                    <td>{{ $detail->keterangan ?: '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        @if($pembelian->status == 'draft')
                        <button class="btn btn-success btn-sm" onclick="ubahStatus('selesai')">
                            <i class="fas fa-check"></i> Selesaikan Pembelian
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="ubahStatus('batal')">
                            <i class="fas fa-times"></i> Batalkan Pembelian
                        </button>
                        @endif
                        <button class="btn btn-warning btn-sm" onclick="printDetail()">
                            <i class="fas fa-print"></i> Cetak
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function ubahStatus(status) {
    var statusText = status === 'selesai' ? 'menyelesaikan' : 'membatalkan';
    var confirmText = status === 'selesai' ?
        'Setelah diselesaikan, stok barang akan bertambah dan pembelian tidak dapat dibatalkan.' :
        'Setelah dibatalkan, stok barang akan dikembalikan dan pembelian tidak dapat diubah lagi.';

    Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin ingin ' + statusText + ' pembelian ini? ' + confirmText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: status === 'selesai' ? '#28a745' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("pembelian.update-status", ":id") }}'.replace(':id', '{{ $pembelian->id }}'),
                type: 'PATCH',
                data: {
                    status: status,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2000
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = 'Terjadi kesalahan:';
                    if (errors) {
                        for (var key in errors) {
                            errorMessage += '\n- ' + errors[key][0];
                        }
                    }
                    Swal.fire('Error!', errorMessage, 'error');
                }
            });
        }
    });
}

function printDetail() {
    window.print();
}
</script>

<style>
@media print {
    .btn, .card-header {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

@include('layout.footer')
