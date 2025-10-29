<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Laporan Stok - GrosirIndo</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .menu-card {
            background: white;
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            cursor: pointer;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .menu-icon {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 1rem;
        }

        .menu-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .menu-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .container {
            max-width: 1200px;
        }

        .page-title {
            color: #333;
            font-weight: 700;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .menu-card {
                margin-bottom: 1rem;
            }

            .menu-icon {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    @include('layout.header')

    <div class="container mt-4">
        <h1 class="page-title text-center">
            <i class="fas fa-boxes me-3"></i>
            Laporan Stok
        </h1>

        <div class="row g-4">
            <!-- Stok Akhir -->
            <div class="col-lg-4 col-md-6">
                <div class="menu-card p-4" onclick="window.location.href='{{ route('laporan.stok_akhir') }}'">
                    <div class="text-center">
                        <div class="menu-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h5 class="menu-title">📦 Stok Akhir</h5>
                        <p class="menu-description">
                            Menampilkan jumlah stok terkini setiap barang dan nilai total stok berdasarkan harga beli terakhir.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stok Masuk & Keluar -->
            <div class="col-lg-4 col-md-6">
                <div class="menu-card p-4" onclick="window.location.href='{{ route('laporan.stok-masuk-keluar') }}'">
                    <div class="text-center">
                        <div class="menu-icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h5 class="menu-title">🔄 Stok Masuk & Keluar</h5>
                        <p class="menu-description">
                            Melacak aliran barang masuk dari pembelian dan keluar dari penjualan dalam periode tertentu.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Barang Hampir Habis -->
            <div class="col-lg-4 col-md-6">
                <div class="menu-card p-4" onclick="window.location.href='{{ route('laporan.barang-hampir-habis') }}'">
                    <div class="text-center">
                        <div class="menu-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h5 class="menu-title">⚠️ Barang Hampir Habis</h5>
                        <p class="menu-description">
                            Menampilkan daftar barang yang berada di bawah batas minimum stok untuk peringatan restock.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Barang Tidak Laku -->
            <div class="col-lg-4 col-md-6">
                <div class="menu-card p-4" onclick="window.location.href='{{ route('laporan.barang-tidak-laku') }}'">
                    <div class="text-center">
                        <div class="menu-icon">
                            <i class="fas fa-sleep"></i>
                        </div>
                        <h5 class="menu-title">💤 Barang Tidak Laku</h5>
                        <p class="menu-description">
                            Mengetahui barang yang tidak terjual dalam periode tertentu untuk optimasi inventory.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Koreksi Stok -->
            <div class="col-lg-4 col-md-6">
                <div class="menu-card p-4" onclick="window.location.href='{{ route('laporan.koreksi-stok') }}'">
                    <div class="text-center">
                        <div class="menu-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <h5 class="menu-title">🧾 Koreksi Stok</h5>
                        <p class="menu-description">
                            Membandingkan hasil stok opname dengan data sistem untuk koreksi dan penyesuaian stok.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layout.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
