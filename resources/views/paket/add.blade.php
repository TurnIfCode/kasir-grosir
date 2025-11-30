@extends('layout.master')

@section('title', 'Tambah Paket')

@section('content')
<div class="container-fluid">
  <h3 class="mb-4">Tambah Paket</h3>

  @if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <form action="{{ route('paket.store') }}" method="POST" id="paketForm">
    @csrf
    <div class="mb-3">
      <label for="nama" class="form-label">Nama Paket</label>
      <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama') }}" required maxlength="100" />
    </div>

    <div class="mb-3">
      <label for="total_qty" class="form-label">Total Qty</label>
      <input type="number" class="form-control" id="total_qty" name="total_qty" value="{{ old('total_qty', 1) }}" required min="1" />
    </div>

    <div class="mb-3">
      <label for="harga" class="form-label">Harga</label>
      <input type="number" class="form-control" id="harga" name="harga" value="{{ old('harga', 0) }}" required min="0" />
    </div>

    <div class="mb-3">
      <label for="status" class="form-label">Status</label>
      <select name="status" id="status" class="form-select" required>
        <option value="aktif" {{ old('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
        <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="barang_ids" class="form-label">Daftar Barang</label>
      
      <!-- Preserve select2 autocomplete used for searching barang -->
      <select id="barang_ids" name="barang_ids[]" class="form-select" multiple="multiple" style="width: 100%;">
        <!-- Options will be loaded by select2 ajax -->
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('paket.index') }}" class="btn btn-secondary">Batal</a>
  </form>
</div>

@section('scripts')
<script>
$(document).ready(function() {
  $('#barang_ids').select2({
    placeholder: 'Cari dan pilih barang',
    minimumInputLength: 2,
    ajax: {
      url: '{{ route('barang.select2') }}',
      dataType: 'json',
      delay: 250,
      data: function(params) {
        return {
          q: params.term
        };
      },
      processResults: function(data) {
        return {
          results: data.items
        };
      },
      cache: true
    }
  });
});
</script>
@endsection

@endsection
