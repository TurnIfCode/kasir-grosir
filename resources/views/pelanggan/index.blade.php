@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Data Pelanggan</h3>
  <div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
      <a href="{{ route('pelanggan.add') }}" class="btn btn-primary">Tambah Pelanggan</a>
    </div>
    <table id="pelangganTable" class="table table-striped">
      <thead>
        <tr>
          <th>Kode Pelanggan</th>
          <th>Nama Pelanggan</th>
          <th>Telepon</th>
          <th>Email</th>
          <th>Alamat</th>
          <th>Jenis</th>
          <th>Harga Tambah</th>
          <th>Status</th>
          <th>Dibuat Oleh</th>
          <th>Dibuat Tanggal</th>
          <th>Diubah Oleh</th>
          <th>Diubah Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal Edit Pelanggan -->
<div class="modal fade" id="editPelangganModal" tabindex="-1" aria-labelledby="editPelangganModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editPelangganModalLabel">Edit Pelanggan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editPelangganForm" action="#" method="POST">
          @csrf
          <input type="hidden" id="pelangganId" name="id">
          <div class="mb-3">
            <label for="edit_kode_pelanggan" class="form-label">Kode Pelanggan</label>
            <input type="text" class="form-control" id="edit_kode_pelanggan" name="kode_pelanggan" readonly>
          </div>
          <div class="mb-3">
            <label for="edit_nama_pelanggan" class="form-label">Nama Pelanggan *</label>
            <input type="text" class="form-control" id="edit_nama_pelanggan" name="nama_pelanggan">
          </div>
          <div class="mb-3">
            <label for="edit_telepon" class="form-label">Telepon</label>
            <input type="text" class="form-control" id="edit_telepon" name="telepon">
          </div>
          <div class="mb-3">
            <label for="edit_email" class="form-label">Email</label>
            <input type="email" class="form-control" id="edit_email" name="email">
          </div>
          <div class="mb-3">
            <label for="edit_alamat" class="form-label">Alamat</label>
            <textarea class="form-control" id="edit_alamat" name="alamat" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label for="edit_jenis" class="form-label">Jenis *</label>
            <select class="form-control" id="edit_jenis" name="jenis">
              <option value="normal">Normal</option>
              <option value="modal">Modal</option>
              <option value="antar">Antar</option>
            </select>
          </div>
          <div class="mb-3" style="display: none;">
            <label for="edit_ongkos" class="form-label">Harga Tambah *</label>
            <input type="number" class="form-control" id="edit_ongkos" name="ongkos" value="0">
          </div>
          <div class="mb-3">
            <label for="edit_status" class="form-label">Status *</label>
            <select class="form-control" id="edit_status" name="status">
              <option value="aktif">Aktif</option>
              <option value="non_aktif">Non Aktif</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnUpdatePelanggan">Simpan</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail Pelanggan -->
<div class="modal fade" id="detailPelangganModal" tabindex="-1" aria-labelledby="detailPelangganModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailPelangganModalLabel">Detail Pelanggan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tbody id="pelangganDetailBody">
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

@include('layout.footer')

