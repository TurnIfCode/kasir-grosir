@include('layout.header')
<div class="container-fluid">
  <h3 class="mb-4">Tambah</h3>
  <div class="card p-4">
    <form id="addKategoriForm" action="#" method="POST">
      @csrf
      <div class="mb-3">
        <label for="kode_kategori" class="form-label">Kode Kategori*</label>
        <input type="input" class="form-control" id="kode_kategori" name="kode_kategori" required>
      </div>
      <div class="mb-3">
        <label for="nama_kategori" class="form-label">Kategori*</label>
        <input type="input" class="form-control" id="nama_kategori" name="nama_kategori" required>
      </div>
      <div class="mb-3">
        <label for="deskripsi">Deksripsi</label>
        <textarea name="deskripsi" id="deskripsi" class="form-control" placeholder="-"></textarea>
      </div>
      <div class="mb-3">
        <label for="status" class="form-label">Status Aktif</label>
        <select class="form-control" id="status" name="status" required>
          <option value="aktif">AKTIF</option>
          <option value="nonaktif">TIDAK AKTIF</option>
        </select>
      </div>
      <button type="submit" id="btnSave" class="btn btn-primary">Simpan</button>
    </form>
  </div>

@section('scripts')
<script>
$(document).ready(function() {
  $("#btnSave").click(function() {
    $('#addKategoriForm').validate({
      rules: {
        kode_kategori: {
          required: true,
          minlength: 3
        },
        nama_kategori: {
          required: true,
          minlength: 3
        }
      },
      messages: {
        kode_kategori: {
          required: "Kode kategori wajib diisi",
          minlength: "Kode kategori minimal 3 karakter"
        },
        nama_kategori: {
          required: "Kategori wajib diisi",
          minlength: "Kategori minimal 3 karakter"
        }
      },
      submitHandler: function(form) {
        // Set default deskripsi jika kosong
        if (!$('#deskripsi').val().trim()) {
          $('#deskripsi').val('-');
        }

        $.ajax({
          url: "{{ route('kategori.store') }}",
          type: "POST",
          data: $(form).serialize(),
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              setTimeout(() => {
                window.location.href = "{{ route('kategori.add') }}";
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
