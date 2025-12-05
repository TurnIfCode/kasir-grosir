@include('layout.header')

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Tambah Stok Opname</h2>
    <a href="{{ route('stok-opname.index') }}" class="btn btn-secondary">Kembali</a>
  </div>

  <div class="card" id="initial-form">
    <div class="card-body">
      <form id="opname-form">
        @csrf

        <div class="row mb-3">
          <div class="col-md-6">
            <label for="tanggal" class="form-label">Tanggal Opname</label>
            <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}" required>
          </div>
          <div class="col-md-6">
            <label for="catatan" class="form-label">Catatan</label>
            <textarea class="form-control" id="catatan" name="catatan" rows="3"></textarea>
          </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
          <button type="submit" id="btnSave" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card d-none" id="barang-form">
    <div class="card-body">
      <h5 class="mb-3">Tambah Barang</h5>
      <form id="add-barang-form">
        @csrf
        <input type="hidden" id="stok_opname_id" name="stok_opname_id">
        <div class="row mb-3">
          <div class="col-md-2">
            <label for="barang_search" class="form-label">Cari Barang</label>
            <input type="text" class="form-control" id="barang_search" placeholder="Ketik nama atau kode barang">
            <input type="hidden" id="barang_id" name="barang_id">
          </div>
          <div class="col-md-2">
            <label for="nama_barang" class="form-label">Nama Barang</label>
            <input type="text" class="form-control" id="nama_barang" readonly>
          </div>
          <div class="col-md-1">
            <label for="satuan_id" class="form-label">Satuan</label>
            <select class="form-control" id="satuan_id" name="satuan_id" required>
              <option value="">-- Pilih Satuan --</option>
            </select>
          </div>
          <div class="col-md-1">
            <label for="stok_sistem" class="form-label">Stok Sistem</label>
            <input type="number" class="form-control" id="stok_sistem" readonly>
          </div>
          <div class="col-md-1">
            <label for="stok_fisik" class="form-label">Stok Fisik</label>
            <input type="number" class="form-control" id="stok_fisik" name="stok_fisik" step="0.01" min="0" required>
          </div>
          <div class="col-md-1">
            <label for="selisih" class="form-label">Selisih</label>
            <input type="number" class="form-control" id="selisih" step="1" readonly>
          </div>
          <div class="col-md-2">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea class="form-control" id="keterangan" name="keterangan" rows="1"></textarea>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" id="btnSave" class="btn btn-success">Tambah Barang</button>
          </div>
        </div>
      </form>

      <hr>

      <h5 class="mb-3">Daftar Barang</h5>
      <div class="table-responsive">
        <table class="table table-striped" id="added-barang-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Barang</th>
              <th>Satuan</th>
              <th>Stok Sistem</th>
              <th>Stok Fisik</th>
              <th>Selisih</th>
              <th>Keterangan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-end mt-4">
        <button type="button" class="btn btn-primary" id="finish-opname">Selesai</button>
      </div>
    </div>
  </div>
</div>

<script>
// Catch unhandled promise rejections to prevent errors from external scripts
window.addEventListener('unhandledrejection', function(event) {
  console.warn('Unhandled promise rejection caught:', event.reason);
  event.preventDefault();
});

