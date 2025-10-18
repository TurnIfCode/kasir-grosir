@include('layout.header')

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Detail Stok Opname</h2>
    <a href="{{ route('stok-opname.index') }}" class="btn btn-secondary">Kembali</a>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <p><strong>Kode Opname:</strong> {{ $stokOpname->kode_opname }}</p>
          <p><strong>Tanggal:</strong> {{ $stokOpname->tanggal->format('d/m/Y') }}</p>
          <p><strong>User:</strong> {{ $stokOpname->user->name }}</p>
        </div>
        <div class="col-md-6">
          <p><strong>Status:</strong>
            <span class="badge
              @if($stokOpname->status == 'draft') bg-secondary
              @elseif($stokOpname->status == 'selesai') bg-success
              @else bg-danger
              @endif">
              {{ ucfirst($stokOpname->status) }}
            </span>
          </p>
          @if($stokOpname->catatan)
            <p><strong>Catatan:</strong> {{ $stokOpname->catatan }}</p>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5 class="mb-3">Detail Barang</h5>
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
            @forelse($stokOpname->details as $detail)
              <tr>
                <td>{{ $detail->barang->kode_barang }}</td>
                <td>{{ $detail->barang->nama_barang }}</td>
                <td>{{ round($detail->stok_sistem) }}</td>
                <td>{{ round($detail->stok_fisik) }}</td>
                <td class="{{ $detail->selisih < 0 ? 'text-danger' : ($detail->selisih > 0 ? 'text-success' : '') }}">
                  {{ round($detail->selisih) }}
                </td>
                <td>{{ $detail->keterangan }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center">Tidak ada data detail</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@include('layout.footer')
