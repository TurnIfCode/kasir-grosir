@include('layout.header')

<style>
  .dashboard-header {
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    color: #333;
    margin-bottom: 2rem;
  }

  .summary-card {
    background: white;
    border: none;
    border-radius: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 1.5rem;
    text-align: center;
    transition: transform 0.2s ease;
  }

  .summary-card:hover {
    transform: translateY(-2px);
  }

  .summary-card .icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    opacity: 0.8;
  }

  .summary-card .value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
  }

  .summary-card .label {
    font-size: 0.875rem;
    color: #666;
    font-weight: 500;
  }

  .chart-card {
    background: white;
    border: none;
    border-radius: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .table-card {
    background: white;
    border: none;
    border-radius: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .notification-panel {
    position: fixed;
    top: 80px;
    right: 20px;
    width: 300px;
    z-index: 1000;
  }

  .notification {
    background: white;
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 1rem;
    margin-bottom: 0.5rem;
  }

  .notification.warning {
    border-left: 4px solid #ffc107;
  }

  .notification.info {
    border-left: 4px solid #17a2b8;
  }

  /* Desktop */
  @media (min-width: 992px) {
    .charts-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
    }

    .tables-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
    }
  }

  /* Tablet */
  @media (min-width: 768px) and (max-width: 991px) {
    .summary-cards {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
    }

    .charts-row {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }

    .tables-row {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }
  }

  /* Mobile */
  @media (max-width: 767px) {
    .summary-cards {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1rem;
    }

    .charts-row {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }

    .tables-row {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }

    .notification-panel {
      width: calc(100% - 40px);
      right: 10px;
      left: 10px;
    }
  }
</style>

