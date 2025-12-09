@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Tambah Pelanggan</h3>
  <div class="card p-4">
    <form id="addPelangganForm" action="#" method="POST">
      @csrf

      <div class="form-group mb-3">
        <label for="kode_pelanggan">Kode Pelanggan</label>
        <input type="text" class="form-control" id="kode_pelanggan" name="kode_pelanggan" readonly>
        <small class="form-text text-muted">Kode pelanggan akan di-generate otomatis</small>
      </div>

      <div class="form-group mb-3">
        <label for="nama_pelanggan">Nama Pelanggan*</label>
        <input type="text" class="form-control" id="nama_pelanggan" name="nama_pelanggan">
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
        <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
      </div>

      <div class="form-group mb-3">
        <label for="jenis">Jenis*</label>
        <select class="form-control" id="jenis" name="jenis">
          <option value="normal">Normal</option>
          <option value="modal">Modal</option>
          <option value="antar">Antar</option>
        </select>
      </div>

      <div class="form-group mb-3" style="display: none;">
        <label for="ongkos">Harga Tambah*</label>
        <input type="number" class="form-control" id="ongkos" name="ongkos" value="0">
      </div>

      <div class="form-group mb-3">
        <label for="status">Status*</label>
        <select class="form-control" id="status" name="status">
          <option value="aktif">Aktif</option>
          <option value="non_aktif">Non Aktif</option>
        </select>
      </div>

      <button type="submit" id="btnSave" class="btn btn-primary">Simpan</button>
      <a href="{{ route('pelanggan.index') }}" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>

<script>
$(document).ready(function() {
  $('[name=nama_pelanggan]').focus().select();
  // Generate kode pelanggan otomatis saat halaman load
  $.ajax({
    url: "{{ route('pelanggan.generate-kode') }}",
    type: "GET",
    success: function(response) {
      $('#kode_pelanggan').val(response.kode_pelanggan);
    }
  });
  $("#btnSave").click(function() {
    $('#addPelangganForm').validate({
      rules: {
        nama_pelanggan: {
          required: true,
          minlength: 3
        },
        status: {
          required: true
        }
      },
      messages: {
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
            if (response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: response.message
              }).then(function() {
                setTimeout(() => {
                  window.location.href = "{{ route('pelanggan.add') }}";
                }, 500);
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: response.message
              }).then(function() {
                setTimeout(() => {
                  $(`#${response.form}`).focus().select();
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

  // Show/Hide Harga Tambah based on jenis selection
  $('#jenis').change(function() {
    if ($(this).val() === 'antar') {
      $('div.form-group:has(#ongkos)').show();
    } else {
      $('div.form-group:has(#ongkos)').hide();
    }
  });
});
</script>


@include('layout.footer')
