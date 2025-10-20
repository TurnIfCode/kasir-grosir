@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Profil Toko</h3>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="card p-4">
    <form method="POST" action="{{ route('profil-toko.update', $profilToko->id) }}" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="nama_toko" class="form-label">Nama Toko*</label>
            <input type="text" class="form-control" id="nama_toko" name="nama_toko" value="{{ old('nama_toko', $profilToko->nama_toko) }}" required>
          </div>

          <div class="mb-3">
            <label for="slogan" class="form-label">Slogan</label>
            <input type="text" class="form-control" id="slogan" name="slogan" value="{{ old('slogan', $profilToko->slogan) }}">
          </div>

          <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea class="form-control" id="alamat" name="alamat" rows="3">{{ old('alamat', $profilToko->alamat) }}</textarea>
          </div>

          <div class="mb-3">
            <label for="kota" class="form-label">Kota</label>
            <input type="text" class="form-control" id="kota" name="kota" value="{{ old('kota', $profilToko->kota) }}">
          </div>

          <div class="mb-3">
            <label for="provinsi" class="form-label">Provinsi</label>
            <input type="text" class="form-control" id="provinsi" name="provinsi" value="{{ old('provinsi', $profilToko->provinsi) }}">
          </div>

          <div class="mb-3">
            <label for="kode_pos" class="form-label">Kode Pos</label>
            <input type="text" class="form-control" id="kode_pos" name="kode_pos" value="{{ old('kode_pos', $profilToko->kode_pos) }}">
          </div>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            <label for="no_telp" class="form-label">No. Telepon</label>
            <input type="text" class="form-control" id="no_telp" name="no_telp" value="{{ old('no_telp', $profilToko->no_telp) }}">
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $profilToko->email) }}">
          </div>

          <div class="mb-3">
            <label for="website" class="form-label">Website</label>
            <input type="text" class="form-control" id="website" name="website" value="{{ old('website', $profilToko->website) }}">
          </div>

          <div class="mb-3">
            <label for="npwp" class="form-label">NPWP</label>
            <input type="text" class="form-control" id="npwp" name="npwp" value="{{ old('npwp', $profilToko->npwp) }}">
          </div>

          <div class="mb-3">
            <label for="logo" class="form-label">Logo Toko</label>
            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
            @if(file_exists(public_path('assets/images/logo/logo.png')))
              <div class="mt-2">
                <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo Toko" height="100" class="img-thumbnail">
              </div>
            @endif
          </div>

          <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $profilToko->deskripsi) }}</textarea>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
  // Optional: Add any client-side validation or enhancements here
});
</script>
@endsection

@include('layout.footer')
