@include('layout.header')
<div class="container-fluid">
  <h3 class="mb-4">Data Konversi Satuan</h3>
  <div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
      <a href="{{ route('konversi-satuan.add') }}" class="btn btn-primary">Tambah Konversi Satuan</a>
    </div>
    <table id="konversiTable" class="table table-striped">
      <thead>
        <tr>
          <th>No</th>
          <th>Barang</th>
          <th>Satuan Dasar</th>
          <th>Satuan Konversi</th>
          <th>Nilai Konversi</th>
          <th>Harga Beli</th>
          <th>Harga Jual</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal Detail Konversi Satuan -->
<div class="modal fade" id="detailKonversiModal" tabindex="-1" aria-labelledby="detailKonversiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailKonversiModalLabel">Detail Konversi Satuan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tbody id="konversiDetailBody">
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

<!-- Modal Edit Konversi Satuan -->
<div class="modal fade" id="editKonversiModal" tabindex="-1" aria-labelledby="editKonversiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editKonversiModalLabel">Edit Konversi Satuan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editKonversiForm" action="#" method="POST">
          @csrf
          <input type="hidden" id="konversiId" name="id">

          <div class="form-group">
            <label for="edit_barang_nama">Pilih Barang</label>
            <input type="text" class="form-control barang-autocomplete" id="edit_barang_nama" placeholder="Ketik nama, kode barang atau scan barcode" required>
            <input type="hidden" name="barang_id" id="edit_barang_id" required>
          </div>

          <div class="form-group">
            <label for="edit_satuan_dasar_id">Satuan Dasar</label>
            <select name="satuan_dasar_id" id="edit_satuan_dasar_id" class="form-control" required>
              <option value="">-- Pilih Satuan Dasar --</option>
              @if(isset($satuan))
                @foreach($satuan as $s)
                  <option value="{{ $s->id }}">{{ $s->nama_satuan }}</option>
                @endforeach
              @endif
            </select>
          </div>

          <div class="form-group">
            <label for="edit_satuan_konversi_id">Satuan Konversi</label>
            <select name="satuan_konversi_id" id="edit_satuan_konversi_id" class="form-control" required>
              <option value="">-- Pilih Satuan Konversi --</option>
              @if(isset($satuan))
                @foreach($satuan as $s)
                  <option value="{{ $s->id }}">{{ $s->nama_satuan }}</option>
                @endforeach
              @endif
            </select>
          </div>

          <div class="form-group">
            <label for="edit_nilai_konversi">Nilai Konversi</label>
            <input type="number" step="0.01" name="nilai_konversi" id="edit_nilai_konversi"
                   class="form-control" placeholder="contoh: 12 (1 pack = 12 bungkus)" required>
          </div>

          <div class="form-group">
            <label for="edit_harga_beli">Harga Beli Satuan Konversi</label>
            <input type="number" step="0.01" name="harga_beli" id="edit_harga_beli"
                   class="form-control" placeholder="contoh: 324000">
          </div>

          <div class="form-group">
            <label for="edit_harga_jual">Harga Jual Satuan Konversi</label>
            <input type="number" step="0.01" name="harga_jual" id="edit_harga_jual"
                   class="form-control" placeholder="contoh: 384000">
          </div>

          <div class="form-group">
            <label for="edit_status">Status</label>
            <select name="status" id="edit_status" class="form-control">
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="btnUpdateKonversi" class="btn btn-primary">Simpan</button>
      </div>
    </div>
  </div>
</div>

<script>
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

