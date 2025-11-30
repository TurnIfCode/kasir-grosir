@include('layout.header')

<div class="container-fluid">
    <h3 class="mb-4">Daftar Paket</h3>

    <a href="{{ route('master.paket.create') }}" class="btn btn-primary mb-3">Tambah Paket</a>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <table class="table table-striped" id="paketTable" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Total Qty</th>
                <th>Harga</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            {{-- DataTables will load data via AJAX --}}
        </tbody>
    </table>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    $('#paketTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("master.paket.data") }}',
        columns: [
            { data: 0, name: 'id' },
            { data: 1, name: 'nama' },
            { data: 2, name: 'total_qty' },
            { data: 3, name: 'harga' },
            { data: 4, name: 'status' },
            { data: 5, name: 'aksi', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    });
});
</script>

@include('layout.footer')
