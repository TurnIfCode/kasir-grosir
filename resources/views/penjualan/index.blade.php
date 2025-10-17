@include('layout.header')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Daftar Penjualan</h5>
                    <a href="{{ route('penjualan.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Penjualan
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="penjualanTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Kode Penjualan</th>
                                    <th>Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th>Grand Total</th>
                                    <th>Jenis Pembayaran</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#penjualanTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: '{{ route("penjualan.data") }}',
        columns: [
            { data: 'kode_penjualan', name: 'kode_penjualan' },
            { data: 'tanggal_penjualan', name: 'tanggal_penjualan' },
            { data: 'pelanggan', name: 'pelanggan', defaultContent: '-' },
            { data: 'grand_total', name: 'grand_total' },
            { data: 'jenis_pembayaran', name: 'jenis_pembayaran' },
            { data: 'status', name: 'status' },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });
});


</script>

@include('layout.footer')
