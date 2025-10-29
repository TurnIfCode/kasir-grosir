@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Data Stok Minimum</h3>
  <div class="card p-4">
    <table id="stokMinimumTable" class="table table-striped">
      <thead>
        <tr>
          <th>Nama Barang</th>
          <th>Kode Barang</th>
          <th>Jumlah Minimum</th>
          <th>Satuan</th>
          <th>Jumlah Satuan Terkecil</th>
          <th>Satuan Terkecil</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

@include('layout.footer')
