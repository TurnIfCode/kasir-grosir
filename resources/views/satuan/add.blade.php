@include('layout.header')
<div class="container-fluid">
  <h3 class="mb-4">Tambah Satuan</h3>
  <div class="card p-4">
    <form id="addSatuanForm" action="#" method="POST">
      @csrf
      <div class="mb-3">
        <label for="kode_satuan" class="form-label">Kode Satuan*</label>
        <input type="text" class="form-control" id="kode_satuan" name="kode_satuan" required>
      </div>
      <div class="mb-3">
        <label for="nama_satuan" class="form-label">Nama Satuan*</label>
        <input type="text" class="form-control" id="nama_satuan" name="nama_satuan" required>
      </div>
      <div class="mb-3">
        <label for="deskripsi" class="form-label">Deskripsi</label>
        <input type="text" class="form-control" id="deskripsi" name="deskripsi" placeholder="-">
      </div>
      <div class="mb-3">
        <label for="status" class="form-label">Status Aktif</label>
        <select class="form-control" id="status" name="status" required>
          <option value="AKTIF">AKTIF</option>
          <option value="NONAKTIF">NONAKTIF</option>
        </select>
      </div>
      <button type="submit" id="btnSave" class="btn btn-primary">Simpan</button>
    </form>
  </div>

<script>
$(document).ready(function() {
  $("#btnSave").click(function() {
    $('#addSatuanForm').validate({
      rules: {
        kode_satuan: {
          required: true,
          minlength: 3
        },
        nama_satuan: {
          required: true,
          minlength: 2
        }
      },
      messages: {
        kode_satuan: {
          required: "Kode Satuan wajib diisi",
          minlength: "Kode Satuan minimal 3 karakter"
        },
        nama_satuan: {
          required: "Nama Satuan wajib diisi",
          minlength: "Nama Satuan minimal 2 karakter"
        }
      },
      submitHandler: function(form) {
        // Set default deskripsi jika kosong
        if (!$('#deskripsi').val().trim()) {
          $('#deskripsi').val('-');
        }

        $.ajax({
          url: "{{ route('satuan.store') }}",
          type: "POST",
          data: $(form).serialize(),
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              setTimeout(() => {
                window.location.href = "{{ route('satuan.add') }}";
              }, 500);
            });
          },
          error: function(xhr) {
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan'
            });
          }
        });
      }
    });
  });
});
</script>


@include('layout.footer')
