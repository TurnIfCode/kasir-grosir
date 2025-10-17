@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Tambah Supplier</h3>
  <div class="card p-4">
    <form id="addSupplierForm" action="#" method="POST">
      @csrf

      <div class="row">
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="kode_supplier">Kode Supplier *</label>
            <input type="text" class="form-control" id="kode_supplier" name="kode_supplier">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="nama_supplier">Nama Supplier *</label>
            <input type="text" class="form-control" id="nama_supplier" name="nama_supplier">
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="kontak_person">Kontak Person</label>
            <input type="text" class="form-control" id="kontak_person" name="kontak_person">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="telepon">Telepon</label>
            <input type="text" class="form-control" id="telepon" name="telepon">
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="status">Status *</label>
            <select class="form-control" id="status" name="status">
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>
        </div>
      </div>

      <div class="form-group mb-3">
        <label for="alamat">Alamat</label>
        <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="kota">Kota</label>
            <input type="text" class="form-control" id="kota" name="kota">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="provinsi">Provinsi</label>
            <input type="text" class="form-control" id="provinsi" name="provinsi">
          </div>
        </div>
      </div>

      <button type="submit" id="btnSave" class="btn btn-primary">Simpan</button>
      <a href="{{ route('supplier.index') }}" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
  $("#btnSave").click(function() {
    $('#addSupplierForm').validate({
      rules: {
        kode_supplier: {
          required: true,
          minlength: 3
        },
        nama_supplier: {
          required: true,
          minlength: 3
        },
        status: {
          required: true
        }
      },
      messages: {
        kode_supplier: {
          required: "Kode Supplier wajib diisi",
          minlength: "Kode Supplier minimal 3 karakter"
        },
        nama_supplier: {
          required: "Nama Supplier wajib diisi",
          minlength: "Nama Supplier minimal 3 karakter"
        },
        status: {
          required: "Status wajib dipilih"
        }
      },
      submitHandler: function(form) {
        $.ajax({
          url: "{{ route('supplier.store') }}",
          type: "POST",
          data: $(form).serialize(),
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              setTimeout(() => {
                window.location.href = "{{ route('supplier.add') }}";
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
