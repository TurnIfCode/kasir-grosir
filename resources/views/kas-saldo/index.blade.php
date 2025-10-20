@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Data Saldo Kas</h3>
  <div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
      <a href="{{ route('kas-saldo.create') }}" class="btn btn-primary">Tambah Saldo Kas</a>
    </div>
    <table id="kasSaldoTable" class="table table-striped">
      <thead>
        <tr>
          <th>Sumber Kas</th>
          <th>Saldo Awal</th>
          <th>Saldo Akhir</th>
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
  var table = $('#kasSaldoTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '{{ route("kas-saldo.data") }}',
      type: 'GET'
    },
    columns: [
      { data: 'sumber_kas', name: 'sumber_kas' },
      { data: 'saldo_awal', name: 'saldo_awal' },
      { data: 'saldo_akhir', name: 'saldo_akhir' },
      { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
    ],
    order: [[0, 'asc']],
    responsive: true,
    pageLength: 10,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
    language: {
      search: "Cari:",
      lengthMenu: "Tampilkan _MENU_ data per halaman",
      zeroRecords: "Data tidak ditemukan",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
      infoEmpty: "Tidak ada data yang tersedia",
      infoFiltered: "(difilter dari _MAX_ total data)",
      paginate: {
        first: "Pertama",
        last: "Terakhir",
        next: "Selanjutnya",
        previous: "Sebelumnya"
      }
    }
  });

  // Delete handler
  $(document).on('click', '#btnDelete', function() {
    var saldoId = $(this).data('id');
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
          url: '{{ route("kas-saldo.delete", ":id") }}'.replace(':id', saldoId),
          type: 'DELETE',
          data: { _token: '{{ csrf_token() }}' },
          success: function(response) {
            if (response.status) {
              Swal.fire('Terhapus!', response.message, 'success');
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
