@include('layout.header')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
        <div class="card-header">
            <h5 class="card-title">Daftar Pembelian</h5>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('pembelian.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Pembelian
                    </a>
                </div>
                <div class="d-flex gap-2">
                    <select id="filterSupplier" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Semua Supplier</option>
                        @foreach(\App\Models\Supplier::where('status', 'AKTIF')->get() as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->nama_supplier }}</option>
                        @endforeach
                    </select>
                    <select id="filterStatus" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="selesai">Selesai</option>
                        <option value="batal">Batal</option>
                    </select>
                    <input type="date" id="filterTanggalDari" class="form-control form-control-sm" style="width: auto;" placeholder="Tanggal Dari">
                    <input type="date" id="filterTanggalSampai" class="form-control form-control-sm" style="width: auto;" placeholder="Tanggal Sampai">
                    <button id="filterBtn" class="btn btn-secondary btn-sm">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </div>
        </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="pembelianTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Pembelian</th>
                                    <th>Tanggal</th>
                                    <th>Supplier</th>
                                    <th>Total</th>
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
    // Set filterTanggalDari and filterTanggalSampai to today's date
    var today = new Date().toISOString().split('T')[0];
    $('#filterTanggalDari').val(today);
    $('#filterTanggalSampai').val(today);

    var table = $('#pembelianTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("pembelian.data") }}',
            type: 'GET',
            data: function(d) {
                d.supplier_id = $('#filterSupplier').val();
                d.status = $('#filterStatus').val();
                d.tanggal_dari = $('#filterTanggalDari').val();
                d.tanggal_sampai = $('#filterTanggalSampai').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'kode_pembelian', name: 'kode_pembelian' },
            { data: 'tanggal_pembelian_formatted', name: 'tanggal_pembelian' },
            { data: 'supplier_nama', name: 'supplier' },
            { data: 'total_formatted', name: 'total' },
            { data: 'status', name: 'status' },
            { data: 'aksi', orderable: false, searchable: false }
        ],
        language: {
            emptyTable: "Tidak ada data pembelian"
        }
    });

    // Filter functionality
    $('#filterBtn').click(function() {
        table.ajax.reload();
    });

    // Enter key on filter inputs
    $('#filterSupplier, #filterStatus, #filterTanggalDari, #filterTanggalSampai').on('change', function() {
        table.ajax.reload();
    });

    // Delete functionality
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data pembelian akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("pembelian.index") }}/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire('Berhasil!', response.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('Gagal!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Terjadi kesalahan saat menghapus data', 'error');
                    }
                });
            }
        });
    });


});
</script>

@include('layout.footer')
