@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Edit Harga Barang</h3>
  <div class="card p-4">
    <form action="{{ route('harga-barang.update', $hargaBarang->id) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="barang_id">Barang</label>
            <select name="barang_id" id="barang_id" class="form-control" required>
              <option value="">-- Pilih Barang --</option>
              @if(isset($barang))
                @foreach($barang as $b)
                  <option value="{{ $b->id }}" {{ $hargaBarang->barang_id == $b->id ? 'selected' : '' }}>{{ $b->kode_barang }} - {{ $b->nama_barang }}</option>
                @endforeach
              @endif
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="satuan_id">Satuan</label>
            <select name="satuan_id" id="satuan_id" class="form-control" required>
              <option value="">-- Pilih Satuan --</option>
              @if(isset($satuan))
                @foreach($satuan as $s)
                  <option value="{{ $s->id }}" {{ $hargaBarang->satuan_id == $s->id ? 'selected' : '' }}>{{ $s->nama_satuan }}</option>
                @endforeach
              @endif
            </select>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="tipe_harga">Tipe Harga</label>
            <select name="tipe_harga" id="tipe_harga" class="form-control" required>
              <option value="ecer" {{ $hargaBarang->tipe_harga == 'ecer' ? 'selected' : '' }}>Ecer</option>
              <option value="grosir" {{ $hargaBarang->tipe_harga == 'grosir' ? 'selected' : '' }}>Grosir</option>
              <option value="member" {{ $hargaBarang->tipe_harga == 'member' ? 'selected' : '' }}>Member</option>
              <option value="promo" {{ $hargaBarang->tipe_harga == 'promo' ? 'selected' : '' }}>Promo</option>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="harga">Harga</label>
            <input type="number" step="0.01" name="harga" id="harga" class="form-control" value="{{ $hargaBarang->harga }}" required>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control" required>
              <option value="aktif" {{ $hargaBarang->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
              <option value="nonaktif" {{ $hargaBarang->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="{{ route('harga-barang.index') }}" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>

@include('layout.footer')
