@include('layout.header')
<div class="container-fluid">
  <h3 class="mb-4">Tambah</h3>
  <div class="card p-4">
    <form id="addUserForm" action="#" method="POST">
      @csrf
      <div class="mb-3">
        <label for="username" class="form-label">Username*</label>
        <input type="input" class="form-control" id="username" name="username">
      </div>
      <div class="mb-3">
        <label for="name" class="form-label">Nama Lengkap*</label>
        <input type="input" class="form-control" id="name" name="name">
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password*</label>
        <input type="password" class="form-control" id="password" name="password">
      </div>
      <div class="mb-3">
        <label for="role" class="form-label">Role</label>
        <select class="form-control" id="role" name="role">
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
      <button type="submit" id="btnSave" class="btn btn-primary">Simpan</button>
    </form>
  </div>

@section('scripts')
<script>
$(document).ready(function() {
  $("#btnSave").click(function() {
    $('#addUserForm').validate({
      rules: {
        username: {
          required: true,
          minlength: 3
        },
        name: {
          required: true,
          minlength: 3
        },
        password: {
          required: true,
          minlength: 6
        }
      },
      messages: {
        username: {
          required: "Username wajib diisi",
          minlength: "Username minimal 3 karakter"
        },
        name: {
          required: "Nama lengkap wajib diisi",
          minlength: "Nama lengkap minimal 3 karakter"
        },
        password: {
          required: "Password wajib diisi",
          minlength: "Password minimal 6 karakter"
        }
      },
      submitHandler: function(form) {
        $.ajax({
          url: "{{ route('user.store') }}",
          type: "POST",
          data: $(form).serialize(),
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              setTimeout(() => {
                window.location.href = "{{ route('user.add') }}";
              }, 500);
            });
          },
          error: function(xhr) {
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: response.message
            });
          }
        });
      }
    });
  });
});
</script>

@endsection

@include('layout.footer')
