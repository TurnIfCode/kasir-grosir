@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Tambah Harga Barang</h3>
  <div class="card p-4">
    <form id="hargaForm" action="{{ route('harga-barang.store') }}" method="POST">
      @csrf

      <div id="hargaRows">
        <div class="harga-row border p-3 mb-3">
          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label for="barang_id_0">Barang</label>
                <select name="harga_data[0][barang_id]" id="barang_id_0" class="form-control" required>
                  <option value="">-- Pilih Barang --</option>
                  @if(isset($barang))
                    @foreach($barang as $b)
                      <option value="{{ $b->id }}">{{ $b->kode_barang }} - {{ $b->nama_barang }}</option>
                    @endforeach
                  @endif
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label for="satuan_id_0">Satuan</label>
                <select name="harga_data[0][satuan_id]" id="satuan_id_0" class="form-control" required>
                  <option value="">-- Pilih Satuan --</option>
                  @if(isset($satuan))
                    @foreach($satuan as $s)
                      <option value="{{ $s->id }}">{{ $s->nama_satuan }}</option>
                    @endforeach
                  @endif
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label for="tipe_harga_0">Tipe Harga</label>
                <select name="harga_data[0][tipe_harga]" id="tipe_harga_0" class="form-control" required>
                  <option value="ecer">Ecer</option>
                  <option value="grosir">Grosir</option>
                  <option value="member">Member</option>
                  <option value="promo">Promo</option>
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label for="harga_0">Harga</label>
                <input type="number" step="0.01" name="harga_data[0][harga]" id="harga_0" class="form-control" placeholder="0.00" required>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label for="status_0">Status</label>
                <select name="harga_data[0][status]" id="status_0" class="form-control" required>
                  <option value="aktif">Aktif</option>
                  <option value="nonaktif">Nonaktif</option>
                </select>
              </div>
            </div>
            <div class="col-md-1">
              <div class="form-group">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-danger btn-sm remove-row" style="display: none;">Hapus</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <button type="button" id="addRow" class="btn btn-secondary mb-3">Tambah Harga Lagi</button>
      <button type="submit" class="btn btn-primary">Simpan Semua</button>
      <a href="{{ route('harga-barang.index') }}" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
  let rowCount = 1;

  $('#addRow').on('click', function() {
    const newRow = `
      <div class="harga-row border p-3 mb-3">
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label for="barang_id_${rowCount}">Barang</label>
              <select name="harga_data[${rowCount}][barang_id]" id="barang_id_${rowCount}" class="form-control" required>
                <option value="">-- Pilih Barang --</option>
                @if(isset($barang))
                  @foreach($barang as $b)
                    <option value="{{ $b->id }}">{{ $b->kode_barang }} - {{ $b->nama_barang }}</option>
                  @endforeach
                @endif
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="satuan_id_${rowCount}">Satuan</label>
              <select name="harga_data[${rowCount}][satuan_id]" id="satuan_id_${rowCount}" class="form-control" required>
                <option value="">-- Pilih Satuan --</option>
                @if(isset($satuan))
                  @foreach($satuan as $s)
                    <option value="{{ $s->id }}">{{ $s->nama_satuan }}</option>
                  @endforeach
                @endif
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="tipe_harga_${rowCount}">Tipe Harga</label>
              <select name="harga_data[${rowCount}][tipe_harga]" id="tipe_harga_${rowCount}" class="form-control" required>
                <option value="ecer">Ecer</option>
                <option value="grosir">Grosir</option>
                <option value="member">Member</option>
                <option value="promo">Promo</option>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="harga_${rowCount}">Harga</label>
              <input type="number" step="0.01" name="harga_data[${rowCount}][harga]" id="harga_${rowCount}" class="form-control" placeholder="0.00" required>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="status_${rowCount}">Status</label>
              <select name="harga_data[${rowCount}][status]" id="status_${rowCount}" class="form-control" required>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
              </select>
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <label>&nbsp;</label>
              <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
            </div>
          </div>
        </div>
      </div>
    `;
    $('#hargaRows').append(newRow);
    rowCount++;
  });

  $(document).on('click', '.remove-row', function() {
    $(this).closest('.harga-row').remove();
  });

  $('#hargaForm').on('submit', function(e) {
    // Basic validation can be added here if needed
  });
});
</script>
@endsection

@include('layout.footer')
