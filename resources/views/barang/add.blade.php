@include('layout.header')
<div class="container-fluid">
  <h3 class="mb-4">Tambah Barang</h3>
  <div class="card p-4">
    <form id="addBarangForm" action="#" method="POST">
      @csrf
      <div class="mb-3">
        <label for="kategori_id" class="form-label">Kategori*</label>
        <select class="form-control" id="kategori_id" name="kategori_id" required>
          <option value="">Pilih Kategori</option>
          @if(isset($categories))
            @foreach($categories as $category)
              <option value="{{ $category->id }}">{{ $category->nama_kategori }}</option>
            @endforeach
          @endif
        </select>
      </div>
      <div class="mb-3">
        <label for="satuan_id" class="form-label">Satuan*</label>
        <select class="form-control" id="satuan_id" name="satuan_id" required>
          <option value="">Pilih Satuan</option>
          @if(isset($satuans))
            @foreach($satuans as $satuan)
              <option value="{{ $satuan->id }}">{{ $satuan->nama_satuan }}</option>
            @endforeach
          @endif
        </select>
      </div>
      <div class="mb-3">
        <label for="kode_barang" class="form-label">Kode Barang*</label>
        <input type="text" class="form-control" id="kode_barang" name="kode_barang" required>
      </div>
      <div class="mb-3">
        <label for="nama_barang" class="form-label">Nama Barang*</label>
        <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
      </div>
      <div class="mb-3">
        <label for="stok" class="form-label">Stok*</label>
        <input type="number" class="form-control" id="stok" name="stok" value="0" min="0" required>
      </div>
      <div class="mb-3">
        <label for="harga_beli" class="form-label">Harga Beli*</label>
        <input type="number" class="form-control" id="harga_beli" name="harga_beli" min="0" value="0" required>
      </div>
      <div class="mb-3">
        <label for="harga_jual" class="form-label">Harga Jual*</label>
        <input type="number" class="form-control" id="harga_jual" name="harga_jual" min="0" value="0" required>
      </div>
      <div class="mb-3">
        <label for="deskripsi" class="form-label">Deskripsi</label>
        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
      </div>
      <div class="mb-3">
        <label for="multi_satuan" class="form-label">Multi Satuan</label>
        <select class="form-control" id="multi_satuan" name="multi_satuan">
          <option value="0">Tidak</option>
          <option value="1">Ya</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="status" class="form-label">Status</label>
        <select class="form-control" id="status" name="status">
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Nonaktif</option>
        </select>
      </div>

      <button type="submit" id="btnSave" class="btn btn-primary">Simpan</button>
    </form>
  </div>
</div>

@include('layout.footer')

<script>
$(document).ready(function() {

  $("[name=kategori_id]").focus().select();

  // Autocomplete kategori
  $('#kategori_autocomplete').autocomplete({
    source: function(request, response) {
      $.ajax({
        url: '{{ route("kategori.data") }}',
        dataType: 'json',
        data: { q: request.term },
        success: function(data) {
          if (data.success === true) {
            response($.map(data.data, function(item) {
              return {
                label: item.nama_kategori,
                value: item.nama_kategori,
                id: item.id
              };
            }));
          } else {
            $('#' + data.form).focus().select();
            alert(data.message);
          }
        },
        error: function(xhr) {
          console.log(xhr.responseText);
          alert('Terjadi kesalahan pada server.');
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

  $("#btnSave").click(function() {

    if ($("[name=deskripsi]").val() == "") {
      $("[name=deskripsi]").val('-');
    }

    $('#addBarangForm').validate({
      rules: {
        kode_barang: {
          required: true,
          minlength: 3
        },
        nama_barang: {
          required: true,
          minlength: 3
        },
        kategori_id: {
          required: true
        },
        satuan_id: {
          required: true
        },

        stok: {
          required: true,
          number: true,
          min: 0
        },
        harga_beli: {
          required: true,
          number: true,
          min: 0
        },
        harga_jual: {
          required: true,
          number: true,
          min: 0
        }
      },
      messages: {
        kode_barang: {
          required: "Kode Barang harus diisi",
          minlength: "Kode Barang minimal 3 karakter"
        },
        nama_barang: {
          required: "Nama Barang harus diisi",
          minlength: "Nama Barang minimal 3 karakter"
        },
        kategori_id: {
          required: "Kategori harus dipilih"
        },
        satuan_id: {
          required: "Satuan harus dipilih"
        },
        stok: {
          required: "Stok harus diisi",
          number: "Stok harus berupa angka",
          min: "Stok tidak boleh negatif"
        },
        harga_beli: {
          required: "Harga Beli harus diisi",
          number: "Harga Beli harus berupa angka",
          min: "Harga Beli tidak boleh negatif"
        },
        harga_jual: {
          required: "Harga Jual harus diisi",
          number: "Harga Jual harus berupa angka",
          min: "Harga Jual tidak boleh negatif"
        }
      },
      submitHandler: function(form) {
        // Set default deskripsi jika kosong
        if (!$('#deskripsi').val().trim()) {
          $('#deskripsi').val('-');
        }
        $.ajax({
          url: "{{ route('barang.store') }}",
          type: "POST",
          data: $(form).serialize(),
          success: function(response) {
            if (response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: response.message
              }).then(function() {
                setTimeout(() => {
                  window.location.href = "{{ route('barang.add') }}";
                }, 500);
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: response.message
              }).then(function() {
                setTimeout(() => {
                  $(`#${response.form}`).focus().select();
                }, 500);
              });
            }
          },
          error: function(xhr) {
            let message = 'Terjadi kesalahan';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
              const errors = Object.values(xhr.responseJSON.errors).flat();
              message = errors.join('<br>');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
              message = xhr.responseJSON.message;
            }
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              html: message
            });
          }
        });
      }
    });
  });
});
</script>
</body>
</html>