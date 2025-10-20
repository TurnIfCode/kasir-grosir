@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Tambah Transaksi Kas</h3>
  <div class="card p-4">
    <form id="addKasForm" action="#" method="POST">
      @csrf
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="tanggal" class="form-label">Tanggal*</label>
            <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="tipe" class="form-label">Tipe*</label>
            <select class="form-control" id="tipe" name="tipe" required>
              <option value="">Pilih Tipe</option>
              <option value="masuk" {{ request()->get('tipe') == 'masuk' ? 'selected' : '' }}>Masuk</option>
              <option value="keluar" {{ request()->get('tipe') == 'keluar' ? 'selected' : '' }}>Keluar</option>
            </select>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="sumber_kas" class="form-label">Sumber Kas*</label>
            <input type="text" class="form-control" id="sumber_kas" name="sumber_kas" placeholder="Contoh: Kas Tunai, Bank BCA" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="kategori" class="form-label">Kategori</label>
            <input type="text" class="form-control" id="kategori" name="kategori" placeholder="Contoh: Penjualan, Pembelian">
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="nominal" class="form-label">Nominal*</label>
            <input type="number" step="0.01" class="form-control" id="nominal" name="nominal" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Keterangan transaksi">
          </div>
        </div>
      </div>
      <button type="submit" id="btnSave" class="btn btn-primary">Simpan</button>
      <a href="{{ route('kas.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
  </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
  $("#btnSave").click(function() {
    $('#addKasForm').validate({
      rules: {
        tanggal: { required: true },
        tipe: { required: true },
        sumber_kas: { required: true },
        nominal: { required: true, number: true, min: 0.01 }
      },
      messages: {
        tanggal: { required: "Tanggal wajib diisi" },
        tipe: { required: "Tipe wajib dipilih" },
        sumber_kas: { required: "Sumber Kas wajib diisi" },
        nominal: {
          required: "Nominal wajib diisi",
          number: "Nominal harus berupa angka",
          min: "Nominal harus lebih dari 0"
        }
      },
      submitHandler: function(form) {
        $.ajax({
          url: "{{ route('kas.store') }}",
          type: "POST",
          data: $(form).serialize(),
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              window.location.href = "{{ route('kas.index') }}";
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
