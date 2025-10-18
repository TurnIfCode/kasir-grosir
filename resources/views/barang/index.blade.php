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
          <th>Multi Satuan</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
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
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_kode_barang" class="form-label">Kode Barang*</label>
                <input type="text" class="form-control" id="edit_kode_barang" name="kode_barang" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_nama_barang" class="form-label">Nama Barang*</label>
                <input type="text" class="form-control" id="edit_nama_barang" name="nama_barang" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
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
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_satuan_id" class="form-label">Satuan</label>
                <select class="form-control" id="edit_satuan_id" name="satuan_id">
                  <option value="">Pilih Satuan</option>
                  @if(isset($satuans))
                    @foreach($satuans as $satuan)
                      <option value="{{ $satuan->id }}">{{ $satuan->nama_satuan }}</option>
                    @endforeach
                  @endif
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_stok" class="form-label">Stok</label>
                <input type="number" step="0.01" class="form-control" id="edit_stok" name="stok" value="0">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_harga_beli" class="form-label">Harga Beli</label>
                <input type="number" step="0.01" class="form-control" id="edit_harga_beli" name="harga_beli">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_harga_jual" class="form-label">Harga Jual</label>
                <input type="number" step="0.01" class="form-control" id="edit_harga_jual" name="harga_jual">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_multi_satuan" class="form-label">Multi Satuan</label>
                <select class="form-control" id="edit_multi_satuan" name="multi_satuan">
                  <option value="0">Tidak</option>
                  <option value="1">Ya</option>
                </select>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="edit_barcode" class="form-label">Barcode</label>
            <input type="text" class="form-control" id="edit_barcode" name="barcode">
          </div>
          <div class="mb-3">
            <label for="edit_deskripsi" class="form-label">Deskripsi</label>
            <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label for="edit_status" class="form-label">Status</label>
            <select class="form-control" id="edit_status" name="status" required>
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
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
$(document).ready(function() {
  // Load categories for edit modal
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

  loadCategories();

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
      { data: 'satuan_dasar', name: 'satuan_dasar' },
      { data: 'stok', name: 'stok' },
      { data: 'harga_beli', name: 'harga_beli' },
      { data: 'harga_jual', name: 'harga_jual' },
      { data: 'multi_satuan', name: 'multi_satuan' },
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
      kode_barang: { required: true, minlength: 3 },
      nama_barang: { required: true, minlength: 3 },
      stok: { number: true, min: 0 },
      harga_beli: { number: true, min: 0 },
      harga_jual: { number: true, min: 0 },
      status: { required: true }
    },
    messages: {
      kode_barang: {
        required: "Kode Barang wajib diisi",
        minlength: "Kode Barang minimal 3 karakter"
      },
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
          $('#edit_stok').val(parseFloat(barang.stok).toFixed(0));
          $('#edit_harga_beli').val(barang.harga_beli ? parseFloat(barang.harga_beli).toFixed(0) : '');
          $('#edit_harga_jual').val(barang.harga_jual ? parseFloat(barang.harga_jual).toFixed(0) : '');
          $('#edit_multi_satuan').val(barang.multi_satuan);
          $('#edit_barcode').val(barang.barcode);
          $('#edit_deskripsi').val(barang.deskripsi);
          $('#edit_status').val(barang.status);

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
