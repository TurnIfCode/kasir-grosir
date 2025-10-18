@include('layout.header')

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Daftar Stok Opname</h2>
    <a href="{{ route('stok-opname.create') }}" class="btn btn-primary">Tambah Stok Opname</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Kode Opname</th>
              <th>Tanggal</th>
              <th>User</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($stokOpnames as $opname)
              <tr>
                <td>{{ $opname->kode_opname }}</td>
                <td>{{ $opname->tanggal->format('d/m/Y') }}</td>
                <td>{{ $opname->user->name }}</td>
                <td>
                  <span class="badge
                    @if($opname->status == 'draft') bg-secondary
                    @elseif($opname->status == 'selesai') bg-success
                    @else bg-danger
                    @endif">
                    {{ ucfirst($opname->status) }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('stok-opname.show', $opname->id) }}" class="btn btn-sm btn-info">Lihat</a>
                  @if($opname->status == 'draft')
                    <form action="{{ route('stok-opname.updateStatus', $opname->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('POST')
                      <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Apakah Anda yakin ingin menyelesaikan stok opname ini?')">Selesai</button>
                    </form>
                    <form action="{{ route('stok-opname.destroy', $opname->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus stok opname ini?')">Hapus</button>
                    </form>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center">Belum ada data stok opname</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $stokOpnames->links() }}
    </div>
  </div>
</div>

@include('layout.footer')
