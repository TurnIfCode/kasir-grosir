@include('layout.header')
<div class="container-fluid">
  <h3 class="mb-4">Tambah Barang</h3>
  <div class="card p-4">
    <form id="addBarangForm" action="#" method="POST">
      @csrf
      <div class="mb-3">
        <label for="kode_barang" class="form-label">Kode Barang*</label>
        <input type="text" class="form-control" id="kode_barang" name="kode_barang" required>
      </div>
      <div class="mb-3">
        <label for="nama_barang" class="form-label">Nama Barang*</label>
        <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
      </div>
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
        <label for="stok" class="form-label">Stok*</label>
        <input type="number" class="form-control" id="stok" name="stok" value="0" min="0" required>
      </div>
      <div class="mb-3">
        <label for="harga_beli" class="form-label">Harga Beli*</label>
        <input type="number" step="0.01" class="form-control" id="harga_beli" name="harga_beli" min="0" value="0" required>
      </div>
      <div class="mb-3">
        <label for="harga_jual" class="form-label">Harga Jual*</label>
        <input type="number" step="0.01" class="form-control" id="harga_jual" name="harga_jual" min="0" value="0" required>
      </div>
      <div class="mb-3">
        <label for="multi_satuan" class="form-label">Multi Satuan</label>
        <select class="form-control" id="multi_satuan" name="multi_satuan">
          <option value="0">Tidak</option>
          <option value="1">Ya</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="deskripsi" class="form-label">Deskripsi</label>
        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
      </div>
      <div class="mb-3">
        <label for="status" class="form-label">Status</label>
        <select class="form-control" id="status" name="status">
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Nonaktif</option>
        </select>
      </div>

      <div id="barcode-list" class="mb-3">
        <label class="form-label">Barcode Barang</label>
        <div class="input-group mb-2">
          <input type="text" name="barcodes[]" class="form-control" placeholder="Scan atau input barcode">
          <button type="button" class="btn btn-success add-barcode">+</button>
        </div>
      </div>

      <button type="submit" id="btnSave" class="btn btn-primary">Simpan</button>
    </form>
  </div>

@section('scripts')
<script>
$(document).ready(function() {

  // Add barcode functionality
  $('.add-barcode').on('click', function() {
    const clone = $(this).closest('.input-group').clone();
    clone.find('input').val('');
    clone.find('.add-barcode').removeClass('add-barcode btn-success').addClass('remove-barcode btn-danger').html('-');
    $('#barcode-list').append(clone);
  });

  $(document).on('click', '.remove-barcode', function() {
    $(this).closest('.input-group').remove();
  });

  $("#btnSave").click(function() {
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
        },
        'barcodes.*': {
          maxlength: 100
        }
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
        kategori_id: {
          required: "Kategori wajib dipilih"
        },
        stok: {
          required: "Stok wajib diisi",
          number: "Stok harus berupa angka",
          min: "Stok tidak boleh negatif"
        },
        harga_beli: {
          required: "Harga Beli wajib diisi",
          number: "Harga Beli harus berupa angka",
          min: "Harga Beli tidak boleh negatif"
        },
        harga_jual: {
          required: "Harga Jual wajib diisi",
          number: "Harga Jual harus berupa angka",
          min: "Harga Jual tidak boleh negatif"
        },
        'barcodes.*': {
          maxlength: "Barcode maksimal 50 karakter"
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
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              setTimeout(() => {
                window.location.href = "{{ route('barang.index') }}";
              }, 500);
            });
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

@endsection

@include('layout.footer')
