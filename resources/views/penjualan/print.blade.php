<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Struk Penjualan</title>

<style>
    body {
        width: 200px; /* ULTRA SAFE for 58mm printers */
        font-family: monospace;
        font-size: 11px;
        margin: 0;
        padding: 0;
        word-wrap: break-word;
    }

    .center { text-align: center; }
    .bold { font-weight: bold; }
    .line { border-top: 1px dashed #000; margin: 5px 0; }
    .logo { width: 50px; margin: 0 auto 4px auto; display: block; }

    .row {
        display: flex;
        justify-content: space-between;
    }

    .right { text-align: right; }
</style>
</head>

<body onload="window.print()">

    <div class="center">
        @if($profilToko && $profilToko->logo)
            <img src="{{ asset($profilToko->logo) }}" class="logo">
        @endif

        <div class="bold">{{ $profilToko->nama_toko ?? '-' }}</div>
        <div>{{ $profilToko->slogan ?? '' }}</div>

        <!-- ALAMAT, DIPAKSA WRAP -->
        <div style="white-space: normal; max-width: 200px;">
            {{ $profilToko->alamat ?? '-' }},
            {{ $profilToko->kota ?? '' }},
            {{ $profilToko->provinsi ?? '' }}
            {{ $profilToko->kode_pos ?? '' }}
        </div>

        <div>Telp: {{ $profilToko->no_telp ?? '-' }}</div>
    </div>

    <div class="line"></div>

    <div>Tanggal : {{ $penjualan->tanggal_penjualan->format('d/m/Y') }}</div>
    <div>No. Str : {{ $penjualan->kode_penjualan }}</div>
    <div>Kasir   : {{ $penjualan->creator->name ?? 'Kasir' }}</div>
    <div>Pembayaran : {{ ucfirst($penjualan->jenis_pembayaran) }}</div>

    <div class="line"></div>

    <div class="bold">Item | Qty | Subtotal</div>
    <div>-------------------------------</div>

    @foreach($penjualan->details as $detail)

        <!-- Nama Barang -->
        <div>{{ Str::limit($detail->barang->nama_barang, 26) }}</div>

        <!-- qty harga subtotal -->
        <div class="row">
            <span>{{ round($detail->qty, 2) }} x {{ number_format($detail->harga_jual,0,',','.') }}</span>
            <span>{{ number_format($detail->subtotal,0,',','.') }}</span>
        </div>

    @endforeach

    <div class="line"></div>

    <div class="row">
        <span>Total</span>
        <span>{{ number_format($penjualan->total,0,',','.') }}</span>
    </div>

    <div class="row">
        <span>Pembulatan</span>
        <span>{{ number_format($penjualan->pembulatan,0,',','.') }}</span>
    </div>

    <div class="row bold">
        <span>GRAND TOTAL</span>
        <span>{{ number_format($penjualan->grand_total,0,',','.') }}</span>
    </div>

    @if($penjualan->jenis_pembayaran == 'tunai')
    <div class="row">
        <span>DIBAYAR</span>
        <span>{{ number_format($penjualan->dibayar,0,',','.') }}</span>
    </div>

    <div class="row">
        <span>KEMBALIAN</span>
        <span>{{ number_format($penjualan->kembalian,0,',','.') }}</span>
    </div>
    @endif

    <div class="line"></div>

    <div class="center bold">TERIMA KASIH</div>

    <div class="line"></div>

</body>
</html>
