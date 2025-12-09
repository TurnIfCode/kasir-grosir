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

          <div class="form-group mb-3">
            <label for="edit_kode_supplier">Kode Supplier</label>
            <input type="text" class="form-control" id="edit_kode_supplier" name="kode_supplier" readonly>
            <small class="form-text text-muted">Kode supplier tidak dapat diubah</small>
          </div>

          <div class="form-group mb-3">
            <label for="edit_nama_supplier">Nama Supplier*</label>
            <input type="text" class="form-control" id="edit_nama_supplier" name="nama_supplier">
          </div>

          <div class="form-group mb-3">
            <label for="edit_kontak_person">Kontak</label>
            <input type="text" class="form-control" id="edit_kontak_person" name="kontak_person">
          </div>

          <div class="form-group mb-3">
            <label for="edit_telepon">Telepon</label>
            <input type="text" class="form-control" id="edit_telepon" name="telepon">
          </div>

          <div class="form-group mb-3">
            <label for="edit_email">Email</label>
            <input type="email" class="form-control" id="edit_email" name="email">
          </div>

          <div class="form-group mb-3">
            <label for="edit_alamat">Alamat</label>
            <textarea class="form-control" id="edit_alamat" name="alamat" rows="3">-</textarea>
          </div>

          <div class="form-group mb-3">
            <label for="edit_kota">Kota</label>
            <input type="text" class="form-control" id="edit_kota" name="kota">
          </div>

          <div class="form-group mb-3">
            <label for="edit_provinsi">Provinsi</label>
            <input type="text" class="form-control" id="edit_provinsi" name="provinsi">
          </div>

          <div class="form-group">
            <label for="edit_status">Status *</label>
            <select class="form-control" id="edit_status" name="status">
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
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

<!-- Modal Detail Supplier -->
<div class="modal fade" id="detailSupplierModal" tabindex="-1" aria-labelledby="detailSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailSupplierModalLabel">Detail Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tbody id="supplierDetailBody">
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


@include('layout.footer')

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
    order: [[0, 'asc']]
  });

  // Detail handler
  $(document).on('click', '#btnDetail', function() {
    var supplierId = $(this).data('id');
    $.ajax({
      url: '{{ route("supplier.find", ":id") }}'.replace(':id', supplierId),
      type: 'GET',
      success: function(response) {
        var detailHtml = '';

        if (response.success) {
          // Format data untuk tabel detail
          detailHtml += '<tr><td><strong>Kode Supplier</strong></td><td>' + (response.data.kode_supplier || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Nama Supplier</strong></td><td>' + (response.data.nama_supplier || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Kontak</strong></td><td>' + (response.data.kontak_person || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Telepon</strong></td><td>' + (response.data.telepon || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Email</strong></td><td>' + (response.data.email || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Alamat</strong></td><td>' + (response.data.alamat || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Kota</strong></td><td>' + (response.data.kota || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Provinsi</strong></td><td>' + (response.data.provinsi || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Status</strong></td><td>' + (response.data.status || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Dibuat Oleh</strong></td><td>' + (response.data.created_by || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Tanggal Dibuat</strong></td><td>' + (response.data.created_at ? new Date(response.data.created_at).toLocaleString('id-ID') : '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Diubah Oleh</strong></td><td>' + (response.data.updated_by || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Tanggal Diubah</strong></td><td>' + (response.data.updated_at ? new Date(response.data.updated_at).toLocaleString('id-ID') : '-') + '</td></tr>';

          $('#supplierDetailBody').html(detailHtml);
          $('#detailSupplierModal').modal('show');
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: response.message
          }).then(function() {
            setTimeout(() => {
              table.ajax.reload();
            }, 500);
          });
        }
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
      }
    });
  });

  // Edit handler
  $(document).on('click', '#btnEdit', function() {
    var supplierId = $(this).data('id');
    $.ajax({
      url: '{{ route("supplier.find", ":id") }}'.replace(':id', supplierId),
      type: 'GET',
      success: function(response) {
        if (response.success) {
          $('#supplierId').val(response.data.id);
          $('#edit_kode_supplier').val(response.data.kode_supplier);
          $('#edit_nama_supplier').val(response.data.nama_supplier);
          $('#edit_kontak_person').val(response.data.kontak_person);
          $('#edit_telepon').val(response.data.telepon);
          $('#edit_email').val(response.data.email);
          $('#edit_alamat').val(response.data.alamat);
          $('#edit_kota').val(response.data.kota);
          $('#edit_provinsi').val(response.data.provinsi);
          $('#edit_status').val(response.data.status);
          $('#editSupplierModal').modal('show');
          setTimeout(() => {
            $("[name=nama_supplier]").focus().select();
          }, 500);
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: response.message
          }).then(function() {
            setTimeout(() => {
              table.ajax.reload();
            }, 500);
          });
        }
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
            if (response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: response.message
              }).then(function() {
                setTimeout(() => {
                  table.ajax.reload();
                }, 500);
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: response.message
              }).then(function() {
                setTimeout(() => {
                  table.ajax.reload();
                }, 500);
              });
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
    if ($('[name="kontak_person"]').val() == '') {
      $('[name="kontak_person"]').val('-');
    }
    if ($('[name="telepon"]').val() == '') {
      $('[name="telepon"]').val('-');
    }
    if ($('[name="kota"]').val() == '') {
      $('[name="kota"]').val('-');
    }
    if ($('[name="provinsi"]').val() == '') {
      $('[name="provinsi"]').val('-');
    }
    if ($('[name="alamat"]').val() == '') {
      $('[name="alamat"]').val('-');
    }
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
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: response.message
          }).then(function() {
            setTimeout(() => {
              $('#editSupplierModal').modal('hide');
              table.ajax.reload();
            }, 500);
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: response.message
          }).then(function() {
            setTimeout(() => {
              $(`[name=${response.form}]`).focus().select();
            }, 500);
          });
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
</body>
</html>