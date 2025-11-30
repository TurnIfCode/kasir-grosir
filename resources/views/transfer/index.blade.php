@include('layout.header')
<div class="container-fluid">
  <h3 class="mb-4">Data Transfer</h3>
  <div class="card p-4">
    <div class="d-flex justify-content-between mb-3">
      <h5>Data Transfer</h5>
      <a href="{{ route('transfer.add') }}" class="btn btn-primary">Tambah Transfer</a>
    </div>
    <div class="row mb-3">
      <div class="col-md-3">
        <label for="start_date">Tanggal Awal</label>
        <input type="date" id="start_date" class="form-control" value="{{ date('Y-m-d') }}">
      </div>
      <div class="col-md-3">
        <label for="end_date">Tanggal Akhir</label>
        <input type="date" id="end_date" class="form-control" value="{{ date('Y-m-d') }}">
      </div>
    </div>
    <table id="transferTable" class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>ID</th>
          <th>Bank Asal</th>
          <th>Bank Tujuan</th>
          <th>Nominal Transfer</th>
          <th>Admin Bank</th>
          <th>Grand Total</th>
          <th>Catatan</th>
          <th>Dibuat Oleh</th>
          <th>Tanggal Dibuat</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>



<script>
$(document).ready(function() {
  var table = $('#transferTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "{{ route('transfer.data') }}",
      type: "GET",
      data: function(d) {
        d.start_date = $('#start_date').val();
        d.end_date = $('#end_date').val();
      }
    },
    columns: [
      { data: 'id' },
      { data: 'bank_asal' },
      { data: 'bank_tujuan' },
      { data: 'nominal_transfer' },
      { data: 'admin_bank' },
      { data: 'grand_total' },
      { data: 'catatan' },
      { data: 'created_by' },
      { data: 'created_at' }
    ]
  });

  // Reload table on date change
  $('#start_date, #end_date').on('change', function() {
    table.ajax.reload();
  });

  // Edit button click
  $('#transferTable').on('click', '#btnEdit', function() {
    var id = $(this).data('id');
    $.ajax({
      url: "{{ url('/transfer') }}/" + id + "/find",
      type: "GET",
      success: function(response) {
        if (response.status) {
          $('#editId').val(response.data.id);
          $('#editBankAsal').val(response.data.bank_asal);
          $('#editBankTujuan').val(response.data.bank_tujuan);
          $('#editNominalTransfer').val(response.data.nominal_transfer);
          $('#editAdminBank').val(response.data.admin_bank);
          $('#editCatatan').val(response.data.catatan);
          $('#editModal').modal('show');
        }
      }
    });
  });

  // Update button click
  $('#btnUpdate').click(function() {
    $('#editTransferForm').validate({
      rules: {
        bank_tujuan: {
          required: true
        },
        nominal_transfer: {
          required: true,
          number: true,
          min: 0
        },
        admin_bank: {
          required: true,
          number: true,
          min: 0
        }
      },
      messages: {
        bank_tujuan: {
          required: "Bank tujuan wajib diisi"
        },
        nominal_transfer: {
          required: "Nominal transfer wajib diisi",
          number: "Nominal transfer harus berupa angka",
          min: "Nominal transfer tidak boleh negatif"
        },
        admin_bank: {
          required: "Admin bank wajib diisi",
          number: "Admin bank harus berupa angka",
          min: "Admin bank tidak boleh negatif"
        }
      },
      submitHandler: function(form) {
        var id = $('#editId').val();
        $.ajax({
          url: "{{ url('/transfer') }}/" + id + "/update",
          type: "PUT",
          data: $(form).serialize(),
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            });
            $('#editModal').modal('hide');
            table.ajax.reload();
          },
          error: function(xhr) {
            var response = xhr.responseJSON;
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: response ? response.message : 'Terjadi kesalahan'
            });
          }
        });
      }
    });
  });

  // Delete button click
  $('#transferTable').on('click', '#btnDelete', function() {
    var id = $(this).data('id');
    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: "Data transfer akan dihapus permanen!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "{{ url('/transfer') }}/" + id + "/delete",
          type: "DELETE",
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            });
            table.ajax.reload();
          },
          error: function(xhr) {
            var response = xhr.responseJSON;
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: response ? response.message : 'Terjadi kesalahan'
            });
          }
        });
      }
    });
  });

  // Detail button click (if needed)
  $('#transferTable').on('click', '#btnDetail', function() {
    var id = $(this).data('id');
    // Implement detail view if needed
    console.log('Detail for ID: ' + id);
  });
});
</script>

@include('layout.footer')
