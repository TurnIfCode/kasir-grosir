@extends('layout.master')

@section('title', 'Daftar Paket')

@section('content')
<div class="container-fluid">
    <h3 class="mb-4">Daftar Paket</h3>

    <a href="{{ route('paket.add') }}" class="btn btn-primary mb-3">Tambah Paket</a>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <table class="table table-striped" id="paketTable">
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
            @foreach ($paket as $p)
            <tr>
                <td>{{ $p->id }}</td>
                <td>{{ $p->nama }}</td>
                <td>{{ $p->total_qty }}</td>
                <td>{{ number_format($p->harga) }}</td>
                <td>{{ ucfirst($p->status) }}</td>
                <td>
                    <a href="{{ route('paket.edit', $p->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <!-- Add delete button if needed -->
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $paket->links() }}
</div>

@endsection