<div class="container-fluid">
  <!-- Header -->
  <div class="row">
    <div class="col-12">
      <h1 class="dashboard-header">Dashboard</h1>
    </div>
  </div>

  <!-- Notifikasi -->
  @if(count($notifikasi) > 0)
  <div class="notification-panel">
    @foreach($notifikasi as $notif)
    <div class="notification {{ $notif['type'] }}">
      <div class="d-flex align-items-center">
        <i class="fas fa-{{ $notif['type'] == 'warning' ? 'exclamation-triangle' : 'info-circle' }} me-2"></i>
        <span>{{ $notif['message'] }}</span>
      </div>
    </div>
    @endforeach
  </div>
  @endif

  <!-- Ringkasan Angka Utama -->
  <div class="row summary-cards">
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
      <div class="summary-card">
        <div class="icon text-primary"><i class="fas fa-cash-register"></i></div>
        <div class="value text-success">Rp {{ number_format($totalPenjualanHariIni, 0, ',', '.') }}</div>
        <div class="label">Total Penjualan Hari Ini</div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
      <div class="summary-card">
        <div class="icon text-success"><i class="fas fa-chart-line"></i></div>
        <div class="value text-success">Rp {{ number_format($labaKotorHariIni, 0, ',', '.') }}</div>
        <div class="label">Laba Kotor Hari Ini</div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
      <div class="summary-card">
        <div class="icon text-info"><i class="fas fa-boxes"></i></div>
        <div class="value">{{ number_format($totalBarangTerjual) }}</div>
        <div class="label">Barang Terjual</div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
      <div class="summary-card">
        <div class="icon text-warning"><i class="fas fa-shopping-cart"></i></div>
        <div class="value text-danger">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</div>
        <div class="label">Total Pembelian</div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
      <div class="summary-card">
        <div class="icon text-secondary"><i class="fas fa-user-tie"></i></div>
        <div class="value">{{ $kasirAktif }}</div>
        <div class="label">Kasir Aktif</div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
      <div class="summary-card">
        <div class="icon text-primary"><i class="fas fa-wallet"></i></div>
        <div class="value">Rp {{ number_format($saldoKas, 0, ',', '.') }}</div>
        <div class="label">Saldo Kas</div>
      </div>
    </div>
  </div>

  <!-- Grafik Tengah -->
  <div class="charts-row">
    <!-- Grafik Penjualan 7 Hari Terakhir -->
    <div class="chart-card">
      <h5 class="mb-3">Grafik Penjualan 7 Hari Terakhir</h5>
      <div class="chart-container" style="position: relative; height: 300px;">
        <canvas id="salesChart"></canvas>
      </div>
    </div>

    <!-- Grafik Perbandingan Penjualan vs Pembelian -->
    <div class="chart-card">
      <h5 class="mb-3">Penjualan vs Pembelian Bulan Ini</h5>
      <div class="chart-container" style="position: relative; height: 300px;">
        <canvas id="comparisonChart"></canvas>
      </div>
    </div>

    <!-- Pie Chart Kategori Penjualan -->
    <div class="chart-card">
      <h5 class="mb-3">Kategori Penjualan Hari Ini</h5>
      <div class="chart-container" style="position: relative; height: 300px;">
        <canvas id="categoryChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Tabel Bawah -->
  <div class="tables-row">
    <!-- 5 Barang Paling Laku Hari Ini -->
    <div class="table-card">
      <h5 class="mb-3">5 Barang Paling Laku Hari Ini</h5>
      <div class="table-responsive">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Nama Barang</th>
              <th>Total Terjual</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($topBarangLaku as $item)
              <tr>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ number_format($item->total_terjual) }}</td>
              </tr>
            @empty
              <tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- 5 Barang Tidak Laku Minggu Ini -->
    <div class="table-card">
      <h5 class="mb-3">5 Barang Tidak Laku Minggu Ini</h5>
      <div class="table-responsive">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Nama Barang</th>
              <th>Total Terjual</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($barangTidakLaku as $item)
              <tr>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ number_format($item->total_terjual) }}</td>
              </tr>
            @empty
              <tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Barang Hampir Habis -->
    <div class="table-card">
      <h5 class="mb-3">Barang Hampir Habis</h5>
      <div class="table-responsive">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Kode Barang</th>
              <th>Nama Barang</th>
              <th>Stok</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($barangHampirHabis as $b)
              <tr>
                <td>{{ $b->kode_barang }}</td>
                <td>{{ $b->nama_barang }}</td>
                <td>{{ round($b->stok) }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted">Semua stok aman</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pengeluaran Hari Ini -->
    <div class="table-card">
      <h5 class="mb-3">Pengeluaran Hari Ini</h5>
      <div class="table-responsive">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Keterangan</th>
              <th>Nominal</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($pengeluaranHariIni as $pengeluaran)
              <tr>
                <td>{{ $pengeluaran->keterangan }}</td>
                <td>Rp {{ number_format($pengeluaran->nominal, 0, ',', '.') }}</td>
              </tr>
            @empty
              <tr><td colspan="2" class="text-center text-muted">Tidak ada pengeluaran</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@section('scripts')
<script>
  // Grafik Penjualan 7 Hari Terakhir (Line Chart)
  const salesCtx = document.getElementById('salesChart').getContext('2d');
  const salesLabels = @json($grafikPenjualan7Hari->pluck('tanggal'));
  const salesData = @json($grafikPenjualan7Hari->pluck('total'));

  new Chart(salesCtx, {
    type: 'line',
    data: {
      labels: salesLabels,
      datasets: [{
        label: 'Penjualan (Rp)',
        data: salesData,
        borderColor: '#007bff',
        backgroundColor: 'rgba(0, 123, 255, 0.1)',
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return 'Rp ' + value.toLocaleString('id-ID');
            }
          }
        }
      },
      plugins: {
        legend: {
          display: true,
          labels: {
            font: {
              size: 14,
              family: 'Inter'
            }
          }
        }
      }
    }
  });

  // Grafik Perbandingan Penjualan vs Pembelian (Bar Chart)
  const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
  const comparisonData = {
    labels: ['Bulan Ini'],
    datasets: [{
      label: 'Penjualan',
      data: [@json($penjualanBulanIni)],
      backgroundColor: '#28a745'
    }, {
      label: 'Pembelian',
      data: [@json($pembelianBulanIni)],
      backgroundColor: '#dc3545'
    }]
  };

  new Chart(comparisonCtx, {
    type: 'bar',
    data: comparisonData,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return 'Rp ' + value.toLocaleString('id-ID');
            }
          }
        }
      },
      plugins: {
        legend: {
          display: true,
          labels: {
            font: {
              size: 14,
              family: 'Inter'
            }
          }
        }
      }
    }
  });

  // Pie Chart Kategori Penjualan
  const categoryCtx = document.getElementById('categoryChart').getContext('2d');
  const categoryLabels = @json($kategoriPenjualan->pluck('nama_kategori'));
  const categoryData = @json($kategoriPenjualan->pluck('total'));

  new Chart(categoryCtx, {
    type: 'pie',
    data: {
      labels: categoryLabels,
      datasets: [{
        data: categoryData,
        backgroundColor: [
          '#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#17a2b8'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            font: {
              size: 12,
              family: 'Inter'
            }
          }
        }
      }
    }
  });
</script>
@endsection

@include('layout.footer')
