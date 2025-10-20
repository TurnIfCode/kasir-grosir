@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Data Barang</h3>
  <div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
      <a href="{{ route('barang.add') }}" class="btn btn-primary">Tambah Barang</a>
    </div>
    <table id="barangTable" class="table table-striped">
      <thead>
        <tr>
          <th>Kode Barang</th>
          <th>Nama Barang</th>
          <th>Kategori</th>
          <th>Satuan</th>
          <th>Stok</th>
          <th>Harga Beli</th>
          <th>Harga Jual</th>
          <th>Barcode</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal Detail Barang -->
<div class="modal fade" id="detailBarangModal" tabindex="-1" aria-labelledby="detailBarangModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailBarangModalLabel">Detail Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tbody id="barangDetailBody">
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

<!-- Modal Edit Barang -->
<div class="modal fade" id="editBarangModal" tabindex="-1" aria-labelledby="editBarangModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editBarangModalLabel">Edit Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editBarangForm" action="#" method="POST">
          @csrf
          <input type="hidden" id="barangId" name="id">
          <div class="mb-3">
            <label for="edit_kode_barang" class="form-label">Kode Barang</label>
            <input type="text" class="form-control" id="edit_kode_barang" name="kode_barang">
          </div>
          <div class="mb-3">
            <label for="edit_nama_barang" class="form-label">Nama Barang*</label>
            <input type="text" class="form-control" id="edit_nama_barang" name="nama_barang" required>
          </div>
          <div class="mb-3">
            <label for="edit_kategori_id" class="form-label">Kategori</label>
            <select class="form-control" id="edit_kategori_id" name="kategori_id">
              <option value="">Pilih Kategori</option>
              @if(isset($kategori))
                @foreach($kategori as $cat)
                  <option value="{{ $cat->id }}">{{ $cat->nama_kategori }}</option>
                @endforeach
              @endif
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_satuan_id" class="form-label">Satuan</label>
            <select class="form-control" id="edit_satuan_id" name="satuan_id">
              <option value="">Pilih Satuan</option>
              <!-- Satuan options will be loaded dynamically -->
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_stok" class="form-label">Stok</label>
            <input type="number" class="form-control" id="edit_stok" name="stok" value="0" min="0">
          </div>
          <div class="mb-3">
            <label for="edit_harga_beli" class="form-label">Harga Beli</label>
            <input type="number" step="0.01" class="form-control" id="edit_harga_beli" name="harga_beli" min="0">
          </div>
          <div class="mb-3">
            <label for="edit_harga_jual" class="form-label">Harga Jual</label>
            <input type="number" step="0.01" class="form-control" id="edit_harga_jual" name="harga_jual" min="0">
          </div>
          <div class="mb-3">
            <label for="edit_multi_satuan" class="form-label">Multi Satuan</label>
            <select class="form-control" id="edit_multi_satuan" name="multi_satuan">
              <option value="0">Tidak</option>
              <option value="1">Ya</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_deskripsi" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label for="edit_status" class="form-label">Status</label>
            <select class="form-control" id="edit_status" name="status">
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>

          <div id="edit-barcode-list" class="mb-3">
            <label class="form-label">Barcode Barang</label>
            <!-- Barcodes will be loaded dynamically -->
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="btnUpdateBarang" class="btn btn-primary">Simpan</button>
      </div>
    </div>
  </div>
</div>

