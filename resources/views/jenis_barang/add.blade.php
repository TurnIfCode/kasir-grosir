@include('layout.header')
<div class="container-fluid">
  <h3 class="mb-4">Tambah Jenis Barang</h3>
  <div class="card p-4">
    <form id="addJenisBarangForm" action="#" method="POST">
      @csrf
      <div class="mb-3">
        <label for="kode_jenis" class="form-label">Kode Jenis*</label>
        <input type="text" class="form-control" id="kode_jenis" name="kode_jenis" required>
      </div>
      <div class="mb-3">
        <label for="nama_jenis" class="form-label">Nama Jenis*</label>
        <input type="text" class="form-control" id="nama_jenis" name="nama_jenis" required>
      </div>
      <div class="mb-3">
        <label for="kategori_autocomplete" class="form-label">Kategori*</label>
        <input type="text" class="form-control" id="kategori_autocomplete" name="kategori_nama" placeholder="Ketik nama kategori" required>
        <input type="hidden" id="kategori_id" name="kategori_id" required>
      </div>
      <div class="mb-3">
        <label for="barang_autocomplete" class="form-label">Barang*</label>
        <input type="text" class="form-control" id="barang_autocomplete" name="barang_nama" placeholder="Ketik nama barang" required>
        <input type="hidden" id="barang_id" name="barang_id" required>
      </div>
      <div class="mb-3">
        <label for="supplier_autocomplete" class="form-label">Supplier*</label>
        <input type="text" class="form-control" id="supplier_autocomplete" name="supplier_nama" placeholder="Ketik nama supplier" required>
        <input type="hidden" id="supplier_id" name="supplier_id" required>
      </div>
      <div class="mb-3">
        <label for="deskripsi">Deskripsi</label>
        <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" placeholder="-"></textarea>
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
  // Autocomplete kategori
  $('#kategori_autocomplete').autocomplete({
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
      $('#kategori_id').val(ui.item.id);
      $('#kategori_autocomplete').val(ui.item.value);
      return false;
    }
  });

  // Autocomplete barang
  $('#barang_autocomplete').autocomplete({
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
      $('#barang_id').val(ui.item.id);
      $('#barang_autocomplete').val(ui.item.value);
      return false;
    }
  });

  // Autocomplete supplier
  $('#supplier_autocomplete').autocomplete({
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
      $('#supplier_id').val(ui.item.id);
      $('#supplier_autocomplete').val(ui.item.value);
      return false;
    }
  });

  $("#btnSave").click(function() {
    $('#addJenisBarangForm').validate({
      rules: {
        kode_jenis: {
          required: true,
          minlength: 3
        },
        nama_jenis: {
          required: true,
          minlength: 3
        },
        kategori_id: "required",
        barang_id: "required",
        supplier_id: "required"
      },
      messages: {
        kode_jenis: {
          required: "Kode jenis wajib diisi",
          minlength: "Kode jenis minimal 3 karakter"
        },
        nama_jenis: {
          required: "Nama jenis wajib diisi",
          minlength: "Nama jenis minimal 3 karakter"
        },
        kategori_id: "Kategori wajib dipilih",
        barang_id: "Barang wajib dipilih",
        supplier_id: "Supplier wajib dipilih"
      },
      submitHandler: function(form) {
        // Set default deskripsi jika kosong
        if (!$('#deskripsi').val().trim()) {
          $('#deskripsi').val('-');
        }

        $.ajax({
          url: "{{ route('jenis_barang.store') }}",
          type: "POST",
          data: $(form).serialize(),
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              setTimeout(() => {
                window.location.href = "{{ route('jenis_barang.add') }}";
              }, 500);
            });
          },
          error: function(xhr) {
            var response = xhr.responseJSON;
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: response.message || 'Terjadi kesalahan pada server'
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
