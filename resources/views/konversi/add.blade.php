@include('layout.header')
<div class="container-fluid">
  <h3 class="mb-4">Tambah Konversi Satuan</h3>
  <div class="card p-4">
    <form id="addKonversiForm" action="{{ route('konversi-satuan.store') }}" method="POST">
      @csrf

      <div class="form-group">
        <label for="barang_id">Pilih Barang</label>
        <select name="barang_id" id="barang_id" class="form-control" required>
          <option value="">-- Pilih Barang --</option>
          @if(isset($barang))
            @foreach($barang as $b)
              <option value="{{ $b->id }}">{{ $b->kode_barang }} - {{ $b->nama_barang }}</option>
            @endforeach
          @endif
        </select>
      </div>

      <div class="form-group">
        <label for="satuan_dasar_id">Satuan Dasar</label>
        <select name="satuan_dasar_id" id="satuan_dasar_id" class="form-control" required>
          <option value="">-- Pilih Satuan Dasar --</option>
          @if(isset($satuan))
            @foreach($satuan as $s)
              <option value="{{ $s->id }}">{{ $s->nama_satuan }}</option>
            @endforeach
          @endif
        </select>
      </div>

      <div class="form-group">
        <label for="satuan_konversi_id">Satuan Konversi</label>
        <select name="satuan_konversi_id" id="satuan_konversi_id" class="form-control" required>
          <option value="">-- Pilih Satuan Konversi --</option>
          @if(isset($satuan))
            @foreach($satuan as $s)
              <option value="{{ $s->id }}">{{ $s->nama_satuan }}</option>
            @endforeach
          @endif
        </select>
      </div>

      <div class="form-group">
        <label for="nilai_konversi">Nilai Konversi</label>
        <input type="number" step="0.01" name="nilai_konversi" id="nilai_konversi"
               class="form-control" placeholder="contoh: 12 (1 pack = 12 bungkus)" required>
      </div>

      <div class="form-group">
        <label for="harga_beli">Harga Beli Satuan Konversi</label>
        <input type="number" step="0.01" name="harga_beli" id="harga_beli"
               class="form-control" placeholder="contoh: 324000">
      </div>

      <div class="form-group">
        <label for="harga_jual">Harga Jual Satuan Konversi</label>
        <input type="number" step="0.01" name="harga_jual" id="harga_jual"
               class="form-control" placeholder="contoh: 384000">
      </div>

      <div class="form-group">
        <label for="status">Status</label>
        <select name="status" id="status" class="form-control">
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Nonaktif</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary mt-3">Simpan Konversi</button>

      <h5 class="mt-4">Konversi Satuan yang Sudah Dibuat</h5>
      <table id="konversiTable" class="table table-striped">
        <thead>
          <tr>
            <th>No</th>
            <th>Satuan Dasar</th>
            <th>Satuan Konversi</th>
            <th>Nilai Konversi</th>
            <th>Harga Beli</th>
            <th>Harga Jual</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="konversiTableBody">
          <!-- Data will be loaded here -->
        </tbody>
      </table>
    </form>
  </div>

@section('scripts')
<script>
$(document).ready(function() {

  // Load konversi table for selected barang
  function loadKonversiTable(barangId) {
    if (!barangId) {
      $('#konversiTableBody').html('');
      return;
    }

    $.ajax({
      url: '{{ route("konversi-satuan.data") }}',
      type: 'GET',
      data: {
        barang_id: barangId,
        length: 100 // Get all for this barang
      },
      success: function(response) {
        if (response.data) {
          var html = '';
          response.data.forEach(function(k, index) {
            html += '<tr>';
            html += '<td>' + (index + 1) + '</td>';
            html += '<td>' + k.satuan_dasar + '</td>';
            html += '<td>' + k.satuan_konversi + '</td>';
            html += '<td>' + k.nilai_konversi + '</td>';
            html += '<td>' + k.harga_beli + '</td>';
            html += '<td>' + k.harga_jual + '</td>';
            html += '<td>' + k.status + '</td>';
            html += '</tr>';
          });
          $('#konversiTableBody').html(html);
        }
      },
      error: function(xhr) {
        console.log('Error loading konversi table');
      }
    });
  }

  // On barang change, load konversi table
  $('#barang_id').on('change', function() {
    var barangId = $(this).val();
    loadKonversiTable(barangId);
  });

  // Add validation rule for different satuan
  $.validator.addMethod("notEqualTo", function(value, element, param) {
    return value !== $(param).val();
  }, "Satuan Dasar dan Satuan Konversi tidak boleh sama");

  $("#addKonversiForm").click(function() {
    $('#addKonversiForm').validate({
      rules: {
        barang_id: {
          required: true
        },
        satuan_dasar_id: {
          required: true
        },
        satuan_konversi_id: {
          required: true,
          notEqualTo: "#satuan_dasar_id"
        },
        nilai_konversi: {
          required: true,
          number: true,
          min: 0.01
        },
        harga_beli: {
          number: true,
          min: 0
        },
        harga_jual: {
          number: true,
          min: 0
        }
      },
      messages: {
        barang_id: {
          required: "Barang wajib dipilih"
        },
        satuan_dasar_id: {
          required: "Satuan Dasar wajib dipilih"
        },
        satuan_konversi_id: {
          required: "Satuan Konversi wajib dipilih"
        },
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
        }
      },
      submitHandler: function(form) {
        $.ajax({
          url: "{{ route('konversi-satuan.store') }}",
          type: "POST",
          data: $(form).serialize(),
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              // Reload konversi table
              var barangId = $('#barang_id').val();
              loadKonversiTable(barangId);
              // Reset form
              form.reset();
            });
          },
          error: function(xhr) {
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan'
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
