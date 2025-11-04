@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Data Jenis Barang</h3>
  <div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
      <h5>Data Jenis Barang</h5>
      <a href="{{ route('jenis_barang.add') }}" class="btn btn-primary">Tambah Jenis Barang</a>
    </div>
    <table id="jenisBarangTable" class="table table-striped">
      <thead>
        <tr>
          <th>Kode Jenis</th>
          <th>Nama Jenis</th>
          <th>Kategori</th>
          <th>Barang</th>
          <th>Supplier</th>
          <th>Deskripsi</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal Detail Jenis Barang -->
<div class="modal fade" id="detailJenisBarangModal" tabindex="-1" aria-labelledby="detailJenisBarangModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailJenisBarangModalLabel">Detail Jenis Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tbody id="jenisBarangDetailBody">
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

<!-- Modal Edit Jenis Barang -->
<div class="modal fade" id="editJenisBarangModal" tabindex="-1" aria-labelledby="editJenisBarangModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editJenisBarangModalLabel">Ubah Jenis Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="updateJenisBarangForm" action="#" method="POST">
          @csrf
          <input type="hidden" id="jenisBarangId" name="id">
          <div class="mb-3">
            <label for="editKodeJenis" class="form-label">Kode Jenis*</label>
            <input type="text" class="form-control" id="editKodeJenis" name="kode_jenis" required>
          </div>
          <div class="mb-3">
            <label for="editNamaJenis" class="form-label">Nama Jenis*</label>
            <input type="text" class="form-control" id="editNamaJenis" name="nama_jenis" required>
          </div>
          <div class="mb-3">
            <label for="edit_kategori_autocomplete" class="form-label">Kategori*</label>
            <input type="text" class="form-control" id="edit_kategori_autocomplete" name="kategori_nama" placeholder="Ketik nama kategori" required>
            <input type="hidden" id="editKategoriId" name="kategori_id" required>
          </div>
          <div class="mb-3">
            <label for="edit_barang_autocomplete" class="form-label">Barang*</label>
            <input type="text" class="form-control" id="edit_barang_autocomplete" name="barang_nama" placeholder="Ketik nama barang" required>
            <input type="hidden" id="editBarangId" name="barang_id" required>
          </div>
          <div class="mb-3">
            <label for="edit_supplier_autocomplete" class="form-label">Supplier*</label>
            <input type="text" class="form-control" id="edit_supplier_autocomplete" name="supplier_nama" placeholder="Ketik nama supplier" required>
            <input type="hidden" id="editSupplierId" name="supplier_id" required>
          </div>
          <div class="mb-3">
            <label for="editDeskripsi" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="editDeskripsi" name="deskripsi" rows="3" placeholder="-"></textarea>
          </div>
          <div class="mb-3">
            <label for="editStatus" class="form-label">Status Aktif</label>
            <select class="form-control" id="editStatus" name="status" required>
              <option value="aktif">AKTIF</option>
              <option value="nonaktif">TIDAK AKTIF</option>
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

