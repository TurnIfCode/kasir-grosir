@include('layout.header')

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Tambah Stok Opname</h2>
    <a href="{{ route('stok-opname.index') }}" class="btn btn-secondary">Kembali</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('stok-opname.store') }}" method="POST">
        @csrf

        <div class="row mb-3">
          <div class="col-md-6">
            <label for="tanggal" class="form-label">Tanggal Opname</label>
            <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}" required>
          </div>
          <div class="col-md-6">
            <label for="catatan" class="form-label">Catatan</label>
            <textarea class="form-control" id="catatan" name="catatan" rows="3"></textarea>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label for="kategori_id" class="form-label">Pilih Kategori Barang</label>
            <select class="form-control" id="kategori_id" name="kategori_id" required>
              <option value="">-- Pilih Kategori --</option>
              @foreach($kategoris as $kategori)
                <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <h5 class="mb-3">Data Barang</h5>
        <div class="table-responsive">
          <table class="table table-striped" id="barang-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Satuan</th>
                <th>Stok Sistem</th>
                <th>Stok Fisik</th>
                <th>Selisih</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody>
              <!-- Data akan diisi oleh DataTables -->
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end mt-4">
          <button type="submit" class="btn btn-primary">Simpan sebagai Draft</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  // Initialize DataTable
  const table = $('#barang-table').DataTable({
    ajax: {
      url: '{{ route("stok-opname.get-barang-by-kategori") }}',
      data: function(d) {
        d.kategori_id = $('#kategori_id').val();
      }
    },
    columns: [
      { data: 'DT_RowIndex', orderable: false, searchable: false },
      { data: 'kode_barang' },
      { data: 'nama_barang' },
      { data: 'nama_kategori' },
      { data: 'nama_satuan' },
      { data: 'stok_sistem' },
      { data: 'stok_fisik_input', orderable: false },
      { data: 'selisih_input', orderable: false },
      { data: 'keterangan_input', orderable: false }
    ],
    responsive: true,
    pageLength: 25,
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
    },
    drawCallback: function() {
      // Re-bind event handlers after table redraw
      bindStokFisikEvents();
    }
  });

  // Filter by kategori
  $('#kategori_id').on('change', function() {
    table.ajax.reload();
  });

  // Function to bind stok fisik input events
  function bindStokFisikEvents() {
    $('.stok-fisik').off('input').on('input', function() {
      var row = $(this).closest('tr');
      var stokSistem = parseFloat(row.find('td').eq(5).text()) || 0; // Stok sistem column
      var stokFisik = parseFloat($(this).val()) || 0;
      var selisih = Math.round(stokFisik - stokSistem);
      row.find('.selisih').val(selisih);
    });
  }

  // Initial bind
  bindStokFisikEvents();
});
</script>

@include('layout.footer')
