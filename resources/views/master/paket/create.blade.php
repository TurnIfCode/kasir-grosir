@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Tambah Paket</h3>
  <div class="card p-4">
    <form id="paketForm" method="POST" action="{{ route('master.paket.store') }}">
      @csrf
      <div class="mb-3">
        <label for="nama_paket" class="form-label">Nama Paket <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-lg" id="nama" name="nama" placeholder="Masukkan nama paket" required>
      </div>
      <div class="mb-3">
        <label for="total_qty" class="form-label">Total Quantity <span class="text-danger">*</span></label>
        <input type="number" class="form-control form-control-lg" id="total_qty" name="total_qty" placeholder="0" min="1" required>
      </div>
      <div class="mb-3">
        <label for="harga" class="form-label">Harga <span class="text-danger">*</span></label>
        <input type="number" class="form-control form-control-lg" id="harga" name="harga" placeholder="0" min="1" required>
      </div>
      <div class="mb-3">
        <label for="jenis" class="form-label">Jenis <span class="text-danger">*</span></label>
        <select class="form-select form-select-lg" id="jenis" name="jenis" required>
          <option value="tidak" selected>Tidak Campur</option>
          <option value="campur">Campur</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select class="form-select form-select-lg" id="status" name="status" required>
          <option value="aktif" selected>Aktif</option>
          <option value="nonaktif">Nonaktif</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="barang_ids" class="form-label">Daftar Barang <span class="text-danger">*</span></label>
        <select class="form-select form-select-lg" id="barang_ids" name="barang_ids[]" multiple="multiple" required style="width: 100%;">
        </select>
        <small class="form-text text-muted">Ketik minimal 3 karakter untuk mencari barang. Pilih satu atau lebih barang yang akan dimasukkan ke dalam paket.</small>
      </div>
      <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
  </div>
</div>

<script>
$(document).ready(function() {
  $("[name=nama]").focus().select();
  console.log('Initializing Select2...');

  try {
    $('#barang_ids').select2({
      placeholder: 'Ketik minimal 3 karakter untuk mencari barang...',
      minimumInputLength: 3,
      allowClear: true,
      ajax: {
        url: '{{ route("barang.search") }}',
        dataType: 'json',
        delay: 300,
        data: function (params) {
          return {
            term: params.term
          };
        },
        processResults: function (data) {
          console.log('Select2 data received:', data);
          var selectedValues = $(this.$element).val() || [];
          var filteredData = data.data.filter(function(item) {
            return selectedValues.indexOf(item.id.toString()) === -1;
          });
          return {
            results: filteredData
          };
        },
        cache: true,
        error: function(xhr, status, error) {
          console.error('Select2 AJAX error:', error);
        }
      },
      width: '100%'
    });

    console.log('Select2 initialized successfully');

  } catch (error) {
    console.error('Select2 initialization error:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Gagal menginisialisasi Select2'
    });
  }

  // Form validation
  $("#paketForm").validate({
    rules: {
      nama: {
        required: true,
        minlength: 3
      },
      total_qty: {
        required: true,
        number: true,
        min: 1
      },
      harga: {
        required: true,
        number: true,
        min: 0
      },
      status: {
        required: true
      },
      'barang_ids[]': {
        required: true
      }
    },
    messages: {
      nama: {
        required: "Nama Paket wajib diisi",
        minlength: "Nama Paket minimal 3 karakter"
      },
      total_qty: {
        required: "Total Quantity wajib diisi",
        number: "Total Quantity harus berupa angka",
        min: "Total Quantity minimal 1"
      },
      harga: {
        required: "Harga wajib diisi",
        number: "Harga harus berupa angka",
        min: "Harga minimal 0"
      },
      status: {
        required: "Status wajib dipilih"
      },
      'barang_ids[]': {
        required: "Minimal harus memilih satu barang"
      }
    },
    errorPlacement: function(error, element) {
      if (element.attr("id") === "barang_ids") {
        error.insertAfter($('#barang_ids').next('.select2'));
      } else {
        error.insertAfter(element);
      }
    },
    submitHandler: function(form) {
      // Submit via AJAX
      $.ajax({
        url: "{{ route('master.paket.store') }}",
        type: "POST",
        data: $(form).serialize(),
        success: function(response) {
          if (response.status) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message || 'Paket berhasil ditambahkan'
            }).then(function() {
              window.location.href = "{{ route('master.paket.create') }}";
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
          console.error('Form submit error:', xhr.responseText);
          var errorMessage = 'Terjadi kesalahan saat menyimpan data';
          try {
            var response = JSON.parse(xhr.responseText);
            if (response.message) {
              errorMessage = response.message;
            }
          } catch (e) {
            // Ignore parse error
          }
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: errorMessage
          });
        }
      });
    }
  });

  // Trigger validation on select2 change
  $('#barang_ids').on('change.select2', function() {
    $(this).valid();
  });
});
</script>

@include('layout.footer')