<script>
$(document).ready(function() {
  // DataTable
  var table = $('#jenisBarangTable').DataTable({
    processing: true,
    serverSide: true,
      ajax: '{{ route("jenis_barang.data") }}',
    columns: [
      { data: 'kode_jenis', name: 'kode_jenis' },
      { data: 'nama_jenis', name: 'nama_jenis' },
      { data: 'kategori', name: 'kategori' },
      { data: 'barang', name: 'barang' },
      { data: 'supplier', name: 'supplier' },
      { data: 'deskripsi', name: 'deskripsi' },
      { data: 'status', name: 'status' },
      { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
    ]
  });

  // Autocomplete kategori untuk edit
  $('#edit_kategori_autocomplete').autocomplete({
    source: function(request, response) {
      $.ajax({
        url: '{{ route("jenis_barang.search.kategori") }}',
        dataType: 'json',
        data: { q: request.term },
        success: function(data) {
          if (data.status === 'success') {
            response($.map(data.data, function(item) {
              return {
                label: item.nama_kategori,
                value: item.nama_kategori,
                id: item.id
              };
            }));
          }
        }
      });
    },
    minLength: 2,
    select: function(event, ui) {
      $('#editKategoriId').val(ui.item.id);
      $('#edit_kategori_autocomplete').val(ui.item.value);
      return false;
    }
  });

  // Autocomplete barang untuk edit
  $('#edit_barang_autocomplete').autocomplete({
    source: function(request, response) {
      $.ajax({
        url: '{{ route("jenis_barang.search.barang") }}',
        dataType: 'json',
        data: { q: request.term },
        success: function(data) {
          if (data.status === 'success') {
            response($.map(data.data, function(item) {
              return {
                label: item.nama_barang,
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
      $('#editBarangId').val(ui.item.id);
      $('#edit_barang_autocomplete').val(ui.item.value);
      return false;
    }
  });

  // Autocomplete supplier untuk edit
  $('#edit_supplier_autocomplete').autocomplete({
    source: function(request, response) {
      $.ajax({
        url: '{{ route("jenis_barang.search.supplier") }}',
        dataType: 'json',
        data: { q: request.term },
        success: function(data) {
          if (data.status === 'success') {
            response($.map(data.data, function(item) {
              return {
                label: item.nama_supplier,
                value: item.nama_supplier,
                id: item.id
              };
            }));
          }
        }
      });
    },
    minLength: 2,
    select: function(event, ui) {
      $('#editSupplierId').val(ui.item.id);
      $('#edit_supplier_autocomplete').val(ui.item.value);
      return false;
    }
  });

  // Inisialisasi validator
  var validator = $('#updateJenisBarangForm').validate({
    rules: {
      kode_jenis: { required: true, minlength: 3 },
      nama_jenis: { required: true, minlength: 3 },
      kategori_id: "required",
      barang_id: "required",
      supplier_id: "required",
      status: { required: true }
    },
    messages: {
      kode_jenis: {
        required: "Kode Jenis wajib diisi",
        minlength: "Kode Jenis minimal 3 karakter"
      },
      nama_jenis: {
        required: "Nama Jenis wajib diisi",
        minlength: "Nama Jenis minimal 3 karakter"
      },
      kategori_id: "Kategori wajib dipilih",
      barang_id: "Barang wajib dipilih",
      supplier_id: "Supplier wajib dipilih",
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
      // Set default deskripsi jika kosong
      if (!$('#editDeskripsi').val().trim()) {
        $('#editDeskripsi').val('-');
      }

      var jenisBarangId = $('#jenisBarangId').val();
      if (!jenisBarangId) {
        Swal.fire('Error', 'Jenis Barang ID tidak ditemukan. Coba lagi.', 'error');
        return;
      }

      var $btn = $('#btnUpdate');
      $btn.prop('disabled', true).text('Menyimpan...');

      $.ajax({
        url: '{{ route("jenis_barang.update", ":id") }}'.replace(':id', jenisBarangId),
        type: 'POST',
        data: $(form).serialize() + '&_method=PUT',
        success: function(response) {
          if (response.status) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              $('#editJenisBarangModal').modal('hide');
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
    var jenisBarangId = $(this).data('id');

    $.ajax({
      url: '{{ route("jenis_barang.find", ":id") }}'.replace(':id', jenisBarangId),
      type: 'GET',
      success: function(response) {
        if (response.status) {
          var jenisBarang = response.data;
          var detailHtml = '';

          // Format data untuk tabel detail
          detailHtml += '<tr><td><strong>Kode Jenis</strong></td><td>' + (jenisBarang.kode_jenis || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Nama Jenis</strong></td><td>' + (jenisBarang.nama_jenis || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Kategori</strong></td><td>' + (jenisBarang.kategori_nama || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Barang</strong></td><td>' + (jenisBarang.barang_nama || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Supplier</strong></td><td>' + (jenisBarang.supplier_nama || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Deskripsi</strong></td><td>' + (jenisBarang.deskripsi || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Status</strong></td><td>' + (jenisBarang.status || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Dibuat Oleh</strong></td><td>' + (jenisBarang.created_by || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Dibuat Pada</strong></td><td>' + (jenisBarang.created_at || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Diubah Oleh</strong></td><td>' + (jenisBarang.updated_by || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Diubah Pada</strong></td><td>' + (jenisBarang.updated_at || '-') + '</td></tr>';

          $('#jenisBarangDetailBody').html(detailHtml);
          $('#detailJenisBarangModal').modal('show');
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
    var jenisBarangId = $(this).data('id');
    $('#jenisBarangId').val(jenisBarangId);

    $.ajax({
      url: '{{ route("jenis_barang.find", ":id") }}'.replace(':id', jenisBarangId),
      type: 'GET',
      success: function(response) {
        if (response.status) {
          var jenisBarang = response.data;
          $('#editKodeJenis').val(jenisBarang.kode_jenis);
          $('#editNamaJenis').val(jenisBarang.nama_jenis);
          $('#edit_kategori_autocomplete').val(jenisBarang.kategori_nama);
          $('#editKategoriId').val(jenisBarang.kategori_id);
          $('#edit_barang_autocomplete').val(jenisBarang.barang_nama);
          $('#editBarangId').val(jenisBarang.barang_id);
          $('#edit_supplier_autocomplete').val(jenisBarang.supplier_nama);
          $('#editSupplierId').val(jenisBarang.supplier_id);
          $('#editDeskripsi').val(jenisBarang.deskripsi);
          $('#editStatus').val(jenisBarang.status);
          validator.resetForm();
          $('#updateJenisBarangForm').find('.is-invalid').removeClass('is-invalid');
          $('#editJenisBarangModal').modal('show');
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
    var jenisBarangId = $(this).data('id');
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
          url: '{{ route("jenis_barang.delete", ":id") }}'.replace(':id', jenisBarangId),
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
    if ($('#updateJenisBarangForm').valid()) {
      $('#updateJenisBarangForm').submit();
    } else {
      validator.focusInvalid();
    }
  });
});
</script>

@include('layout.footer')
