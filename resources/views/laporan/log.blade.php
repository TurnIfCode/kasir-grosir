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
$(document).ready(function() {
    var logTable = $('#logTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('laporan.log.data') }}",
            data: function(d) {
                d.tanggal_awal = $('#tanggal_awal').val();
                d.tanggal_akhir = $('#tanggal_akhir').val();
            }
        },
        searching: true,
        paging: true,
        info: true,
        ordering: true,
        order: [[3, 'desc']], // Order by Tanggal descending by default
        columns: [
            { data: 'id' },
            { data: 'keterangan' },
            { data: 'created_by', defaultContent: '-' },
            { data: 'created_at', defaultContent: '-' }
        ]
    });

    // Setup filter form submit
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        logTable.ajax.reload();
    });
});
</script>
@endsection
