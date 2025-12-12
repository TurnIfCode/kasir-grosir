@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Tambah Saldo Kas</h3>
  <div class="card p-4">
    <form id="addKasSaldoForm" action="#" method="POST">
      @csrf
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="sumber_kas" class="form-label">Sumber Kas*</label>
            <input type="text" class="form-control" id="sumber_kas" name="sumber_kas" placeholder="Contoh: Kas Tunai, Bank BCA" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="saldo_awal" class="form-label">Saldo Awal</label>
            <input type="number" step="0.01" class="form-control" id="saldo_awal" name="saldo_awal" value="0">
          </div>
        </div>
      </div>
      <button type="submit" id="btnSave" class="btn btn-primary">Simpan</button>
      <a href="{{ route('kas-saldo.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
  </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
  $("#btnSave").click(function() {
    $('#addKasSaldoForm').validate({
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
        $.ajax({
          url: "{{ route('kas-saldo.store') }}",
          type: "POST",
          data: $(form).serialize(),
          success: function(response) {
            if (response.success === true) {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: response.message
              }).then(function() {
                window.location.href = "{{ route('kas-saldo.index') }}";
              });
            } else {
              $('#' + response.form).focus().select();
              Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: response.message
              });
            }
          },
          error: function(xhr) {
            console.log(xhr.responseText);
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: 'Terjadi kesalahan pada server.'
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
