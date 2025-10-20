<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kasir Grosir</title>
    <link href="{{ asset('assets/css/login.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            @php
                $profilToko = \App\Models\ProfilToko::first();
            @endphp
            @if($profilToko && $profilToko->logo)
                <img src="{{ asset('storage/' . $profilToko->logo) }}" alt="{{ $profilToko->nama_toko ?? 'Logo Toko' }}">
            @else
                <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GrosirIndo Logo">
            @endif
        </div>

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="form-group">
                <input type="text" id="username" name="username" required placeholder="Username">
            </div>

            <div class="form-group">
                <input type="password" id="password" name="password" required placeholder="Password">
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script>
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session("success") }}',
                confirmButtonText: 'OK'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session("error") }}',
                confirmButtonText: 'OK'
            });
        @endif
    </script>
</body>
</html>
