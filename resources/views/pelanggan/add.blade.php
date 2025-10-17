@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Tambah Pelanggan</h3>
  <div class="card p-4">
    <form id="addPelangganForm" action="#" method="POST">
      @csrf

      <div class="row">
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="kode_pelanggan">Kode Pelanggan *</label>
            <input type="text" class="form-control" id="kode_pelanggan" name="kode_pelanggan">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="nama_pelanggan">Nama Pelanggan *</label>
            <input type="text" class="form-control" id="nama_pelanggan" name="nama_pelanggan">
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="telepon">Telepon</label>
            <input type="text" class="form-control" id="telepon" name="telepon">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email">
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="alamat">Alamat</label>
            <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="status">Status *</label>
            <select class="form-control" id="status" name="status">
              <option value="aktif">Aktif</option>
              <option value="non_aktif">Non Aktif</option>
            </select>
          </div>
        </div>
      </div>

      <button type="submit" id="btnSave" class="btn btn-primary">Simpan</button>
      <a href="{{ route('pelanggan.index') }}" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
  $("#btnSave").click(function() {
    $('#addPelangganForm').validate({
      rules: {
        kode_pelanggan: {
          required: true,
          minlength: 3
        },
        nama_pelanggan: {
          required: true,
          minlength: 3
        },
        status: {
          required: true
        }
      },
      messages: {
        kode_pelanggan: {
          required: "Kode Pelanggan wajib diisi",
          minlength: "Kode Pelanggan minimal 3 karakter"
        },
        nama_pelanggan: {
          required: "Nama Pelanggan wajib diisi",
          minlength: "Nama Pelanggan minimal 3 karakter"
        },
        status: {
          required: "Status wajib dipilih"
        }
      },
      submitHandler: function(form) {
        $.ajax({
          url: "{{ route('pelanggan.store') }}",
          type: "POST",
          data: $(form).serialize(),
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              setTimeout(() => {
                window.location.href = "{{ route('pelanggan.add') }}";
              }, 500);
            });
          },
          error: function(xhr) {
            var errors = xhr.responseJSON.errors;
            if (errors) {
              var errorMessage = '';
              for (var key in errors) {
                errorMessage += errors[key][0] + '\n';
              }
              Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: errorMessage
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan'
              });
            }
          }
        });
      }
    });
  });
});
</script>

@endsection

@include('layout.footer')
