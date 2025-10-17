@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Data Pelanggan</h3>
  <div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
      <a href="{{ route('pelanggan.add') }}" class="btn btn-primary">Tambah Pelanggan</a>
    </div>
    <table id="pelangganTable" class="table table-striped">
      <thead>
        <tr>
          <th>No</th>
          <th>Kode Pelanggan</th>
          <th>Nama Pelanggan</th>
          <th>Telepon</th>
          <th>Email</th>
          <th>Alamat</th>
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
  var table = $('#pelangganTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '{{ route("pelanggan.data") }}',
      type: 'GET'
    },
    columns: [
      { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
      { data: 'kode_pelanggan', name: 'kode_pelanggan' },
      { data: 'nama_pelanggan', name: 'nama_pelanggan' },
      { data: 'telepon', name: 'telepon' },
      { data: 'email', name: 'email' },
      { data: 'alamat', name: 'alamat' },
      { data: 'status', name: 'status' },
      { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
    ],
    order: [[1, 'asc']]
  });

  // Edit handler
  $(document).on('click', '#btnEdit', function() {
    var pelangganId = $(this).data('id');
    $.ajax({
      url: '{{ route("pelanggan.find", ":id") }}'.replace(':id', pelangganId),
      type: 'GET',
      success: function(response) {
        $('#pelangganId').val(response.id);
        $('#edit_kode_pelanggan').val(response.kode_pelanggan);
        $('#edit_nama_pelanggan').val(response.nama_pelanggan);
        $('#edit_telepon').val(response.telepon);
        $('#edit_email').val(response.email);
        $('#edit_alamat').val(response.alamat);
        $('#edit_status').val(response.status);

        $('#editPelangganModal').modal('show');
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
      }
    });
  });

  // Delete handler
  $(document).on('click', '#btnDelete', function() {
    var pelangganId = $(this).data('id');
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
          url: '{{ route("pelanggan.delete", ":id") }}'.replace(':id', pelangganId),
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

  // Update button handler
  $('#btnUpdatePelanggan').on('click', function(e) {
    e.preventDefault();
    if ($('#editPelangganForm').valid()) {
      $('#editPelangganForm').submit();
    } else {
      validator.focusInvalid();
    }
  });
});
</script>
@endsection

<!-- Modal Edit Pelanggan -->
<div class="modal fade" id="editPelangganModal" tabindex="-1" aria-labelledby="editPelangganModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editPelangganModalLabel">Edit Pelanggan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editPelangganForm" action="#" method="POST">
          @csrf
          <input type="hidden" id="pelangganId" name="id">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_kode_pelanggan" class="form-label">Kode Pelanggan*</label>
                <input type="text" class="form-control" id="edit_kode_pelanggan" name="kode_pelanggan" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_nama_pelanggan" class="form-label">Nama Pelanggan*</label>
                <input type="text" class="form-control" id="edit_nama_pelanggan" name="nama_pelanggan" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_telepon" class="form-label">Telepon</label>
                <input type="text" class="form-control" id="edit_telepon" name="telepon">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_email" class="form-label">Email</label>
                <input type="email" class="form-control" id="edit_email" name="email">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_alamat" class="form-label">Alamat</label>
                <textarea class="form-control" id="edit_alamat" name="alamat" rows="3"></textarea>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_status" class="form-label">Status*</label>
                <select class="form-control" id="edit_status" name="status" required>
                  <option value="aktif">Aktif</option>
                  <option value="non_aktif">Non Aktif</option>
                </select>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnUpdatePelanggan">Simpan</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  // Form validation
  var validator = $('#editPelangganForm').validate({
    rules: {
      kode_pelanggan: { required: true },
      nama_pelanggan: { required: true },
      status: { required: true }
    },
    messages: {
      kode_pelanggan: { required: 'Kode Pelanggan wajib diisi' },
      nama_pelanggan: { required: 'Nama Pelanggan wajib diisi' },
      status: { required: 'Status wajib dipilih' }
    },
    errorElement: 'div',
    errorClass: 'invalid-feedback',
    highlight: function(element) {
      $(element).addClass('is-invalid');
    },
    unhighlight: function(element) {
      $(element).removeClass('is-invalid');
    }
  });

  // Submit form handler
  $('#editPelangganForm').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    formData.append('_method', 'PUT');
    $.ajax({
      url: '{{ route("pelanggan.update", ":id") }}'.replace(':id', $('#pelangganId').val()),
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        if (response.status) {
          Swal.fire('Berhasil!', response.message, 'success');
          $('#editPelangganModal').modal('hide');
          table.ajax.reload();
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
        }
      },
      error: function(xhr) {
        var errors = xhr.responseJSON.errors;
        if (errors) {
          var errorMessage = '';
          for (var key in errors) {
            errorMessage += errors[key][0] + '\n';
          }
          Swal.fire({ icon: 'error', title: 'Validation Error', text: errorMessage });
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
        }
      }
    });
  });
});
</script>

@include('layout.footer')
