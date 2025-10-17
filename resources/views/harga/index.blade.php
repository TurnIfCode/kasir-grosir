@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Data Harga Barang</h3>
  <div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
      <a href="{{ route('harga-barang.create') }}" class="btn btn-primary">Tambah Harga Barang</a>
    </div>
    <table id="hargaTable" class="table table-striped">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama Barang</th>
          <th>Satuan</th>
          <th>Tipe Harga</th>
          <th>Harga</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
  // DataTable
  var table = $('#hargaTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '{{ route("harga-barang.data") }}',
      type: 'GET'
    },
    columns: [
      { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
      { data: 'barang', name: 'barang' },
      { data: 'satuan', name: 'satuan' },
      { data: 'tipe_harga', name: 'tipe_harga' },
      { data: 'harga', name: 'harga' },
      { data: 'status', name: 'status' },
      { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
    ],
    order: [[1, 'asc']]
  });

  // Edit handler
  $(document).on('click', '#btnEdit', function() {
    var hargaId = $(this).data('id');
    window.location.href = '{{ route("harga-barang.edit", ":id") }}'.replace(':id', hargaId);
  });

  // Delete handler
  $(document).on('click', '#btnDelete', function() {
    var hargaId = $(this).data('id');
    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: "Data yang dihapus tidak dapat dikembalikan!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: '{{ route("harga-barang.destroy", ":id") }}'.replace(':id', hargaId),
          type: 'DELETE',
          data: { _token: '{{ csrf_token() }}' },
          success: function(response) {
            if (response.success) {
              Swal.fire('Terhapus!', 'Harga barang berhasil dihapus.', 'success');
              table.ajax.reload();
            } else {
              Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
            }
          },
          error: function() {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
          }
        });
      }
    });
  });
});
</script>
@endsection

@include('layout.footer')
