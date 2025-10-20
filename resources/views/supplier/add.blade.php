@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Tambah Supplier</h3>
  <div class="card p-4">
    <form id="addSupplierForm" action="#" method="POST">
      @csrf

      <div class="form-group mb-3">
        <label for="kode_supplier">Kode Supplier</label>
        <input type="text" class="form-control" id="kode_supplier" name="kode_supplier" readonly>
        <small class="form-text text-muted">Kode supplier akan di-generate otomatis</small>
      </div>

      <div class="form-group mb-3">
        <label for="nama_supplier">Nama Supplier*</label>
        <input type="text" class="form-control" id="nama_supplier" name="nama_supplier">
      </div>

      <div class="form-group mb-3">
        <label for="kontak_person">Kontak</label>
        <input type="text" class="form-control" id="kontak_person" name="kontak_person">
      </div>

      <div class="form-group mb-3">
        <label for="telepon">Telepon</label>
        <input type="text" class="form-control" id="telepon" name="telepon">
      </div>

      <div class="form-group mb-3">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email">
      </div>

      <div class="form-group mb-3">
        <label for="alamat">Alamat</label>
        <textarea class="form-control" id="alamat" name="alamat" rows="3">-</textarea>
      </div>

      <div class="form-group mb-3">
        <label for="kota">Kota</label>
        <input type="text" class="form-control" id="kota" name="kota">
      </div>

      <div class="form-group mb-3">
        <label for="provinsi">Provinsi</label>
        <input type="text" class="form-control" id="provinsi" name="provinsi">
      </div>

      <div class="form-group mb-3">
        <label for="status">Status *</label>
        <select class="form-control" id="status" name="status">
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Nonaktif</option>
        </select>
      </div>

      <button type="submit" id="btnSave" class="btn btn-primary">Simpan</button>
      <a href="{{ route('supplier.index') }}" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
  // Generate kode supplier otomatis saat halaman load
  $.ajax({
    url: "{{ route('supplier.generate-kode') }}",
    type: "GET",
    success: function(response) {
      $('#kode_supplier').val(response.kode_supplier);
    }
  });
  $("#btnSave").click(function() {
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
    $('#addSupplierForm').validate({
      rules: {
        nama_supplier: {
          required: true,
          minlength: 3
        },
        status: {
          required: true
        }
      },
      messages: {
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
