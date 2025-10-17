@include('layout.header')

<div class="container-fluid">
  <!-- === Ringkasan Angka Utama === -->
  <div class="row g-3">
    <div class="col-md-3">
      <div class="card text-center p-3 shadow-sm">
        <h6>Total Penjualan (Hari Ini)</h6>
        <h4 class="text-success">Rp {{ number_format($totalPenjualanHariIni, 0, ',', '.') }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center p-3 shadow-sm">
        <h6>Total Pembelian (Hari Ini)</h6>
        <h4 class="text-danger">Rp {{ number_format($totalPembelianHariIni, 0, ',', '.') }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center p-3 shadow-sm">
        <h6>Barang Terjual (Hari Ini)</h6>
        <h4>{{ number_format($totalBarangTerjual) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center p-3 shadow-sm">
        <h6>Barang Aktif</h6>
        <h4>{{ number_format($totalBarangAktif) }}</h4>
      </div>
    </div>
  </div>

  <!-- === Grafik Penjualan Bulanan === -->
  <div class="card mt-4 p-3 shadow-sm">
    <h5 class="mb-3">Grafik Penjualan 7 Hari Terakhir</h5>
    <canvas id="salesChart" height="120"></canvas>
  </div>

  <!-- === Top Barang Paling Laris === -->
  <div class="card mt-4 p-3 shadow-sm">
    <h5 class="mb-3">Top 5 Barang Paling Laris</h5>
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>Nama Barang</th>
          <th>Total Terjual</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($topBarang as $item)
          <tr>
            <td>{{ $item->nama_barang }}</td>
            <td>{{ number_format($item->total_terjual) }}</td>
          </tr>
        @empty
          <tr><td colspan="2" class="text-center">Tidak ada data</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- === Barang Hampir Habis === -->
  <div class="card mt-4 p-3 shadow-sm">
    <h5 class="mb-3">Barang Hampir Habis (Stok ≤ 5)</h5>
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>Kode Barang</th>
          <th>Nama Barang</th>
          <th>Stok</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($stokMenipis as $b)
          <tr>
            <td>{{ $b->kode_barang }}</td>
            <td>{{ $b->nama_barang }}</td>
            <td>{{ $b->stok }}</td>
          </tr>
        @empty
          <tr><td colspan="3" class="text-center">Semua stok aman</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- === Transaksi Terbaru === -->
  <div class="card mt-4 p-3 shadow-sm mb-5">
    <h5 class="mb-3">Transaksi Penjualan Terbaru</h5>
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>Kode Penjualan</th>
          <th>Tanggal</th>
          <th>Total</th>
          <th>Dibuat Oleh</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($transaksiTerbaru as $trx)
          <tr>
            <td>{{ $trx->kode_penjualan }}</td>
            <td>{{ \Carbon\Carbon::parse($trx->tanggal_penjualan)->format('d/m/Y') }}</td>
            <td>Rp {{ number_format($trx->grand_total, 0, ',', '.') }}</td>
            <td>{{ $trx->created_by }}</td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-center">Belum ada transaksi</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@section('scripts')
<script>
  $(document).ready(function() {
    $('.collapse').on('show.bs.collapse', function() {
      $(this).parent().find('.dropdown-toggle').addClass('active');
    }).on('hide.bs.collapse', function() {
      $(this).parent().find('.dropdown-toggle').removeClass('active');
    });

    $('#sidebarToggle').click(function() {
      $('.sidebar').toggle();
    });
  });

  // === Data untuk grafik Chart.js ===
  const ctx = document.getElementById('salesChart').getContext('2d');
  const labels = @json($grafikPenjualan->pluck('tanggal'));
  const data = @json($grafikPenjualan->pluck('total'));

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Total Penjualan (Rp)',
        data: data,
        backgroundColor: '#007bff',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return 'Rp ' + value.toLocaleString('id-ID');
            }
          }
        }
      }
    }
  });
</script>
@endsection

@include('layout.footer')
