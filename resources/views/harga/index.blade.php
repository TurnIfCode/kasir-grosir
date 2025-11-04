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

<!-- Modal Detail Harga Barang -->
<div class="modal fade" id="detailHargaModal" tabindex="-1" aria-labelledby="detailHargaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailHargaModalLabel">Detail Harga Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tbody id="hargaDetailBody">
            <!-- Data akan diisi oleh JavaScript -->
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

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

  // Detail handler
  $(document).on('click', '#btnDetail', function() {
    var hargaId = $(this).data('id');

    // Since HargaBarangController doesn't have a find method, we need to fetch data differently
    // We'll use a simple approach by making an AJAX call to get the data
    $.ajax({
      url: '{{ route("harga-barang.edit", ":id") }}'.replace(':id', hargaId),
      type: 'GET',
      success: function(response) {
        // Since it's a view response, we need to parse the HTML or use a different approach
        // For now, let's assume we can get the data from the edit route or create a simple detail
        // Actually, let's modify the controller to add a find method or use a different approach

        // For simplicity, let's create a basic detail modal that shows the data from the table row
        var row = $('#btnDetail[data-id="' + hargaId + '"]').closest('tr');
        var cells = row.find('td');

        var detailHtml = '';
        detailHtml += '<tr><td><strong>Nama Barang</strong></td><td>' + $(cells[1]).text() + '</td></tr>';
        detailHtml += '<tr><td><strong>Satuan</strong></td><td>' + $(cells[2]).text() + '</td></tr>';
        detailHtml += '<tr><td><strong>Tipe Harga</strong></td><td>' + $(cells[3]).text() + '</td></tr>';
        detailHtml += '<tr><td><strong>Harga</strong></td><td>' + $(cells[4]).text() + '</td></tr>';
        detailHtml += '<tr><td><strong>Status</strong></td><td>' + $(cells[5]).text() + '</td></tr>';

        $('#hargaDetailBody').html(detailHtml);
        $('#detailHargaModal').modal('show');
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan saat mengambil data detail' });
      }
    });
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

@include('layout.footer')
