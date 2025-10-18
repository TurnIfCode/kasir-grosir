<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekap Bulanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    @include('layout.header')

<div class="container mt-4">
    <h3 class="mb-3">Laporan Rekap Bulanan</h3>

    <!-- Filter -->
    <form id="filterForm" class="mb-3">
        <label for="bulan">Bulan:</label>
        <input type="month" id="bulan" name="bulan" value="{{ date('Y-m') }}" class="form-control d-inline-block w-auto">
        <button type="button" id="btnFilter" class="btn btn-primary btn-sm ms-2">Tampilkan</button>
        <a href="{{ route('laporan.rekap_bulanan.export_pdf', ['bulan' => date('Y-m')]) }}" target="_blank" id="btnExportPDF" class="btn btn-danger btn-sm ms-2">Export PDF</a>
    </form>

    <!-- Ringkasan -->
    <div id="ringkasan" class="mb-4 p-3 border rounded bg-light">
        <h5>Ringkasan Bulan Ini</h5>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-bordered">
                    <tr><td>Total Penjualan</td><td id="total_penjualan">-</td></tr>
                    <tr><td>Total Pembelian</td><td id="total_pembelian">-</td></tr>
                    <tr><td>Total Barang Terjual</td><td id="total_barang_terjual">-</td></tr>
                    <tr><td>Total Barang Dibeli</td><td id="total_barang_dibeli">-</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-bordered">
                    <tr><td>Laba Kotor</td><td id="laba_kotor">-</td></tr>
                    <tr><td>Jumlah Transaksi Penjualan</td><td id="jumlah_transaksi_penjualan">-</td></tr>
                    <tr><td>Jumlah Transaksi Pembelian</td><td id="jumlah_transaksi_pembelian">-</td></tr>
                    <tr><td>Barang Terlaris</td><td id="barang_terlaris">-</td></tr>
                    <tr><td>Rata-rata Penjualan Harian</td><td id="rata_penjualan_harian">-</td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- DataTables -->
    <h5>Rincian Penjualan</h5>
    <table id="tabelPenjualan" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Satuan</th>
                <th>Jumlah Terjual</th>
                <th>Harga</th>
                <th>Total</th>
            </tr>
        </thead>
    </table>

    <h5 class="mt-5">Rincian Pembelian</h5>
    <table id="tabelPembelian" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Satuan</th>
                <th>Jumlah Dibeli</th>
                <th>Harga</th>
                <th>Total</th>
            </tr>
        </thead>
    </table>
</div>

    @include('layout.footer')

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
</body>
</html>

<script>
$(document).ready(function() {
    // Initialize DataTables
    var tabelPenjualan = $('#tabelPenjualan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("laporan.rekap_bulanan.data") }}',
            data: function(d) {
                d.bulan = $('#bulan').val();
                d.type = 'penjualan';
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'kode_barang' },
            { data: 'nama_barang' },
            { data: 'nama_satuan' },
            { data: 'jumlah_terjual_formatted' },
            { data: 'harga_formatted' },
            { data: 'total_formatted' }
        ],
        language: {
            emptyTable: "Tidak ada data penjualan pada bulan ini."
        }
    });

    var tabelPembelian = $('#tabelPembelian').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("laporan.rekap_bulanan.data") }}',
            data: function(d) {
                d.bulan = $('#bulan').val();
                d.type = 'pembelian';
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'kode_barang' },
            { data: 'nama_barang' },
            { data: 'nama_satuan' },
            { data: 'jumlah_dibeli_formatted' },
            { data: 'harga_formatted' },
            { data: 'total_formatted' }
        ],
        language: {
            emptyTable: "Tidak ada data pembelian pada bulan ini."
        }
    });

    // Load ringkasan data
    function loadRingkasan() {
        $.ajax({
            url: '{{ route("laporan.rekap_bulanan.data") }}',
            data: { bulan: $('#bulan').val() },
            success: function(data) {
                $('#total_penjualan').text(data.total_penjualan);
                $('#total_pembelian').text(data.total_pembelian);
                $('#total_barang_terjual').text(data.total_barang_terjual);
                $('#total_barang_dibeli').text(data.total_barang_dibeli);
                $('#laba_kotor').text(data.laba_kotor);
                $('#jumlah_transaksi_penjualan').text(data.jumlah_transaksi_penjualan);
                $('#jumlah_transaksi_pembelian').text(data.jumlah_transaksi_pembelian);
                $('#barang_terlaris').text(data.barang_terlaris);
                $('#rata_penjualan_harian').text(data.rata_penjualan_harian);
            }
        });
    }

    // Filter button
    $('#btnFilter').on('click', function() {
        tabelPenjualan.ajax.reload();
        tabelPembelian.ajax.reload();
        loadRingkasan();

        // Update export PDF link
        var bulan = $('#bulan').val();
        $('#btnExportPDF').attr('href', '{{ route("laporan.rekap_bulanan.export_pdf") }}?bulan=' + bulan);
    });

    // Load initial data
    loadRingkasan();
});
</script>
