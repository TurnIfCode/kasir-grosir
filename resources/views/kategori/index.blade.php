@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Data Kategori</h3>
  <div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
      <h5>Data Kategori</h5>
      <a href="{{ route('kategori.add') }}" class="btn btn-primary">Tambah Kategori</a>
    </div>
    <table id="kategoriTable" class="table table-striped">
      <thead>
        <tr>
          <th>Kode Kategori</th>
          <th>Nama Kategori</th>
          <th>Deskripsi</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal Edit Kategori -->
<div class="modal fade" id="editKategoriModal" tabindex="-1" aria-labelledby="editKategoriModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editKategoriModalLabel">Ubah Kategori</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="updateKategoriForm" action="#" method="POST">
          @csrf
          <input type="hidden" id="kategoriId" name="id">
          <div class="mb-3">
            <label for="kode_kategori" class="form-label">Kode Kategori*</label>
            <input type="text" class="form-control" id="editKodeKategori" name="kode_kategori" required>
          </div>
          <div class="mb-3">
            <label for="nama_kategori" class="form-label">Nama Kategori*</label>
            <input type="text" class="form-control" id="editNamaKategori" name="nama_kategori" required>
          </div>
          <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <input type="text" class="form-control" id="editDeskripsi" name="deskripsi">
          </div>
          <div class="mb-3">
            <label for="status" class="form-label">Status Aktif</label>
            <select class="form-control" id="editStatus" name="status" required>
              <option value="AKTIF">AKTIF</option>
              <option value="NONAKTIF">NONAKTIF</option>
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
  var table = $('#kategoriTable').DataTable({
    processing: true,
    serverSide: true,
      ajax: '{{ route("kategori.data") }}',
    columns: [
      { data: 'kode_kategori', name: 'kode_kategori' },
      { data: 'nama_kategori', name: 'nama_kategori' },
      { data: 'deskripsi', name: 'deskripsi' },
      { data: 'status', name: 'status' },
      { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
    ]
  });

  // Inisialisasi validator
  var validator = $('#updateKategoriForm').validate({
    rules: {
      kode_kategori: { required: true, minlength: 3 },
      nama_kategori: { required: true, minlength: 3 },
      status: { required: true }
    },
    messages: {
      kode_kategori: {
        required: "Kode Kategori wajib diisi",
        minlength: "Kode Kategori minimal 3 karakter"
      },
      nama_kategori: {
        required: "Nama Kategori wajib diisi",
        minlength: "Nama Kategori minimal 3 karakter"
      },
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
      var kategoriId = $('#kategoriId').val();
      if (!kategoriId) {
        Swal.fire('Error', 'Kategori ID tidak ditemukan. Coba lagi.', 'error');
        return;
      }

      var $btn = $('#btnUpdate');
      $btn.prop('disabled', true).text('Menyimpan...');

      $.ajax({
        url: '{{ route("kategori.update", ":id") }}'.replace(':id', kategoriId),
        type: 'POST',
        data: $(form).serialize() + '&_method=PUT',
        success: function(response) {
          if (response.status) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              $('#editKategoriModal').modal('hide');
              table.ajax.reload();
            });
          } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
          }
        },
        error: function(xhr) {
          Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan pada server.' });
        },
        complete: function() {
          $btn.prop('disabled', false).text('Simpan');
        }
      });
    }
  });

  // Edit handler
  $(document).on('click', '#btnEdit', function() {
    var kategoriId = $(this).data('id');
    $('#kategoriId').val(kategoriId);

    $.ajax({
      url: '{{ route("kategori.find", ":id") }}'.replace(':id', kategoriId),
      type: 'GET',
      success: function(response) {
        if (response.status) {
          var kategori = response.data;
          $('#editKodeKategori').val(kategori.kode_kategori);
          $('#editNamaKategori').val(kategori.nama_kategori);
          $('#editDeskripsi').val(kategori.deskripsi);
          $('#editStatus').val(kategori.status);
          validator.resetForm();
          $('#updateKategoriForm').find('.is-invalid').removeClass('is-invalid');
          $('#editKategoriModal').modal('show');
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
        }
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
      }
    });
  });

  // Delete handler
  $(document).on('click', '#btnDelete', function() {
    var kategoriId = $(this).data('id');
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
          url: '{{ route("kategori.delete", ":id") }}'.replace(':id', kategoriId),
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

  // Update button
  $('#btnUpdate').on('click', function(e) {
    e.preventDefault();
    if ($('#updateKategoriForm').valid()) {
      $('#updateKategoriForm').submit();
    } else {
      validator.focusInvalid();
    }
  });
});
</script>
@endsection

@include('layout.footer')
