<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Dashboard - GrosirIndo</title>
  <link rel="icon" href="{{ asset('assets/icon/icon.ico') }}">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
  <!-- Google Fonts: Inter -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <!-- jQuery UI CSS -->
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- jQuery UI JS -->
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

  <!-- jQuery Validate -->
  <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8f9fa;
    }

    /* Sidebar */
    .sidebar {
      width: 240px;
      background: white;
      position: fixed;
      top: 0;
      bottom: 0;
      color: black;
      padding-top: 1rem;
      box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
      overflow-y: auto;
      z-index: 1050;
      transition: transform 0.3s ease;
    }

    .sidebar .nav-link {
      color: black;
      text-decoration: none;
      padding: 12px 20px; /* Increased padding for touch */
      border-radius: 5px;
      margin: 2px 10px;
      font-weight: 500;
      font-size: 16px; /* Larger font for touch */
    }

    .sidebar .nav-link.active,
    .sidebar .nav-link:hover {
      background-color: #007bff;
      color: white;
    }

    .sidebar .collapse .nav-link {
      font-size: 15px; /* Slightly larger */
      padding-left: 35px;
    }

    .sidebar .dropdown-toggle::after {
      float: right;
      margin-top: 6px;
      transition: transform 0.3s;
    }

    .sidebar .dropdown-toggle.active::after {
      transform: rotate(90deg);
    }

    /* Main content */
    .main {
      margin-left: 240px;
      padding: 20px;
      transition: margin-left 0.3s ease;
    }

    .navbar-custom {
      background: #007bff;
      color: white;
      height: 60px; /* Taller navbar for touch */
    }

    .navbar-custom i {
      color: white;
    }

    .navbar-custom .btn {
      font-size: 16px; /* Larger buttons */
      padding: 8px 16px;
    }

    .navbar-custom .navbar-brand {
      font-size: 18px;
    }

    .card {
      border: none;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }

    /* Overlay for mobile sidebar */
    .sidebar-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1040;
    }

    /* Tablet: 768px to 1024px */
    @media (min-width: 768px) and (max-width: 1024px) {
      .sidebar {
        width: 200px; /* Slightly narrower for tablets */
      }

      .sidebar .nav-link {
        padding: 10px 15px;
        font-size: 15px;
      }

      .main {
        margin-left: 200px;
      }

      .sidebar-overlay {
        display: none;
      }

      .navbar-custom .btn {
        font-size: 15px;
        padding: 6px 12px;
      }
    }

    /* Mobile: max-width 767px */
    @media (max-width: 767px) {
      .sidebar {
        transform: translateX(-100%);
        width: 280px; /* Wider for mobile overlay */
      }

      .sidebar.show {
        transform: translateX(0);
      }

      .main {
        margin-left: 0;
      }

      .sidebar-overlay {
        display: none;
      }

      .navbar-custom .btn {
        font-size: 16px;
        padding: 10px 16px; /* Larger for touch */
      }

      .navbar-custom .navbar-brand {
        font-size: 16px;
      }

      .sidebar .nav-link {
        padding: 15px 20px; /* Even larger for mobile */
        font-size: 16px;
      }
    }

    .dropdown-menu {
      background: white;
      border: none;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .dropdown-item:hover {
      background: #007bff;
      color: white;
    }

    .error {
      color: red;
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <nav class="sidebar">
    <h4 class="text-center mb-4">
      <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" class="img-fluid" style="max-height: 40px;">
    </h4>

    <ul class="nav flex-column" id="sidebarMenu">

      <li class="nav-item">
        <a class="nav-link" href="{{ url('/dashboard') }}">
          <i class="fa fa-dashboard me-2"></i> Dashboard
        </a>
      </li>

      @if(auth()->user()->role == 'ADMIN')
      <!-- Dropdown: User -->
      <li class="nav-item">
        <a href="#menuUser" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuUser" role="button" aria-expanded="false">
          <i class="fa fa-user me-2"></i> User
        </a>
        <ul class="collapse nav flex-column ms-3" id="menuUser" data-bs-parent="#sidebarMenu">
          <li><a class="nav-link" href="{{ route('user.add') }}">Tambah</a></li>
          <li><a class="nav-link" href="{{ route('user.data') }}">Data</a></li>
        </ul>
      </li>

      <!-- Dropdown: Master Data -->
      <li class="nav-item">
        <a href="#menuMasterData" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuMasterData" role="button" aria-expanded="false">
          <i class="fa fa-database me-2"></i> Master Data
        </a>
        <ul class="collapse nav flex-column ms-3" id="menuMasterData" data-bs-parent="#sidebarMenu">

          <!-- Kategori -->
          <li class="nav-item">
            <a href="#menuKategori" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuKategori" role="button" aria-expanded="false">
              Kategori
            </a>
            <ul class="collapse nav flex-column ms-3" id="menuKategori" data-bs-parent="#menuMasterData">
              <li><a class="nav-link" href="{{ route('kategori.add') }}">Tambah</a></li>
              <li><a class="nav-link" href="{{ route('kategori.data') }}">Data</a></li>
            </ul>
          </li>
          <!-- Satuan -->
          <li class="nav-item">
            <a href="#menuSatuan" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuSatuan" role="button" aria-expanded="false">
              Satuan
            </a>
            <ul class="collapse nav flex-column ms-3" id="menuSatuan" data-bs-parent="#menuMasterData">
              <li><a class="nav-link" href="{{ route('satuan.add') }}">Tambah</a></li>
              <li><a class="nav-link" href="{{ route('satuan.data') }}">Data</a></li>
            </ul>
          </li>
          <!-- Barang -->
          <li class="nav-item">
            <a href="#menuBarang" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuBarang" role="button" aria-expanded="false">
              Barang
            </a>
            <ul class="collapse nav flex-column ms-3" id="menuBarang" data-bs-parent="#menuMasterData">
              <li><a class="nav-link" href="{{ route('barang.add') }}">Tambah</a></li>
              <li><a class="nav-link" href="{{ route('barang.data') }}">Data</a></li>
            </ul>
          </li>
          <!-- Konversi Satuan -->
          <li class="nav-item">
            <a href="#menuKonversiSatuan" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuKonversiSatuan" role="button" aria-expanded="false">
              Konversi Satuan
            </a>
            <ul class="collapse nav flex-column ms-3" id="menuKonversiSatuan" data-bs-parent="#menuMasterData">
              <li><a class="nav-link" href="{{ route('konversi-satuan.add') }}">Tambah</a></li>
              <li><a class="nav-link" href="{{ route('konversi-satuan.data') }}">Data</a></li>
            </ul>
          </li>
          <!-- Harga Barang -->
          <li class="nav-item">
            <a href="#menuHargaBarang" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuHargaBarang" role="button" aria-expanded="false">
              Harga Barang
            </a>
            <ul class="collapse nav flex-column ms-3" id="menuHargaBarang" data-bs-parent="#menuMasterData">
              <li><a class="nav-link" href="{{ route('harga-barang.create') }}">Tambah</a></li>
              <li><a class="nav-link" href="{{ route('harga-barang.index') }}">Data</a></li>
            </ul>
          </li>
          <!-- Jenis Barang -->
          <li class="nav-item">
            <a href="#menuJenisBarang" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuJenisBarang" role="button" aria-expanded="false">
              Jenis Barang
            </a>
            <ul class="collapse nav flex-column ms-3" id="menuJenisBarang" data-bs-parent="#menuMasterData">
              <li><a class="nav-link" href="{{ route('jenis_barang.add') }}">Tambah</a></li>
              <li><a class="nav-link" href="{{ route('jenis_barang.data') }}">Data</a></li>
            </ul>
          </li>

        </ul>
      </li>
          <!-- Supplier -->
          <li class="nav-item">
            <a href="#menuSupplier" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuSupplier" role="button" aria-expanded="false">
              <i class="fas fa-truck"></i> Supplier
            </a>
        <ul class="collapse nav flex-column ms-3" id="menuSupplier" data-bs-parent="#menuMasterData">
          <li><a class="nav-link" href="{{ route('supplier.add') }}">Tambah</a></li>
          <li><a class="nav-link" href="{{ route('supplier.index') }}">Data</a></li>
        </ul>
      </li>
          <!-- Pelanggan -->
          <li class="nav-item">
            <a href="#menuPelanggan" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuPelanggan" role="button" aria-expanded="false">
              <i class="fas fa-users"></i> Pelanggan
            </a>
        <ul class="collapse nav flex-column ms-3" id="menuPelanggan" data-bs-parent="#menuMasterData">
          <li><a class="nav-link" href="{{ route('pelanggan.add') }}">Tambah</a></li>
          <li><a class="nav-link" href="{{ route('pelanggan.index') }}">Data</a></li>
        </ul>
      </li>
          <!-- Pembelian -->
          <li class="nav-item">
            <a href="#menuPembelian" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuPembelian" role="button" aria-expanded="false">
              <i class="fas fa-cart-plus"></i> Pembelian
            </a>
        <ul class="collapse nav flex-column ms-3" id="menuPembelian" data-bs-parent="#menuMasterData">
          <li><a class="nav-link" href="{{ route('pembelian.create') }}">Tambah Pembelian</a></li>
          <li><a class="nav-link" href="{{ route('pembelian.index') }}">Lihat Daftar</a></li>
        </ul>
      </li>
      @endif

      @if(auth()->user()->role == 'ADMIN' || auth()->user()->role == 'KASIR')
          <!-- Penjualan -->
          <li class="nav-item">
            <a href="#menuPenjualan" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuPenjualan" role="button" aria-expanded="false">
              <i class="fas fa-shopping-cart"></i> Penjualan
            </a>
        <ul class="collapse nav flex-column ms-3" id="menuPenjualan" data-bs-parent="#sidebarMenu">
          <li><a class="nav-link" href="{{ route('penjualan.create') }}">Tambah Penjualan</a></li>
          <li><a class="nav-link" href="{{ route('penjualan.index') }}">Lihat Daftar</a></li>
        </ul>
      </li>
      @endif

      @if(auth()->user()->role == 'ADMIN' || auth()->user()->role == 'GUDANG')
      <!-- Stok Opname -->
      <li class="nav-item">
        <a class="nav-link" href="{{ route('stok-opname.index') }}">
          <i class="fas fa-clipboard-check me-2"></i> Stok Opname
        </a>
      </li>
      @endif

      @if(auth()->user()->role == 'ADMIN')
      <!-- Keuangan -->
      <li class="nav-item">
        <a href="#menuKeuangan" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuKeuangan" role="button" aria-expanded="false">
          <i class="fas fa-money-bill-wave"></i> Keuangan
        </a>
        <ul class="collapse nav flex-column ms-3" id="menuKeuangan" data-bs-parent="#sidebarMenu">
          <li><a class="nav-link" href="{{ route('kas.masuk') }}"><i class="fas fa-plus-circle"></i> Kas Masuk</a></li>
          <li><a class="nav-link" href="{{ route('kas.keluar') }}"><i class="fas fa-minus-circle"></i> Kas Keluar</a></li>
          <li><a class="nav-link" href="{{ route('kas.index') }}"><i class="fas fa-list"></i> Daftar Transaksi Kas</a></li>
          <li><a class="nav-link" href="{{ route('kas-saldo.index') }}"><i class="fas fa-chart-line"></i> Arus Kas (Cash Flow)</a></li>
        </ul>
      </li>

      <!-- Laporan -->
      <li class="nav-item">
        <a href="#menuLaporan" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuLaporan" role="button" aria-expanded="false">
          <i class="fas fa-chart-bar"></i> Laporan
        </a>
        <ul class="collapse nav flex-column ms-3" id="menuLaporan" data-bs-parent="#sidebarMenu">
          <!-- Dropdown: Penjualan -->
          <li class="nav-item">
            <a href="#menuLaporanPenjualan" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuLaporanPenjualan" role="button" aria-expanded="false">
              <i class="fas fa-cash-register"></i> Penjualan
            </a>
            <ul class="collapse nav flex-column ms-3" id="menuLaporanPenjualan" data-bs-parent="#menuLaporan">
              <li><a class="nav-link" href="{{ route('laporan.penjualan') }}">Laporan Penjualan</a></li>
              <li><a class="nav-link" href="{{ route('laporan.penjualan-harian') }}">Laporan Penjualan Harian / Periodik</a></li>
              <li><a class="nav-link" href="{{ route('laporan.penjualan-barang') }}">Laporan Penjualan per Barang</a></li>            </ul>
          </li>
          <!-- Dropdown: Pembelian -->
          <li class="nav-item">
            <a href="#menuLaporanPembelian" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuLaporanPembelian" role="button" aria-expanded="false">
              <i class="fas fa-shopping-cart"></i> Pembelian
            </a>
            <ul class="collapse nav flex-column ms-3" id="menuLaporanPembelian" data-bs-parent="#menuLaporan">
              <li><a class="nav-link" href="{{ route('laporan.pembelian') }}">Laporan Pembelian Harian / Periodik</a></li>
              <li><a class="nav-link" href="{{ route('laporan.pembelian-per-supplier') }}">Laporan Pembelian per Supplier</a></li>
            </ul>
          </li>
          <li><a class="nav-link" href="{{ route('laporan.laba-rugi') }}"><i class="fas fa-chart-line"></i> Laba Rugi</a></li>
          <li><a class="nav-link" href="{{ route('laporan.stok') }}"><i class="fas fa-boxes"></i> Stok Barang</a></li>
          <li><a class="nav-link" href="{{ route('laporan.rekap_harian') }}"><i class="fas fa-calendar-day"></i> Rekap Harian</a></li>
          <li><a class="nav-link" href="{{ route('laporan.rekap_bulanan') }}"><i class="fas fa-calendar-alt"></i> Rekap Bulanan</a></li>
          <li><a class="nav-link" href="{{ route('laporan.stok-opname') }}"><i class="fas fa-clipboard-check"></i> Stok Opname</a></li>
        </ul>
      </li>

      <!-- Pengaturan -->
      <li class="nav-item">
        <a href="#menuPengaturan" class="nav-link dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#menuPengaturan" role="button" aria-expanded="false">
          <i class="fas fa-cog"></i> Pengaturan
        </a>
        <ul class="collapse nav flex-column ms-3" id="menuPengaturan" data-bs-parent="#sidebarMenu">
          <li><a class="nav-link" href="{{ route('profil-toko.index') }}"><i class="fas fa-store"></i> Profil Toko</a></li>
        </ul>
      </li>
      @endif
    </ul>
  </nav>

  <!-- Sidebar Overlay for Mobile -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <div class="main">
  <!-- Navbar -->
  <nav class="navbar navbar-expand navbar-custom mb-4">
    <div class="container-fluid">
      <button class="btn btn-outline-light me-2" id="sidebarToggle"><i class="fa fa-bars"></i></button>
      <span class="navbar-brand text-white fw-bold"></span>
      <div class="d-flex align-items-center">
        <i class="fa fa-bell me-3"></i>
        <i class="fa fa-envelope me-3"></i>
        <span class="me-3 d-none d-sm-inline">Welcome, <strong>{{ auth()->user()->name }}</strong></span>
        <div class="dropdown">
          <img src="{{ asset('assets/images/logo/logo.png') }}" class="rounded-circle dropdown-toggle" alt="User" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer; width: 30px; height: 30px; object-fit: cover;">
          <ul class="dropdown-menu" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="#"><i class="fa fa-user me-2"></i> Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="dropdown-item"><i class="fa fa-sign-out-alt me-2"></i> Logout</button>
              </form>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <script>
    $(document).ready(function() {
      var pathname = window.location.pathname;
      if (pathname.startsWith('/satuan')) {
        $('#menuMasterData').addClass('show');
        $('a[href="#menuMasterData"]').addClass('active');
        $('#menuSatuan').addClass('show');
        $('a[href="#menuSatuan"]').addClass('active');
        $('a[href="' + pathname + '"]').addClass('active');
      } else if (pathname.startsWith('/kategori')) {
        $('#menuMasterData').addClass('show');
        $('a[href="#menuMasterData"]').addClass('active');
        $('#menuKategori').addClass('show');
        $('a[href="#menuKategori"]').addClass('active');
        $('a[href="' + pathname + '"]').addClass('active');
      } else if (pathname.startsWith('/barang')) {
        $('#menuMasterData').addClass('show');
        $('a[href="#menuMasterData"]').addClass('active');
        $('#menuBarang').addClass('show');
        $('a[href="#menuBarang"]').addClass('active');
        $('a[href="' + pathname + '"]').addClass('active');
      } else if (pathname.startsWith('/konversi-satuan')) {
        $('#menuMasterData').addClass('show');
        $('a[href="#menuMasterData"]').addClass('active');
        $('#menuKonversiSatuan').addClass('show');
        $('a[href="#menuKonversiSatuan"]').addClass('active');
        $('a[href="' + pathname + '"]').addClass('active');
      } else if (pathname.startsWith('/harga-barang')) {
        $('#menuMasterData').addClass('show');
        $('a[href="#menuMasterData"]').addClass('active');
        $('#menuHargaBarang').addClass('show');
        $('a[href="#menuHargaBarang"]').addClass('active');
        $('a[href="' + pathname + '"]').addClass('active');
      } else if (pathname.startsWith('/jenis-barang')) {
        $('#menuMasterData').addClass('show');
        $('a[href="#menuMasterData"]').addClass('active');
        $('#menuJenisBarang').addClass('show');
        $('a[href="#menuJenisBarang"]').addClass('active');
        $('a[href="' + pathname + '"]').addClass('active');
      } else if (pathname.startsWith('/supplier')) {
        $('#menuSupplier').addClass('show');
        $('a[href="#menuSupplier"]').addClass('active');
        $('a[href="' + pathname + '"]').addClass('active');
      } else if (pathname.startsWith('/pelanggan')) {
        $('#menuPelanggan').addClass('show');
        $('a[href="#menuPelanggan"]').addClass('active');
        $('a[href="' + pathname + '"]').addClass('active');
      } else if (pathname.startsWith('/pembelian')) {
        $('#menuMasterData').addClass('show');
        $('a[href="#menuMasterData"]').addClass('active');
        $('#menuPembelian').addClass('show');
        $('a[href="#menuPembelian"]').addClass('active');
        $('a[href="' + pathname + '"]').addClass('active');
      } else if (pathname.startsWith('/penjualan')) {
        $('#menuMasterData').addClass('show');
        $('a[href="#menuMasterData"]').addClass('active');
        $('#menuPenjualan').addClass('show');
        $('a[href="#menuPenjualan"]').addClass('active');
        $('a[href="' + pathname + '"]').addClass('active');
      } else if (pathname.startsWith('/kas')) {
        $('#menuKeuangan').addClass('show');
        $('a[href="#menuKeuangan"]').addClass('active');
        $('a[href="' + pathname + '"]').addClass('active');
      } else if (pathname.startsWith('/kas-saldo')) {
        $('#menuKeuangan').addClass('show');
        $('a[href="#menuKeuangan"]').addClass('active');
        $('a[href="' + pathname + '"]').addClass('active');
      } else if (pathname.startsWith('/profil-toko')) {
        $('#menuPengaturan').addClass('show');
        $('a[href="#menuPengaturan"]').addClass('active');
        $('a[href="' + pathname + '"]').addClass('active');
      }

      // Sidebar toggle functionality
      $('#sidebarToggle').click(function() {
        $('.sidebar').toggleClass('show');
      });

      // Close sidebar when clicking overlay
      $('#sidebarOverlay').click(function() {
        $('.sidebar').removeClass('show');
        $(this).hide();
      });

      // Close sidebar when clicking a nav link (only for actual links, not dropdown toggles)
      $('.sidebar .nav-link').not('.dropdown-toggle').click(function() {
        $('.sidebar').removeClass('show');
        $('#sidebarOverlay').hide();
      });

      // Prevent closing sidebar when clicking on dropdown toggles
      $('.sidebar .dropdown-toggle').click(function(e) {
        e.preventDefault();
        var target = $(this).attr('data-bs-target');
        $(target).collapse('toggle');
      });
    });
  </script>
