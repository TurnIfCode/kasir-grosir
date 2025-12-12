@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Data Satuan</h3>
  <div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
      <h5>Data Satuan</h5>
      <a href="{{ route('satuan.add') }}" class="btn btn-primary">Tambah Satuan</a>
    </div>
    <table id="satuanTable" class="table table-striped">
      <thead>
        <tr>
          <th>Kode Satuan</th>
          <th>Nama Satuan</th>
          <th>Deskripsi</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal Detail Satuan -->
<div class="modal fade" id="detailSatuanModal" tabindex="-1" aria-labelledby="detailSatuanModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailSatuanModalLabel">Detail Satuan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tbody id="satuanDetailBody">
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

<!-- Modal Edit Satuan -->
<div class="modal fade" id="editSatuanModal" tabindex="-1" aria-labelledby="editSatuanModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editSatuanModalLabel">Edit Satuan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editSatuanForm" action="#" method="POST">
          @csrf
          <input type="hidden" id="satuanId" name="id">
          <div class="mb-3">
            <label for="edit_kode_satuan" class="form-label">Kode Satuan*</label>
            <input type="text" class="form-control" id="edit_kode_satuan" name="kode_satuan" readonly>
          </div>
          <div class="mb-3">
            <label for="edit_nama_satuan" class="form-label">Nama Satuan*</label>
            <input type="text" class="form-control" id="edit_nama_satuan" name="nama_satuan" required>
          </div>
          <div class="mb-3">
            <label for="edit_deskripsi" class="form-label">Deskripsi</label>
            <input type="text" class="form-control" id="edit_deskripsi" name="deskripsi" placeholder="-">
          </div>
          <div class="mb-3">
            <label for="edit_status" class="form-label">Status Aktif</label>
            <select class="form-control" id="edit_status" name="status" required>
              <option value="aktif">AKTIF</option>
              <option value="nonaktif">NONAKTIF</option>
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

@include('layout.footer')

<script>
$(document).ready(function() {
  // DataTable
  var table = $('#satuanTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route("satuan.data") }}',
    columns: [
      { data: 'kode_satuan' },
      { data: 'nama_satuan' },
      { data: 'deskripsi' },
      { data: 'status' },
      { data: 'aksi', orderable: false, searchable: false }
    ]
  });

  // Detail handler
  $(document).on('click', '#btnDetail', function() {
    var satuanId = $(this).data('id');

    $.ajax({
      url: '/satuan/' + satuanId + '/find',
      type: 'GET',
      success: function(response) {
        if (response.success) {
          var satuan = response.data;
          var detailHtml = '';

          // Format data untuk tabel detail
          detailHtml += '<tr><td><strong>Kode Satuan</strong></td><td>' + (satuan.kode_satuan || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Nama Satuan</strong></td><td>' + (satuan.nama_satuan || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Deskripsi</strong></td><td>' + (satuan.deskripsi || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Status</strong></td><td>' + (satuan.status || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Dibuat Oleh</strong></td><td>' + (satuan.created_by || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Dibuat Pada</strong></td><td>' + (satuan.created_at || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Diubah Oleh</strong></td><td>' + (satuan.updated_by || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Diubah Pada</strong></td><td>' + (satuan.updated_at || '-') + '</td></tr>';

          $('#satuanDetailBody').html(detailHtml);
          $('#detailSatuanModal').modal('show');
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
        }
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
      }
    });
  });

  // Klik Edit
  $(document).on('click', '#btnEdit', function() {
    var satuanId = $(this).data('id');
    $('#satuanId').val(satuanId);

    $.ajax({
      url: '/satuan/' + satuanId + '/find',
      type: 'GET',
      success: function(response) {
        if (response.success) {
          var satuan = response.data;
          $('#edit_kode_satuan').val(satuan.kode_satuan);
          $('#edit_nama_satuan').val(satuan.nama_satuan);
          $('#edit_deskripsi').val(satuan.deskripsi);
          $('#edit_status').val(satuan.status);
          validator.resetForm();
          $('#editSatuanForm').find('.is-invalid').removeClass('is-invalid');
          $('#editSatuanModal').modal('show');
          setTimeout(() => {
            $("[name=nama_satuan]").focus().select();
          }, 500);
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
        }
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
      }
    });
  });

  // Inisialisasi validator
  var validator = $('#editSatuanForm').validate({
    rules: {
      nama_satuan: { required: true, minlength: 3 },
      status: { required: true }
    },
    messages: {
      nama_satuan: {
        required: "Nama Satuan wajib diisi",
        minlength: "Nama Satuan minimal 3 karakter"
      },
      status: { required: "Status wajib dipilih" }
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
      // Set default deskripsi jika kosong
      if (!$('#edit_deskripsi').val().trim()) {
        $('#edit_deskripsi').val('-');
      }

      var satuanId = $('#satuanId').val();
      if (!satuanId) {
        console.error('satuanId kosong');
        Swal.fire('Error', 'Satuan ID tidak ditemukan. Coba lagi.', 'error');
        return;
      }

      var $btn = $('#btnUpdate');
      $btn.prop('disabled', true).text('Menyimpan...');

      $.ajax({
        url: '/satuan/' + satuanId + '/update',
        type: 'POST',
        data: $(form).serialize() + '&_method=PUT',
        success: function(response) {
          if (response.success) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              $('#editSatuanModal').modal('hide');
              table.ajax.reload();
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: response.message
            }).then(function() {
              setTimeout(() => {
                if (response.form == 'reload') {
                  table.ajax.reload();
                } else {
                  $(`[name=${response.form}]`).focus().select();
                }
              }, 500);
            });
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

  

  // Delete
  $(document).on('click', '#btnDelete', function() {
    var satuanId = $(this).data('id');
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
          url: '/satuan/' + satuanId + '/delete',
          type: 'DELETE',
          data: { _token: '{{ csrf_token() }}' },
          success: function(response) {
            if (response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: response.message
              }).then(function() {
                table.ajax.reload();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: response.message
              }).then(function() {
                table.ajax.reload();
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

  // Tombol Update
  $('#btnUpdate').on('click', function(e) {
    e.preventDefault();
    if ($('#editSatuanForm').valid()) {
      $('#editSatuanForm').submit();
    } else {
      validator.focusInvalid();
    }
  });

});
</script>
</body>
</html>