@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Edit Saldo Kas</h3>
  <div class="card p-4">
    <form id="editKasSaldoForm" action="#" method="POST">
      @csrf
      @method('PUT')
      <input type="hidden" id="saldoId" name="id" value="{{ $saldo->id }}">
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="sumber_kas" class="form-label">Sumber Kas*</label>
            <input type="text" class="form-control" id="sumber_kas" name="sumber_kas" value="{{ $saldo->sumber_kas }}" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="saldo_awal" class="form-label">Saldo Awal</label>
            <input type="number" step="0.01" class="form-control" id="saldo_awal" name="saldo_awal" value="{{ $saldo->saldo_awal }}">
          </div>
        </div>
      </div>
      <button type="submit" id="btnUpdate" class="btn btn-primary">Simpan</button>
      <a href="{{ route('kas-saldo.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
  </div>
</div>

<script>
$(document).ready(function() {
  $("#btnUpdate").click(function() {
    $('#editKasSaldoForm').validate({
      rules: {
        sumber_kas: { required: true },
        saldo_awal: { number: true, min: 0 }
      },
      messages: {
        sumber_kas: { required: "Sumber Kas wajib diisi" },
        saldo_awal: {
          number: "Saldo Awal harus berupa angka",
          min: "Saldo Awal tidak boleh negatif"
        }
      },
      submitHandler: function(form) {
        var saldoId = $('#saldoId').val();
        $.ajax({
          url: "{{ route('kas-saldo.update', $saldo->id) }}",
          type: "POST",
          data: $(form).serialize(),
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              window.location.href = "{{ route('kas-saldo.index') }}";
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
