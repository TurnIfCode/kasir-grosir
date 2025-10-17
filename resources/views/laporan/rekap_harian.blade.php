@include('layouts.header')

<div class="container mt-4">
    <h3 class="mb-3">Laporan Rekap Harian</h3>

    <!-- Filter -->
    <form id="filterForm" class="mb-3">
        <label for="tanggal">Tanggal:</label>
        <input type="date" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}" class="form-control d-inline-block w-auto">
        <button type="button" id="btnFilter" class="btn btn-primary btn-sm ms-2">Tampilkan</button>
        <a href="{{ route('laporan.rekap-harian.export_pdf', ['tanggal' => date('Y-m-d')]) }}" target="_blank" id="btnExportPDF" class="btn btn-danger btn-sm ms-2">Export PDF</a>
    </form>

    <!-- Ringkasan -->
    <div id="ringkasan" class="mb-4 p-3 border rounded bg-light">
        <h5>Ringkasan Hari Ini</h5>
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

@include('layouts.footer')

<script>
$(document).ready(function() {
    // Initialize DataTables
    var tabelPenjualan = $('#tabelPenjualan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("laporan.rekap-harian.data") }}',
            data: function(d) {
                d.tanggal = $('#tanggal').val();
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
            emptyTable: "Tidak ada data penjualan pada tanggal ini."
        }
    });

    var tabelPembelian = $('#tabelPembelian').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("laporan.rekap-harian.data") }}',
            data: function(d) {
                d.tanggal = $('#tanggal').val();
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
            emptyTable: "Tidak ada data pembelian pada tanggal ini."
        }
    });

    // Load ringkasan data
    function loadRingkasan() {
        $.ajax({
            url: '{{ route("laporan.rekap-harian.data") }}',
            data: { tanggal: $('#tanggal').val() },
            success: function(data) {
                $('#total_penjualan').text(data.total_penjualan);
                $('#total_pembelian').text(data.total_pembelian);
                $('#total_barang_terjual').text(data.total_barang_terjual);
                $('#total_barang_dibeli').text(data.total_barang_dibeli);
                $('#laba_kotor').text(data.laba_kotor);
                $('#jumlah_transaksi_penjualan').text(data.jumlah_transaksi_penjualan);
                $('#jumlah_transaksi_pembelian').text(data.jumlah_transaksi_pembelian);
                $('#barang_terlaris').text(data.barang_terlaris);
            }
        });
    }

    // Filter button
    $('#btnFilter').on('click', function() {
        tabelPenjualan.ajax.reload();
        tabelPembelian.ajax.reload();
        loadRingkasan();

        // Update export PDF link
        var tanggal = $('#tanggal').val();
        $('#btnExportPDF').attr('href', '{{ route("laporan.rekap-harian.export_pdf") }}?tanggal=' + tanggal);
    });

    // Load initial data
    loadRingkasan();
});
</script>