<script>
$(document).ready(function() {
  function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
      prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
      sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
      dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
      s = '',
      toFixedFix = function (n, prec) {
        var k = Math.pow(10, prec);
        return '' + Math.round(n * k) / k;
      };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
      s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
      s[1] = s[1] || '';
      s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
  }

  // DataTable
  var table = $('#pelangganTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '{{ route("pelanggan.data") }}',
      type: 'GET'
    },
    columns: [
      { data: 'kode_pelanggan', name: 'kode_pelanggan' },
      { data: 'nama_pelanggan', name: 'nama_pelanggan' },
      { data: 'telepon', name: 'telepon' },
      { data: 'email', name: 'email' },
      { data: 'alamat', name: 'alamat' },
      { data: 'jenis', name: 'jenis' },
      { data: 'ongkos', name: 'ongkos' },
      { data: 'status', name: 'status' },
      { data: 'created_by', name: 'created_by' },
      { data: 'created_at', name: 'created_at' },
      { data: 'updated_by', name: 'updated_by' },
      { data: 'updated_at', name: 'updated_at' },
      { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
    ],
    order: [[0, 'asc']]
  });

  // Form validation
  var validator = $('#editPelangganForm').validate({
    rules: {
      kode_pelanggan: { required: true },
      nama_pelanggan: { required: true },
      status: { required: true }
    },
    messages: {
      kode_pelanggan: { required: 'Kode Pelanggan wajib diisi' },
      nama_pelanggan: { required: 'Nama Pelanggan wajib diisi' },
      status: { required: 'Status wajib dipilih' }
    },
    errorElement: 'div',
    errorClass: 'invalid-feedback',
    highlight: function(element) {
      $(element).addClass('is-invalid');
    },
    unhighlight: function(element) {
      $(element).removeClass('is-invalid');
    }
  });

  // Edit handler
  $(document).on('click', '#btnEdit', function() {
    var pelangganId = $(this).data('id');
    $.ajax({
      url: '{{ route("pelanggan.find", ":id") }}'.replace(':id', pelangganId),
      type: 'GET',
      success: function(response) {
        if (response.success) {
          $('#pelangganId').val(response.data.id);
          $('#edit_kode_pelanggan').val(response.data.kode_pelanggan);
          $('#edit_nama_pelanggan').val(response.data.nama_pelanggan);
          $('#edit_telepon').val(response.data.telepon);
          if (response.data.email === '-') {
            $('#edit_email').val('');
          } else {
            $('#edit_email').val(response.data.email);
          }
          $('#edit_alamat').val(response.data.alamat);
          $('#edit_jenis').val(response.data.jenis);
          $('#edit_ongkos').val(response.data.ongkos);
          $('#edit_status').val(response.data.status);

          // Trigger change to show/hide ongkos
          $('#edit_jenis').trigger('change');

          $('#editPelangganModal').modal('show');
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: response.message
          }).then(function() {
            setTimeout(() => {
              table.ajax.reload();
            }, 500);
          });
        }
        
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
      }
    });
  });

  // Detail handler
  $(document).on('click', '#btnDetail', function() {
    var pelangganId = $(this).data('id');
    $.ajax({
      url: '{{ route("pelanggan.find", ":id") }}'.replace(':id', pelangganId),
      type: 'GET',
      success: function(response) {

        var detailHtml = '';
        if (response.success) {
          // Format data untuk tabel detail
          detailHtml += '<tr><td><strong>Kode Pelanggan</strong></td><td>' + (response.data.kode_pelanggan || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Nama Pelanggan</strong></td><td>' + (response.data.nama_pelanggan || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Telepon</strong></td><td>' + (response.data.telepon || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Email</strong></td><td>' + (response.data.email || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Alamat</strong></td><td>' + (response.data.alamat || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Jenis</strong></td><td>' + (response.data.jenis || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Harga Tambah</strong></td><td>' + (response.data.ongkos ? number_format(response.data.ongkos, 0, ',', '.') : '0') + '</td></tr>';
          detailHtml += '<tr><td><strong>Status</strong></td><td>' + (response.data.status || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Dibuat Oleh</strong></td><td>' + (response.data.creator.name || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Tanggal Dibuat</strong></td><td>' + (response.data.created_at ? new Date(response.data.created_at).toLocaleString('id-ID') : '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Diubah Oleh</strong></td><td>' + (response.data.updater.name || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Tanggal Diubah</strong></td><td>' + (response.data.updated_at ? new Date(response.data.updated_at).toLocaleString('id-ID') : '-') + '</td></tr>';

          $('#pelangganDetailBody').html(detailHtml);
          $('#detailPelangganModal').modal('show');
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: response.message
          }).then(function() {
            setTimeout(() => {
              table.ajax.reload();
            }, 500);
          });
        }
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
      }
    });
  });

  // Delete handler
  $(document).on('click', '#btnDelete', function() {
    var pelangganId = $(this).data('id');
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
          url: '{{ route("pelanggan.delete", ":id") }}'.replace(':id', pelangganId),
          type: 'DELETE',
          data: { _token: '{{ csrf_token() }}' },
          success: function(response) {
            if (response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: response.message
              }).then(function() {
                setTimeout(() => {
                  table.ajax.reload();
                }, 500);
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: response.message
              }).then(function() {
                setTimeout(() => {
                  table.ajax.reload();
                }, 500);
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

  // Update button handler
  $('#btnUpdatePelanggan').on('click', function(e) {
    e.preventDefault();
    if ($('#editPelangganForm').valid()) {
      $('#editPelangganForm').submit();
    } else {
      validator.focusInvalid();
    }
  });

  $('#edit_jenis').change(function() {
    if ($(this).val() === 'antar') {
      $('div.mb-3:has(#edit_ongkos)').show();
    } else {
      $('div.mb-3:has(#edit_ongkos)').hide();
    }
  });

  // Submit form handler
  $('#editPelangganForm').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    formData.append('_method', 'PUT');
    $.ajax({
      url: '{{ route("pelanggan.update", ":id") }}'.replace(':id', $('#pelangganId').val()),
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        if (response.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: response.message
          }).then(function() {
            setTimeout(() => {
              $('#editPelangganModal').modal('hide');
              table.ajax.reload();
            }, 500);
          });
          // Swal.fire('Berhasil!', response.message, 'success');
          
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
        }
      },
      error: function(xhr) {
        var errors = xhr.responseJSON.errors;
        if (errors) {
          var errorMessage = '';
          for (var key in errors) {
            errorMessage += errors[key][0] + '\n';
          }
          Swal.fire({ icon: 'error', title: 'Validation Error', text: errorMessage });
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
        }
      }
    });
  });
});
</script>
</body>
</html>