@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Tambah Supplier</h3>
  <div class="card p-4">
    <form action="{{ route('supplier.store') }}" method="POST">
      @csrf

      <div class="row">
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="kode_supplier">Kode Supplier *</label>
            <input type="text" name="kode_supplier" id="kode_supplier" class="form-control" value="{{ old('kode_supplier') }}" required>
            @error('kode_supplier')
              <div class="text-danger">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="nama_supplier">Nama Supplier *</label>
            <input type="text" name="nama_supplier" id="nama_supplier" class="form-control" value="{{ old('nama_supplier') }}" required>
            @error('nama_supplier')
              <div class="text-danger">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="kontak_person">Kontak Person</label>
            <input type="text" name="kontak_person" id="kontak_person" class="form-control" value="{{ old('kontak_person') }}">
            @error('kontak_person')
              <div class="text-danger">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="telepon">Telepon</label>
            <input type="text" name="telepon" id="telepon" class="form-control" value="{{ old('telepon') }}">
            @error('telepon')
              <div class="text-danger">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
            @error('email')
              <div class="text-danger">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="status">Status *</label>
            <select name="status" id="status" class="form-control" required>
              <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
              <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            @error('status')
              <div class="text-danger">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <div class="form-group mb-3">
        <label for="alamat">Alamat</label>
        <textarea name="alamat" id="alamat" class="form-control" rows="3">{{ old('alamat') }}</textarea>
        @error('alamat')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="kota">Kota</label>
            <input type="text" name="kota" id="kota" class="form-control" value="{{ old('kota') }}">
            @error('kota')
              <div class="text-danger">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="provinsi">Provinsi</label>
            <input type="text" name="provinsi" id="provinsi" class="form-control" value="{{ old('provinsi') }}">
            @error('provinsi')
              <div class="text-danger">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="{{ route('supplier.index') }}" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>

@include('layout.footer')
