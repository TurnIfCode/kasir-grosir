@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Data Supplier</h3>
  <div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
      <a href="{{ route('supplier.add') }}" class="btn btn-primary">Tambah Supplier</a>
    </div>
    <table id="supplierTable" class="table table-striped">
      <thead>
        <tr>
          <th>No</th>
          <th>Kode Supplier</th>
          <th>Nama Supplier</th>
          <th>Kontak Person</th>
          <th>Telepon</th>
          <th>Email</th>
          <th>Alamat</th>
          <th>Kota</th>
          <th>Provinsi</th>
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
  var table = $('#supplierTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '{{ route("supplier.data") }}',
      type: 'GET'
    },
    columns: [
      { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
      { data: 'kode_supplier', name: 'kode_supplier' },
      { data: 'nama_supplier', name: 'nama_supplier' },
      { data: 'kontak_person', name: 'kontak_person' },
      { data: 'telepon', name: 'telepon' },
      { data: 'email', name: 'email' },
      { data: 'alamat', name: 'alamat' },
      { data: 'kota', name: 'kota' },
      { data: 'provinsi', name: 'provinsi' },
      { data: 'status', name: 'status' },
      { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
    ],
    order: [[1, 'asc']]
  });

  // Edit handler
  $(document).on('click', '#btnEdit', function() {
    var supplierId = $(this).data('id');
    $.ajax({
      url: '{{ route("supplier.find", ":id") }}'.replace(':id', supplierId),
      type: 'GET',
      success: function(response) {
        $('#supplierId').val(response.id);
        $('#edit_kode_supplier').val(response.kode_supplier);
        $('#edit_nama_supplier').val(response.nama_supplier);
        $('#edit_kontak_person').val(response.kontak_person);
        $('#edit_telepon').val(response.telepon);
        $('#edit_email').val(response.email);
        $('#edit_alamat').val(response.alamat);
        $('#edit_kota').val(response.kota);
        $('#edit_provinsi').val(response.provinsi);
        $('#edit_status').val(response.status);

        $('#editSupplierModal').modal('show');
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
      }
    });
  });

  // Delete handler
  $(document).on('click', '#btnDelete', function() {
    var supplierId = $(this).data('id');
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
          url: '{{ route("supplier.delete", ":id") }}'.replace(':id', supplierId),
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
  $('#btnUpdateSupplier').on('click', function(e) {
    e.preventDefault();
    if ($('#editSupplierForm').valid()) {
      $('#editSupplierForm').submit();
    } else {
      validator.focusInvalid();
    }
  });
});
</script>
@endsection

<!-- Modal Edit Supplier -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editSupplierForm" action="#" method="POST">
          @csrf
          <input type="hidden" id="supplierId" name="id">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_kode_supplier" class="form-label">Kode Supplier*</label>
                <input type="text" class="form-control" id="edit_kode_supplier" name="kode_supplier" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_nama_supplier" class="form-label">Nama Supplier*</label>
                <input type="text" class="form-control" id="edit_nama_supplier" name="nama_supplier" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_kontak_person" class="form-label">Kontak Person</label>
                <input type="text" class="form-control" id="edit_kontak_person" name="kontak_person">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_telepon" class="form-label">Telepon</label>
                <input type="text" class="form-control" id="edit_telepon" name="telepon">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_email" class="form-label">Email</label>
                <input type="email" class="form-control" id="edit_email" name="email">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_status" class="form-label">Status*</label>
                <select class="form-control" id="edit_status" name="status" required>
                  <option value="aktif">Aktif</option>
                  <option value="nonaktif">Nonaktif</option>
                </select>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="edit_alamat" class="form-label">Alamat</label>
            <textarea class="form-control" id="edit_alamat" name="alamat" rows="3"></textarea>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_kota" class="form-label">Kota</label>
                <input type="text" class="form-control" id="edit_kota" name="kota">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_provinsi" class="form-label">Provinsi</label>
                <input type="text" class="form-control" id="edit_provinsi" name="provinsi">
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnUpdateSupplier">Simpan</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  // Form validation
  var validator = $('#editSupplierForm').validate({
    rules: {
      kode_supplier: { required: true },
      nama_supplier: { required: true },
      status: { required: true }
    },
    messages: {
      kode_supplier: { required: 'Kode Supplier wajib diisi' },
      nama_supplier: { required: 'Nama Supplier wajib diisi' },
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
  $('#editSupplierForm').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    formData.append('_method', 'PUT');
    $.ajax({
      url: '{{ route("supplier.update", ":id") }}'.replace(':id', $('#supplierId').val()),
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        if (response.status) {
          Swal.fire('Berhasil!', response.message, 'success');
          $('#editSupplierModal').modal('hide');
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