@section('scripts')
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
  // Load categories and satuan for edit modal
  function loadCategories() {
    $.ajax({
      url: '{{ route("kategori.data") }}',
      type: 'GET',
      data: {
        length: 1000 // Get all categories
      },
      success: function(response) {
        if (response.data) {
          var options = '<option value="">Pilih Kategori</option>';
          response.data.forEach(function(cat) {
            options += '<option value="' + cat.id + '">' + cat.nama_kategori + '</option>';
          });
          $('#edit_kategori_id').html(options);
        }
      },
      error: function(xhr) {
        console.log('Error loading categories');
      }
    });
  }

  function loadSatuan() {
    $.ajax({
      url: '{{ route("satuan.data") }}',
      type: 'GET',
      data: { length: 1000 },
      success: function(response) {
        if (response.data) {
          var options = '<option value="">Pilih Satuan</option>';
          response.data.forEach(function(sat) {
            options += '<option value="' + sat.id + '">' + sat.nama_satuan + '</option>';
          });
          $('#edit_satuan_id').html(options);
        }
      },
      error: function(xhr) {
        console.log('Error loading satuan');
      }
    });
  }

  loadCategories();
  loadSatuan();

  // DataTable
  var table = $('#barangTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '{{ route("barang.data") }}',
      type: 'GET'
    },
    columns: [
      { data: 'kode_barang', name: 'kode_barang' },
      { data: 'nama_barang', name: 'nama_barang' },
      { data: 'kategori', name: 'kategori' },
      { data: 'satuan', name: 'satuan' },
      { data: 'stok', name: 'stok' },
      { data: 'harga_beli', name: 'harga_beli' },
      { data: 'harga_jual', name: 'harga_jual' },
      { data: 'barcodes', name: 'barcodes' },
      { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
    ],
    order: [[2, 'asc']],
    responsive: true,
    pageLength: 10,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
    language: {
      search: "Cari:",
      lengthMenu: "Tampilkan _MENU_ data per halaman",
      zeroRecords: "Data tidak ditemukan",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
      infoEmpty: "Tidak ada data yang tersedia",
      infoFiltered: "(difilter dari _MAX_ total data)",
      paginate: {
        first: "Pertama",
        last: "Terakhir",
        next: "Selanjutnya",
        previous: "Sebelumnya"
      }
    }
  });

  // Inisialisasi validator
  var validator = $('#editBarangForm').validate({
    rules: {
      nama_barang: { required: true, minlength: 3 },
      stok: { number: true, min: 0 },
      harga_beli: { number: true, min: 0 },
      harga_jual: { number: true, min: 0 },
      'barcodes.*': { maxlength: 100 }
    },
    messages: {
      nama_barang: {
        required: "Nama Barang wajib diisi",
        minlength: "Nama Barang minimal 3 karakter"
      },
      stok: {
        number: "Stok harus berupa angka",
        min: "Stok tidak boleh negatif"
      },
      harga_beli: {
        number: "Harga Beli harus berupa angka",
        min: "Harga Beli tidak boleh negatif"
      },
      harga_jual: {
        number: "Harga Jual harus berupa angka",
        min: "Harga Jual tidak boleh negatif"
      },
      'barcodes.*': {
        maxlength: "Barcode maksimal 50 karakter"
      }
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
      var barangId = $('#barangId').val();
      if (!barangId) {
        Swal.fire('Error', 'Barang ID tidak ditemukan. Coba lagi.', 'error');
        return;
      }

      var $btn = $('#btnUpdateBarang');
      $btn.prop('disabled', true).text('Menyimpan...');

      $.ajax({
        url: '{{ route("barang.update", ":id") }}'.replace(':id', barangId),
        type: 'POST',
        data: $(form).serialize() + '&_method=PUT',
        success: function(response) {
          if (response.status) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              $('#editBarangModal').modal('hide');
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
    var barangId = $(this).data('id');

    $.ajax({
      url: '{{ route("barang.find", ":id") }}'.replace(':id', barangId),
      type: 'GET',
      success: function(response) {
        if (response.status) {
          var barang = response.data;
          var detailHtml = '';

          // Format data untuk tabel detail
          detailHtml += '<tr><td><strong>Kode Barang</strong></td><td>' + (barang.kode_barang || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Nama Barang</strong></td><td>' + (barang.nama_barang || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Kategori</strong></td><td>' + (barang.kategori ? barang.kategori.nama_kategori : '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Satuan</strong></td><td>' + (barang.satuan ? barang.satuan.nama_satuan : '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Stok</strong></td><td>' + (barang.stok || '0') + '</td></tr>';
          detailHtml += '<tr><td><strong>Harga Beli</strong></td><td>Rp ' + (barang.harga_beli ? number_format(barang.harga_beli, 0, ',', '.') : '0') + '</td></tr>';
          detailHtml += '<tr><td><strong>Harga Jual</strong></td><td>Rp ' + (barang.harga_jual ? number_format(barang.harga_jual, 0, ',', '.') : '0') + '</td></tr>';
          detailHtml += '<tr><td><strong>Multi Satuan</strong></td><td>' + (barang.multi_satuan ? 'Ya' : 'Tidak') + '</td></tr>';
          detailHtml += '<tr><td><strong>Deskripsi</strong></td><td>' + (barang.deskripsi || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Status</strong></td><td>' + (barang.status || 'aktif') + '</td></tr>';

          // Display barcodes
          var barcodes = barang.barcodes ? barang.barcodes.map(b => b.barcode).join(', ') : '-';
          detailHtml += '<tr><td><strong>Barcode</strong></td><td>' + barcodes + '</td></tr>';

          detailHtml += '<tr><td><strong>Dibuat Oleh</strong></td><td>' + (barang.creator ? barang.creator.name : '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Dibuat Pada</strong></td><td>' + (barang.created_at || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Diubah Oleh</strong></td><td>' + (barang.updater ? barang.updater.name : '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Diubah Pada</strong></td><td>' + (barang.updated_at || '-') + '</td></tr>';

          $('#barangDetailBody').html(detailHtml);
          $('#detailBarangModal').modal('show');
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
    var barangId = $(this).data('id');
    $('#barangId').val(barangId);

    $.ajax({
      url: '{{ route("barang.find", ":id") }}'.replace(':id', barangId),
      type: 'GET',
      success: function(response) {
        if (response.status) {
          var barang = response.data;
          $('#edit_kode_barang').val(barang.kode_barang);
          $('#edit_nama_barang').val(barang.nama_barang);
          $('#edit_kategori_id').val(barang.kategori_id || '');
          $('#edit_satuan_id').val(barang.satuan_id || '');
          $('#edit_stok').val(barang.stok || 0);
          $('#edit_harga_beli').val(barang.harga_beli ? parseFloat(barang.harga_beli).toFixed(2) : '');
          $('#edit_harga_jual').val(barang.harga_jual ? parseFloat(barang.harga_jual).toFixed(2) : '');
          $('#edit_multi_satuan').val(barang.multi_satuan || 0);
          $('#edit_deskripsi').val(barang.deskripsi || '');
          $('#edit_status').val(barang.status || 'aktif');

          // Load barcodes
          var barcodeHtml = '';
          if (barang.barcodes && barang.barcodes.length > 0) {
            barang.barcodes.forEach(function(b) {
              barcodeHtml += '<div class="input-group mb-2"><input type="text" name="barcodes[]" value="' + b.barcode + '" class="form-control"><button type="button" class="btn btn-danger remove-barcode">-</button></div>';
            });
          } else {
            barcodeHtml = '<div class="input-group mb-2"><input type="text" name="barcodes[]" class="form-control" placeholder="Scan atau input barcode"><button type="button" class="btn btn-success add-barcode">+</button></div>';
          }
          $('#edit-barcode-list').html(barcodeHtml);

          validator.resetForm();
          $('#editBarangForm').find('.is-invalid').removeClass('is-invalid');
          $('#editBarangModal').modal('show');
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
        }
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
      }
    });
  });

  // Add barcode functionality for edit modal
  $(document).on('click', '.add-barcode', function() {
    var clone = $(this).closest('.input-group').clone();
    clone.find('input').val('');
    clone.find('.add-barcode').removeClass('add-barcode btn-success').addClass('remove-barcode btn-danger').html('-');
    $('#edit-barcode-list').append(clone);
  });

  $(document).on('click', '.remove-barcode', function() {
    $(this).closest('.input-group').remove();
  });

  // Delete handler
  $(document).on('click', '#btnDelete', function() {
    var barangId = $(this).data('id');
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
          url: '{{ route("barang.delete", ":id") }}'.replace(':id', barangId),
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
  $('#btnUpdateBarang').on('click', function(e) {
    e.preventDefault();
    if ($('#editBarangForm').valid()) {
      $('#editBarangForm').submit();
    } else {
      validator.focusInvalid();
    }
  });

});
</script>
@endsection

@include('layout.footer')
