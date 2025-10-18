@include('layout.header')

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Tambah Stok Opname</h2>
    <a href="{{ route('stok-opname.index') }}" class="btn btn-secondary">Kembali</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('stok-opname.store') }}" method="POST">
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

        <h5 class="mb-3">Data Barang</h5>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Stok Sistem</th>
                <th>Stok Fisik</th>
                <th>Selisih</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody>
              @foreach($barangs as $barang)
                <tr>
                  <td>{{ $barang->kode_barang }}</td>
                  <td>{{ $barang->nama_barang }}</td>
                  <td>{{ round($barang->stok) }}</td>
                  <td>
                    <input type="number" class="form-control stok-fisik" name="barang[{{ $barang->id }}][stok_fisik]" step="1" min="0" required>
                  </td>
                  <td>
                    <input type="number" class="form-control selisih" readonly step="1">
                  </td>
                  <td>
                    <input type="text" class="form-control" name="barang[{{ $barang->id }}][keterangan]">
                  </td>
                  <input type="hidden" name="barang[{{ $barang->id }}][stok_sistem]" value="{{ $barang->stok }}">
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end mt-4">
          <button type="submit" class="btn btn-primary">Simpan sebagai Draft</button>
        </div>
      </form>
    </div>
  </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
  $('.stok-fisik').on('input', function() {
    var row = $(this).closest('tr');
    var stokSistem = parseFloat(row.find('input[name*="[stok_sistem]"]').val()) || 0;
    var stokFisik = parseFloat($(this).val()) || 0;
    var selisih = Math.round(stokFisik - stokSistem);
    row.find('.selisih').val(selisih);
  });
});
</script>
@endsection

@include('layout.footer')