$(document).ready(function() {
  // Autocomplete barang for edit modal
  $(document).on('focus', '.barang-autocomplete', function() {
    $(this).autocomplete({
      source: function(request, response) {
        $.ajax({
          url: '{{ route("barang.search") }}',
          dataType: 'json',
          data: { q: request.term },
          success: function(data) {
            if (data.status === 'success') {
              response($.map(data.data, function(item) {
                return {
                  label: item.kode_barang + ' - ' + item.nama_barang,
                  value: item.nama_barang,
                  id: item.id
                };
              }));
            }
          }
        });
      },
      minLength: 2,
      select: function(event, ui) {
        var row = $(this).closest('.modal-body');
        row.find('#edit_barang_id').val(ui.item.id);
        row.find('#edit_barang_nama').val(ui.item.value);
        return false;
      }
    });
  });

  // DataTable
  var table = $('#konversiTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '{{ route("konversi-satuan.data") }}',
      type: 'GET'
    },
    columns: [
      { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
      { data: 'barang', name: 'barang' },
      { data: 'satuan_dasar', name: 'satuan_dasar' },
      { data: 'satuan_konversi', name: 'satuan_konversi' },
      { data: 'nilai_konversi', name: 'nilai_konversi' },
      { data: 'harga_beli', name: 'harga_beli' },
      { data: 'harga_jual', name: 'harga_jual' },
      { data: 'status', name: 'status' },
      { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
    ],
    order: [[1, 'asc']]
  });

  // Add validation rule for different satuan
  $.validator.addMethod("notEqualTo", function(value, element, param) {
    return value !== $(param).val();
  }, "Satuan Dasar dan Satuan Konversi tidak boleh sama");

  // Inisialisasi validator
  var validator = $('#editKonversiForm').validate({
    rules: {
      barang_id: { required: true },
      satuan_dasar_id: { required: true },
      satuan_konversi_id: { required: true, notEqualTo: "#edit_satuan_dasar_id" },
      nilai_konversi: { required: true, number: true, min: 0.01 },
      harga_beli: { number: true, min: 0 },
      harga_jual: { number: true, min: 0 },
      status: { required: true }
    },
    messages: {
      barang_id: { required: "Barang wajib dipilih" },
      satuan_dasar_id: { required: "Satuan Dasar wajib dipilih" },
      satuan_konversi_id: { required: "Satuan Konversi wajib dipilih" },
      nilai_konversi: {
        required: "Nilai Konversi wajib diisi",
        number: "Nilai Konversi harus berupa angka",
        min: "Nilai Konversi harus lebih dari 0"
      },
      harga_beli: {
        number: "Harga Beli harus berupa angka",
        min: "Harga Beli tidak boleh negatif"
      },
      harga_jual: {
        number: "Harga Jual harus berupa angka",
        min: "Harga Jual tidak boleh negatif"
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
      var konversiId = $('#konversiId').val();
      if (!konversiId) {
        Swal.fire('Error', 'Konversi ID tidak ditemukan. Coba lagi.', 'error');
        return;
      }

      var $btn = $('#btnUpdateKonversi');
      $btn.prop('disabled', true).text('Menyimpan...');

      $.ajax({
        url: '{{ route("konversi-satuan.update", ":id") }}'.replace(':id', konversiId),
        type: 'POST',
        data: $(form).serialize() + '&_method=PUT',
        success: function(response) {
          if (response.status) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              $('#editKonversiModal').modal('hide');
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

  // Detail handler
  $(document).on('click', '#btnDetail', function() {
    var konversiId = $(this).data('id');

    $.ajax({
      url: '{{ route("konversi-satuan.find", ":id") }}'.replace(':id', konversiId),
      type: 'GET',
      success: function(response) {
        if (response.status) {
          var konversi = response.data;
          var detailHtml = '';

          // Format data untuk tabel detail
          detailHtml += '<tr><td><strong>Barang</strong></td><td>' + (konversi.barang ? konversi.barang.nama_barang : '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Satuan Dasar</strong></td><td>' + (konversi.satuan_dasar ? konversi.satuan_dasar.nama_satuan : '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Satuan Konversi</strong></td><td>' + (konversi.satuan_konversi ? konversi.satuan_konversi.nama_satuan : '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Nilai Konversi</strong></td><td>' + Math.round(konversi.nilai_konversi || 0) + '</td></tr>';
          detailHtml += '<tr><td><strong>Harga Beli</strong></td><td>Rp ' + (konversi.harga_beli ? number_format(konversi.harga_beli, 0, ',', '.') : '0') + '</td></tr>';
          detailHtml += '<tr><td><strong>Harga Jual</strong></td><td>Rp ' + (konversi.harga_jual ? number_format(konversi.harga_jual, 0, ',', '.') : '0') + '</td></tr>';
          detailHtml += '<tr><td><strong>Status</strong></td><td>' + (konversi.status || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Dibuat Oleh</strong></td><td>' + (konversi.created_by || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Dibuat Pada</strong></td><td>' + (konversi.created_at || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Diubah Oleh</strong></td><td>' + (konversi.updated_by || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Diubah Pada</strong></td><td>' + (konversi.updated_at || '-') + '</td></tr>';

          $('#konversiDetailBody').html(detailHtml);
          $('#detailKonversiModal').modal('show');
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
        }
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
      }
    });
  });

  // Edit handler
  $(document).on('click', '#btnEdit', function() {
    var konversiId = $(this).data('id');
    $('#konversiId').val(konversiId);

    $.ajax({
      url: '{{ route("konversi-satuan.find", ":id") }}'.replace(':id', konversiId),
      type: 'GET',
      success: function(response) {
        if (response.status) {
          var konversi = response.data;
          $('#edit_barang_id').val(konversi.barang_id);
          $('#edit_barang_nama').val(konversi.barang ? konversi.barang.nama_barang : '');
          $('#edit_satuan_dasar_id').val(konversi.satuan_dasar_id);
          $('#edit_satuan_konversi_id').val(konversi.satuan_konversi_id);
          $('#edit_nilai_konversi').val(parseFloat(konversi.nilai_konversi).toFixed(2));
          $('#edit_harga_beli').val(konversi.harga_beli ? parseFloat(konversi.harga_beli).toFixed(2) : '');
          $('#edit_harga_jual').val(konversi.harga_jual ? parseFloat(konversi.harga_jual).toFixed(2) : '');
          $('#edit_status').val(konversi.status);

          validator.resetForm();
          $('#editKonversiForm').find('.is-invalid').removeClass('is-invalid');
          $('#editKonversiModal').modal('show');
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
    var konversiId = $(this).data('id');
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
          url: '{{ route("konversi-satuan.delete", ":id") }}'.replace(':id', konversiId),
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

  // Update button handler
  $('#btnUpdateKonversi').on('click', function(e) {
    e.preventDefault();
    if ($('#editKonversiForm').valid()) {
      $('#editKonversiForm').submit();
    } else {
      validator.focusInvalid();
    }
  });

});
</script>

@include('layout.footer')
