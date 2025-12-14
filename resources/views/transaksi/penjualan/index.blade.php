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
                    <!-- Filter Form -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                            <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-primary me-2" id="btnFilter">Filter</button>
                            <button type="button" class="btn btn-secondary" id="btnReset">Reset</button>
                        </div>
                    </div>

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
    var table = $('#penjualanTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("penjualan.data") }}',
            data: function(d) {
                d.tanggal_awal = $('#tanggal_awal').val();
                d.tanggal_akhir = $('#tanggal_akhir').val();
            }
        },
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

    $('#btnFilter').on('click', function() {
        table.ajax.reload();
    });

    $('#btnReset').on('click', function() {
        $('#tanggal_awal').val('{{ date('Y-m-d') }}');
        $('#tanggal_akhir').val('{{ date('Y-m-d') }}');
        table.ajax.reload();
    });
});


</script>

@include('layout.footer')
