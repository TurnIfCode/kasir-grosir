@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Data</h3>
  <div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
      <h5>Data</h5>
    </div>
    <table id="userTable" class="table table-striped">
      <thead>
        <tr>
          <th>Username</th>
          <th>Nama Lengkap</th>
          <th>Role</th>
          <th>Status Aktif</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal Detail User -->
<div class="modal fade" id="detailUserModal" tabindex="-1" aria-labelledby="detailUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailUserModalLabel">Detail User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tbody id="userDetailBody">
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

<!-- Modal Tambah User -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="updateUserModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateUserModal">Ubah User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="updateUserForm" action="#" method="POST">
          @csrf
          <input type="hidden" id="userId" name="id">
          <div class="mb-3">
            <label for="username" class="form-label">Username*</label>
            <input type="text" class="form-control" readonly id="username" name="username">
          </div>
          <div class="mb-3">
            <label for="name" class="form-label">Nama Lengkap*</label>
            <input type="text" class="form-control" id="name" name="name" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password">
          </div>
          <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-control" id="role" name="role" required>
              <option value="OWNER">OWNER</option>
              <option value="ADMIN">ADMIN</option>
              <option value="KASIR">KASIR</option>
              <option value="GUDANG">GUDANG</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="status" class="form-label">Status Aktif</label>
            <select class="form-control" id="status" name="status" required>
              <option value="AKTIF">AKTIF</option>
              <option value="TIDAK AKTIF">TIDAK AKTIF</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="btnUpdate" class="btn btn-primary">Simpan</button>
      </div>
    </div>
  </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
  // DataTable
  var table = $('#userTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route("user.data") }}',
    columns: [
      { data: 'username' },
      { data: 'name' },
      { data: 'role' },
      { data: 'status' },
      { data: 'aksi', orderable: false, searchable: false }
    ]
  });

  // Inisialisasi validator SEKALI saja
  var validator = $('#updateUserForm').validate({
    rules: {
      username: { required: true, minlength: 3 },
      name: { required: true },
      role: { required: true },
      status: { required: true }
    },
    messages: {
      username: {
        required: "Username wajib diisi",
        minlength: "Username minimal 3 karakter"
      },
      name: { required: "Nama Lengkap wajib diisi" },
      role: { required: "Role wajib dipilih" },
      status: { required: "Status Aktif wajib dipilih" }
    },
    errorElement: 'div',
    errorClass: 'invalid-feedback d-block',
    highlight: function(element) {
      $(element).addClass('is-invalid');
    },
    unhighlight: function(element) {
      $(element).removeClass('is-invalid');
    },
    submitHandler: function(form) {
      // Ambil userId dari hidden input (pastikan sudah di-set ketika klik edit)
      var userId = $('#userId').val();
      if (!userId) {
        console.error('userId kosong - pastikan btnEdit menyimpan id ke #userId');
        Swal.fire('Error', 'User ID tidak ditemukan. Coba lagi.', 'error');
        return;
      }

      var $btn = $('#btnUpdate');
      $btn.prop('disabled', true).text('Menyimpan...');

      // Laravel: gunakan POST + override method PUT
      $.ajax({
        url: '/user/' + userId + '/update',
        type: 'POST',
        data: $(form).serialize() + '&_method=PUT', // method override
        success: function(response) {
          if (response.status) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              $('#addUserModal').modal('hide');
              table.ajax.reload();
            });
          } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
          }
        },
        error: function(xhr) {
          console.error('AJAX error:', xhr.responseText);
          Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan pada server.' });
        },
        complete: function() {
          $btn.prop('disabled', false).text('Simpan');
        }
      });
    }
  });

  // Ketika klik tombol Detail di table -> tampilkan modal detail
  $(document).on('click', '#btnDetail', function() {
    var userId = $(this).data('id');

    $.ajax({
      url: '/user/' + userId + '/find',
      type: 'GET',
      success: function(response) {
        if (response.status) {
          var user = response.data;
          var detailHtml = '';

          // Format data untuk tabel detail
          detailHtml += '<tr><td><strong>Username</strong></td><td>' + (user.username || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Nama Lengkap</strong></td><td>' + (user.name || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Role</strong></td><td>' + (user.role || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Status</strong></td><td>' + (user.status || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Dibuat Oleh</strong></td><td>' + (user.created_by || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Dibuat Pada</strong></td><td>' + (user.created_at || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Diubah Oleh</strong></td><td>' + (user.updated_by || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Diubah Pada</strong></td><td>' + (user.updated_at || '-') + '</td></tr>';

          $('#userDetailBody').html(detailHtml);
          $('#detailUserModal').modal('show');
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
        }
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
      }
    });
  });

  // Ketika klik tombol Edit di table -> isi form + simpan userId
  $(document).on('click', '#btnEdit', function() {
    var userId = $(this).data('id');
    // simpan juga ke tombol update (opsional) dan ke hidden input
    $('#btnUpdate').data('id', userId);
    $('#userId').val(userId);

    $.ajax({
      url: '/user/' + userId + '/find',
      type: 'GET',
      success: function(response) {
        if (response.status) {
          var user = response.data;
          $('#username').val(user.username);
          $('#name').val(user.name);
          $('#password').val('');
          $('#role').val(user.role);
          $('#status').val(user.status);
          // reset validation state setiap buka modal
          validator.resetForm();
          $('#updateUserForm').find('.is-invalid').removeClass('is-invalid');
          $('#updateUserModal').text('Ubah User');
          $('#addUserModal').modal('show');
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
        }
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
      }
    });
  });

  // Delete handler tetap sama (biarkan jika sudah jalan)
  $(document).on('click', '#btnDelete', function() {
    var userId = $(this).data('id');
    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: "Data yang dihapus tidak dapat dikembalikan!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, hapus!',
      cancelButtontText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: '/user/' + userId + '/delete',
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

  // Tombol Update -> trigger validasi/submit sekali saja
  $('#btnUpdate').on('click', function(e) {
    e.preventDefault();
    // fokuskan validasi -> jika valid, submitHandler akan terpanggil
    if ($('#updateUserForm').valid()) {
      $('#updateUserForm').submit();
    } else {
      validator.focusInvalid();
    }
  });

});
</script>
@endsection

@include('layout.footer')