$(document).ready(function() {

  // Function to bind stok fisik input events
  function bindStokFisikEvents() {
    $('.stok-fisik').off('input').on('input', function() {
      var row = $(this).closest('tr');
      var stokSistem = parseFloat(row.find('td').eq(5).text()) || 0; // Stok sistem column
      var stokFisik = parseFloat($(this).val()) || 0;
      var selisih = stokFisik - stokSistem;
      row.find('.selisih').val(selisih);
    });
  }

  // Initial bind
  bindStokFisikEvents();

  // Initialize autocomplete for barang search
  $('#barang_search').autocomplete({
    source: function(request, response) {
      $.ajax({
        url: '/barang/search',
        data: { q: request.term },
        success: function(data) {
          if (data.status === 'success') {
            response(data.data.map(item => ({
              label: `${item.kode_barang} - ${item.nama_barang}`,
              value: item.nama_barang,
              id: item.id
            })));
          }
        }
      });
    },
    minLength: 2,
    select: function(event, ui) {
      $(this).val(ui.item.value);
      $('#barang_id').val(ui.item.id);
      loadBarangInfo(ui.item.id);
      return false;
    }
  });

  // Function to load barang info
  function loadBarangInfo(barangId) {
    $.ajax({
      url: '/barang/' + barangId + '/find',
      success: function(data) {
        if (data.status === true) {
          $('#nama_barang').val(data.data.nama_barang);
          $('#stok_sistem').val(data.data.stok);
          loadSatuanOptions(barangId);
        }
      }
    });
  }

  // Function to load satuan options
  function loadSatuanOptions(barangId) {
    $.ajax({
      url: '/barang/' + barangId + '/satuan',
      success: function(data) {
        if (data.status === 'success') {
          const satuanSelect = $('#satuan_id');
          satuanSelect.empty();
          satuanSelect.append('<option value="">-- Pilih Satuan --</option>');
          // Only load the first satuan (satuan dasar from barang table)
          if (data.data.length > 0) {
            const satuan = data.data[0];
            satuanSelect.append(`<option value="${satuan.satuan_id}">${satuan.nama_satuan}</option>`);
            satuanSelect.val(satuan.satuan_id);
          }
        }
      }
    });
  }

  // Calculate selisih when stok_fisik changes
  $('#stok_fisik').on('input', function() {
    const stokSistem = parseFloat($('#stok_sistem').val()) || 0;
    const stokFisik = parseFloat($(this).val()) || 0;
    const selisih = stokFisik - stokSistem;
    $('#selisih').val(Math.round(selisih));
  });

  // Handle btnSave click for initial form
  $("#btnSave").click(function() {
    $('#opname-form').validate({
      rules: {
        tanggal: {
          required: true
        }
      },
      messages: {
        tanggal: {
          required: "Tanggal opname wajib diisi"
        }
      },
      submitHandler: function(form) {
        $.ajax({
          url: "{{ route('stok-opname.store') }}",
          type: "POST",
          data: $(form).serialize(),
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              // Hide initial form and show barang form
              $('#initial-form').addClass('d-none');
              $('#barang-form').removeClass('d-none');
              // Set stok_opname_id in hidden input
              $('#stok_opname_id').val(response.stok_opname_id);
            });
          },
          error: function(xhr) {
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: 'Terjadi kesalahan saat menyimpan data.'
            });
          }
        });
      }
    });
  });

  // Handle add barang form submit
  $('#add-barang-form').on('submit', function(e) {
    e.preventDefault();
    const stokOpnameId = $('#stok_opname_id').val();

    $.ajax({
      url: "{{ route('stok-opname.add-detail', ':id') }}".replace(':id', stokOpnameId),
      type: "POST",
      data: $(this).serialize(),
      success: function(response) {
        if (response.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: response.message
          });
          // Add to table
          addToBarangTable(response.data);
          // Reset form
          $('#add-barang-form')[0].reset();
          $('#satuan_id').empty().append('<option value="">-- Pilih Satuan --</option>');
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: response.message
          });
        }
      },
      error: function(xhr) {
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: 'Terjadi kesalahan saat menambah barang.'
        });
      }
    });
  });

  // Function to add item to barang table
  function addToBarangTable(detail) {
    const rowHtml = `
      <tr data-id="${detail.id}">
        <td>${$('#added-barang-table tbody tr').length + 1}</td>
        <td>${detail.barang.nama_barang}</td>
        <td>${detail.satuan.nama_satuan}</td>
        <td>${parseFloat(detail.stok_sistem).toFixed(2)}</td>
        <td>${parseFloat(detail.stok_fisik).toFixed(2)}</td>
        <td>${parseFloat(detail.selisih).toFixed(2)}</td>
        <td>${detail.keterangan || '-'}</td>
        <td>
          <button type="button" class="btn btn-danger btn-sm delete-detail" data-id="${detail.id}">
            <i class="fas fa-trash"></i>
          </button>
        </td>
      </tr>
    `;
    $('#added-barang-table tbody').append(rowHtml);
  }

  // Handle delete detail
  $(document).on('click', '.delete-detail', function() {
    const detailId = $(this).data('id');
    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: "Data ini akan dihapus permanen!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, Hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "{{ route('stok-opname.delete-detail', ':detailId') }}".replace(':detailId', detailId),
          type: "DELETE",
          data: {
            _token: '{{ csrf_token() }}'
          },
          success: function(response) {
            if (response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: response.message
              });
              $(`tr[data-id="${detailId}"]`).remove();
              // Re-number rows
              $('#added-barang-table tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: response.message
              });
            }
          },
          error: function(xhr) {
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: 'Terjadi kesalahan saat menghapus data.'
            });
          }
        });
      }
    });
  });

  // Handle finish opname
  $('#finish-opname').on('click', function() {
    const stokOpnameId = $('#stok_opname_id').val();
    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: "Stok opname akan diselesaikan dan stok barang akan disesuaikan!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, Selesai!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "{{ route('stok-opname.finish', ':id') }}".replace(':id', stokOpnameId),
          type: "POST",
          data: {
            _token: '{{ csrf_token() }}'
          },
          success: function(response) {
            if (response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: response.message
              }).then(function() {
                window.location.href = "{{ route('stok-opname.index') }}";
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: response.message
              });
            }
          },
          error: function(xhr) {
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: 'Terjadi kesalahan saat menyelesaikan opname.'
            });
          }
        });
      }
    });
  });
});
</script>

@include('layout.footer')
