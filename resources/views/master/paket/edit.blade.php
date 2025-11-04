@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Edit Paket</h3>
  <div class="card border-primary shadow-sm">
    <div class="card-header bg-primary text-white">
      <h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i>Form Edit Paket</h5>
    </div>
    <div class="card-body">
      <form id="paketForm" method="POST" action="{{ route('paket.update', $paket->id) }}">
        @csrf
        @method('PUT')
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="kode_paket" class="form-label">Kode Paket <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-lg" id="kode_paket" name="kode_paket" 
                   value="{{ old('kode_paket', $paket->kode_paket) }}" placeholder="Masukkan kode paket" required>
          </div>
          <div class="col-md-6 mb-3">
            <label for="nama_paket" class="form-label">Nama Paket <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-lg" id="nama_paket" name="nama_paket" 
                   value="{{ old('nama_paket', $paket->nama_paket) }}" placeholder="Masukkan nama paket" required>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="harga_per_3" class="form-label">Harga per 3 <span class="text-danger">*</span></label>
            <input type="number" class="form-control form-control-lg" id="harga_per_3" name="harga_per_3" 
                   value="{{ old('harga_per_3', $paket->harga_per_3) }}" placeholder="0" min="0" required>
          </div>
          <div class="col-md-6 mb-3">
            <label for="harga_per_unit" class="form-label">Harga per Unit <span class="text-danger">*</span></label>
            <input type="number" class="form-control form-control-lg" id="harga_per_unit" name="harga_per_unit" 
                   value="{{ old('harga_per_unit', $paket->harga_per_unit) }}" placeholder="0" min="0" required>
          </div>
        </div>
        <div class="mb-3">
          <label for="keterangan" class="form-label">Keterangan</label>
          <textarea class="form-control form-control-lg" id="keterangan" name="keterangan" rows="3" 
                    placeholder="Masukkan keterangan (opsional)">{{ old('keterangan', $paket->keterangan) }}</textarea>
        </div>
        <div class="mb-3">
          <label for="daftar_barang" class="form-label">Daftar Barang <span class="text-danger">*</span></label>
          <select class="form-select form-select-lg" id="daftar_barang" name="daftar_barang[]" multiple="multiple" required style="width: 100%;">
            @foreach($paket->details as $detail)
              <option value="{{ $detail->barang_id }}" selected>{{ $detail->barang->nama_barang }}</option>
            @endforeach
          </select>
          <small class="form-text text-muted">Ketik minimal 3 karakter untuk mencari barang. Pilih satu atau lebih barang yang akan dimasukkan ke dalam paket.</small>
        </div>
        <div class="d-flex justify-content-end gap-2">
          <a href="{{ route('paket.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Update
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  console.log('Initializing Select2...');

  // Get selected barang IDs for initial selection
  var selectedBarangIds = [
    @foreach($paket->details as $detail)
      {{ $detail->barang_id }},
    @endforeach
  ];

  // Initialize Select2 with error handling
  try {
    $('#daftar_barang').select2({
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
          return {
            results: data.data
          };
        },
        cache: true,
        error: function(xhr, status, error) {
          console.error('Select2 AJAX error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Gagal memuat data barang'
          });
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
    ignore: [],
    rules: {
      kode_paket: {
        required: true,
        minlength: 3
      },
      nama_paket: {
        required: true,
        minlength: 3
      },
      harga_per_3: {
        required: true,
        number: true,
        min: 0
      },
      harga_per_unit: {
        required: true,
        number: true,
        min: 0
      },
      'daftar_barang[]': {
        required: true
      }
    },
    messages: {
      kode_paket: {
        required: "Kode Paket wajib diisi",
        minlength: "Kode Paket minimal 3 karakter"
      },
      nama_paket: {
        required: "Nama Paket wajib diisi",
        minlength: "Nama Paket minimal 3 karakter"
      },
      harga_per_3: {
        required: "Harga per 3 wajib diisi",
        number: "Harga per 3 harus berupa angka",
        min: "Harga per 3 tidak boleh negatif"
      },
      harga_per_unit: {
        required: "Harga per Unit wajib diisi",
        number: "Harga per Unit harus berupa angka",
        min: "Harga per Unit tidak boleh negatif"
      },
      'daftar_barang[]': {
        required: "Minimal harus memilih satu barang"
      }
    },
    errorPlacement: function(error, element) {
      if (element.attr("id") == "daftar_barang") {
        error.insertAfter($('#daftar_barang').next('.select2'));
      } else {
        error.insertAfter(element);
      }
    },
    submitHandler: function(form) {
      // Set default keterangan jika kosong
      if (!$('#keterangan').val().trim()) {
        $('#keterangan').val('-');
      }

      // Log selected barang before submit
      var selectedBarang = $('#daftar_barang').val();
      console.log('Selected barang IDs:', selectedBarang);

      // Submit via AJAX
      $.ajax({
        url: "{{ route('paket.update', $paket->id) }}",
        type: "POST",
        data: $(form).serialize(),
        success: function(response) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: response.message || 'Paket berhasil diperbarui'
          }).then(function() {
            window.location.href = "{{ route('paket.index') }}";
          });
        },
        error: function(xhr) {
          console.error('Form submit error:', xhr.responseText);
          var errorMessage = 'Terjadi kesalahan saat memperbarui data';
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

  // Fix: when select2 value changes, trigger validation
  $('#daftar_barang').on('change.select2', function() {
    $(this).valid();
  });
});
</script>

@include('layout.footer')