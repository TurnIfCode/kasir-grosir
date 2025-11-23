@extends('layout.master')

@section('title', 'Laporan Log')

@section('content')
<div class="container-fluid">
    <h3 class="mb-4">Laporan Log</h3>

    <form id="filterForm" class="row g-3 mb-3">
        <div class="col-auto">
            <label for="tanggal_awal" class="col-form-label">Tanggal Awal:</label>
            <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control" value="{{ $today }}">
        </div>
        <div class="col-auto">
            <label for="tanggal_akhir" class="col-form-label">Tanggal Akhir:</label>
            <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control" value="{{ $today }}">
        </div>
        <div class="col-auto align-self-end">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped" id="logTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Keterangan</th>
                    <th>Created By</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data rows go here -->
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const logTableBody = document.querySelector('#logTable tbody');

    function fetchLogs(tanggalAwal, tanggalAkhir) {
        fetch(`{{ route('laporan.log.data') }}?tanggal_awal=${tanggalAwal}&tanggal_akhir=${tanggalAkhir}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    logTableBody.innerHTML = '';
                    data.data.forEach(log => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${log.id}</td>
                            <td>${log.keterangan}</td>
                            <td>${log.created_by || '-'}</td>
                            <td>${log.created_at || '-'}</td>
                        `;
                        logTableBody.appendChild(row);
                    });
                }
            });
    }

    // Initial fetch with default dates
    const defaultTanggalAwal = document.getElementById('tanggal_awal').value;
    const defaultTanggalAkhir = document.getElementById('tanggal_akhir').value;
    fetchLogs(defaultTanggalAwal, defaultTanggalAkhir);

    // Setup filter form submit
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const tanggalAwal = document.getElementById('tanggal_awal').value;
        const tanggalAkhir = document.getElementById('tanggal_akhir').value;
        fetchLogs(tanggalAwal, tanggalAkhir);
    });
});
</script>
@endsection
