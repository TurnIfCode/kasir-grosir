@include('layout.header')
<div class="container-fluid">
  <h3 class="mb-4">Tambah Konversi Satuan</h3>
  <div class="card p-4">
    <form id="addKonversiForm" action="{{ route('konversi-satuan.store') }}" method="POST">
      @csrf

      <div class="form-group">
        <label for="barang_id">Pilih Barang *</label>
        <input type="text" class="form-control barang-autocomplete" id="barang_nama" name="barang_nama" placeholder="Ketik nama, kode barang atau scan barcode" required>
        <input type="hidden" name="barang_id" id="barang_id" required>
      </div>

      <div class="form-group">
        <label for="satuan_dasar_id">Satuan Dasar *</label>
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
        <label for="satuan_konversi_id">Satuan Konversi *</label>
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
        <label for="nilai_konversi">Nilai Konversi *</label>
        <input type="number" step="0.01" name="nilai_konversi" id="nilai_konversi"
               class="form-control" placeholder="contoh: 12 (1 pack = 12 bungkus)" required>
      </div>

      <div class="form-group">
        <label for="harga_beli">Harga Beli Satuan Konversi *</label>
        <input type="number" step="0.01" name="harga_beli" id="harga_beli"
               class="form-control" placeholder="contoh: 324000" required>
      </div>

      <div class="form-group" style="display: none;">
        <label for="harga_jual">Harga Jual Satuan Konversi</label>
        <input type="number" step="0.01" name="harga_jual" id="harga_jual"
               class="form-control" placeholder="contoh: 384000" value="0">
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
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="konversiTableBody">
          <!-- Data will be loaded here -->
        </tbody>
      </table>
    </form>
  </div>

<script>
$(document).ready(function() {

  $("#barang_nama").focus().select();

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
            html += '<td>' + k.status + '</td>';
            html += '<td><a href="#" data-id="' + k.id + '" class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash"></i></a></td>';
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

  $(document).on("keypress", "#barang_nama", function (e) {

      if (e.which !== 13) return;
      e.preventDefault();

      let barcode = $(this).val().trim();
      if (barcode === "") return;

      $.ajax({
          url: "{{ route('barang.search') }}",
          type: "GET",
          dataType: "json",
          data: { term: barcode }, // WAJIB: term
          success: function (res) {

              if (!res.success || res.data.length === 0) {
                  Swal.fire({
                      icon: "warning",
                      title: "Barcode tidak ditemukan"
                  });
                  $("#barang_nama").select();
                  return;
              }

              // BARCODE HARUS UNIK
              let barang = res.data[0];

              // 🔥 SET FIELD MANUAL (INI YANG AUTOCOMPLETE LAKUKAN)
              $("#barang_id").val(barang.id);
              $("#barang_nama").val(barang.nama_barang);
              $("#satuan_dasar_id").val(barang.satuan_id);

              // 🔥 PANGGIL LOGIC SETELAH PILIH BARANG
              loadKonversiTable(barang.id);

              // OPTIONAL: fokus ke field berikutnya
              $("#satuan_konversi_id").focus();
          }
      });
  });


  // Autocomplete barang
  $('.barang-autocomplete').autocomplete({
    source: function(request, response) {
      $.ajax({
        url: '{{ route("barang.search") }}',
        dataType: 'json',
        data: { q: request.term },
        success: function(data) {
          if (data.success) {
          response($.map(data.data, function(item) {
              return {
                label: item.kode_barang + ' - ' + item.nama_barang,
                value: item.nama_barang,
                id: item.id,
                satuan_id: item.satuan_id
              };
            }));
          }
        }
      });
    },
    minLength: 2,
    select: function(event, ui) {
      $('#barang_id').val(ui.item.id);
      $('#barang_nama').val(ui.item.value);
      // Set satuan dasar otomatis dari barang yang dipilih
      $('#satuan_dasar_id').val(ui.item.satuan_id);
      loadKonversiTable(ui.item.id);
      return false;
    }
  });

  // On barang change, load konversi table
  $('#barang_nama').on('input', function() {
    if ($(this).val() === '') {
      $('#barang_id').val('');
      loadKonversiTable('');
    }
  });

  // Handle delete button click
  $(document).on('click', '.btn-delete', function(e) {
    e.preventDefault();
    var konversiId = $(this).data('id');
    var barangId = $('#barang_id').val();

    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: "Data konversi satuan akan dihapus permanen!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, Hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: '{{ route("konversi-satuan.delete", ":id") }}'.replace(':id', konversiId),
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              // Reload konversi table
              loadKonversiTable(barangId);
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

  // Add validation rule for different satuan
  $.validator.addMethod("notEqualTo", function(value, element, param) {
    return value !== $(param).val();
  }, "Satuan Dasar dan Satuan Konversi tidak boleh sama");

  $("#addKonversiForm").click(function() {
    $('#addKonversiForm').validate({
      rules: {
        barang_nama: {
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
          required: true,
          number: true,
          min: 0
        },

      },
      messages: {
        barang_nama: {
          required: "Barang harus diisi"
        },
        satuan_dasar_id: {
          required: "Satuan Dasar harus diisi"
        },
        satuan_konversi_id: {
          required: "Satuan Konversi harus diisi"
        },
        nilai_konversi: {
          required: "Nilai Konversi harus diisi",
          number: "Nilai Konversi harus berupa angka",
          min: "Nilai Konversi harus lebih dari 0"
        },
        harga_beli: {
          required: "Harga Beli Satuan Konversi harus diisi",
          number: "Harga Beli harus berupa angka",
          min: "Harga Beli tidak boleh negatif"
        },

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
              $('#barang_nama').val('');
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

@include('layout.footer')
