@include('layout.header')
<div class="container-fluid">
  <h3 class="mb-4">Data Paket</h3>
  <div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">Data Paket</h5>
      <a href="{{ route('paket.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Paket
      </a>
    </div>
    <div class="table-responsive">
      <table id="paketTable" class="table table-hover table-striped">
        <thead>
          <tr>
            <th>Kode Paket</th>
            <th>Nama Paket</th>
            <th>Harga per 3</th>
            <th>Harga per Unit</th>
            <th>Keterangan</th>
            <th>Daftar Barang</th>
            <th>Aksi</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<!-- Modal Detail Paket -->
<div class="modal fade" id="detailPaketModal" tabindex="-1" aria-labelledby="detailPaketModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailPaketModalLabel">Detail Paket</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tbody id="paketDetailBody">
            <!-- Data akan diisi oleh JavaScript -->
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    console.log('Ready dude==>');
  // DataTable
  var table = $('#paketTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route("paket.data") }}',
    columns: [
      { data: 'kode_paket', name: 'kode_paket' },
      { data: 'nama_paket', name: 'nama_paket' },
      { data: 'harga_per_3', name: 'harga_per_3' },
      { data: 'harga_per_unit', name: 'harga_per_unit' },
      { data: 'keterangan', name: 'keterangan', orderable: false, searchable: false },
      { data: 'daftar_barang', name: 'daftar_barang', orderable: false, searchable: false }, // Kolom daftar barang
      { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
    ],
    responsive: true,
    language: {
      processing: '<i class="fas fa-spinner fa-spin"></i> Memuat...',
      lengthMenu: 'Tampilkan _MENU_ data per halaman',
      zeroRecords: 'Tidak ada data yang ditemukan',
      info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
      infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
      infoFiltered: '(difilter dari _MAX_ total data)',
      search: 'Cari:',
      paginate: {
        first: 'Pertama',
        last: 'Terakhir',
        next: 'Selanjutnya',
        previous: 'Sebelumnya'
      }
    }
  });

  // Detail handler
  $(document).on('click', '#btnDetail', function() {
    var paketId = $(this).data('id');

    $.ajax({
      url: '{{ route("paket.find", ":id") }}'.replace(':id', paketId),
      type: 'GET',
      success: function(response) {
        if (response.status) {
          var paket = response.data;
          var detailHtml = '';

          // Format data untuk tabel detail
          detailHtml += '<tr><td><strong>Kode Paket</strong></td><td>' + (paket.kode_paket || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Nama Paket</strong></td><td>' + (paket.nama_paket || '-') + '</td></tr>';
          detailHtml += '<tr><td><strong>Harga per 3</strong></td><td>Rp ' + (paket.harga_per_3 ? paket.harga_per_3 : '0') + '</td></tr>';
          detailHtml += '<tr><td><strong>Harga per Unit</strong></td><td>Rp ' + (paket.harga_per_unit ? paket.harga_per_unit : '0') + '</td></tr>';
          detailHtml += '<tr><td><strong>Keterangan</strong></td><td>' + (paket.keterangan || '-') + '</td></tr>';
          
          // Tampilkan daftar barang di modal detail
          var daftarBarangHtml = '';
          if (paket.daftar_barang && paket.daftar_barang.length > 0) {
            daftarBarangHtml = '<ul class="mb-0">';
            paket.daftar_barang.forEach(function(barang) {
              daftarBarangHtml += '<li>' + barang.nama_barang +'</li>';
            });
            daftarBarangHtml += '</ul>';
          } else {
            daftarBarangHtml = '-';
          }
          
          detailHtml += '<tr><td><strong>Daftar Barang</strong></td><td>' + daftarBarangHtml + '</td></tr>';

          $('#paketDetailBody').html(detailHtml);
          $('#detailPaketModal').modal('show');
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
        }
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
      }
    });
  });

  // Edit handler
  $(document).on('click', '#btnEdit', function() {
    var paketId = $(this).data('id');
    window.location.href = '{{ route("paket.edit", ":id") }}'.replace(':id', paketId);
  });

  // Delete handler
  $(document).on('click', '#btnDelete', function() {
    var paketId = $(this).data('id');
    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: "Data yang dihapus tidak dapat dikembalikan!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: '{{ route("paket.delete", ":id") }}'.replace(':id', paketId),
          type: 'DELETE',
          data: { _token: '{{ csrf_token() }}' },
          success: function(response) {
            if (response.status) {
              Swal.fire('Terhapus!', response.message, 'success');
              table.ajax.reload();
            } else {
              Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
            }
          },
          error: function() {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
          }
        });
      }
    });
  });
});
</script>

@include('layout.footer')