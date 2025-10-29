@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Tambah Harga Barang</h3>
  <div class="card p-4">
    <form id="hargaForm" action="{{ route('harga-barang.store') }}" method="POST">
      @csrf

      <div id="hargaRows">
        <div class="harga-row border p-3 mb-3">
          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label for="barang_autocomplete_0">Barang</label>
                <input type="text" class="form-control barang-autocomplete" id="barang_autocomplete_0" placeholder="Cari barang..." required>
                <input type="hidden" name="harga_data[0][barang_id]" id="barang_id_0" class="barang-id-input">
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label for="satuan_id_0">Satuan</label>
                <select name="harga_data[0][satuan_id]" id="satuan_id_0" class="form-control" disabled required>
                  <option value="">-- Pilih Satuan --</option>
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label for="tipe_harga_0">Tipe Harga</label>
                <select name="harga_data[0][tipe_harga]" id="tipe_harga_0" class="form-control" required>
                  <option value="ecer">Ecer</option>
                  <option value="grosir">Grosir</option>
                  <option value="member">Member</option>
                  <option value="promo">Promo</option>
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label for="harga_0">Harga</label>
                <input type="number" step="0.01" name="harga_data[0][harga]" id="harga_0" class="form-control" placeholder="0.00" required>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label for="status_0">Status</label>
                <select name="harga_data[0][status]" id="status_0" class="form-control" required>
                  <option value="aktif">Aktif</option>
                  <option value="nonaktif">Nonaktif</option>
                </select>
              </div>
            </div>
            <div class="col-md-1">
              <div class="form-group">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-danger btn-sm remove-row" style="display: none;">Hapus</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <button type="button" id="addRow" class="btn btn-secondary mb-3">Tambah Harga Lagi</button>
      <button type="submit" class="btn btn-primary">Simpan Semua</button>
      <a href="{{ route('harga-barang.index') }}" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
  let rowCount = 1;

  // Initialize autocomplete for first row
  initializeAutocomplete(0);

  $('#addRow').on('click', function() {
    const newRow = `
      <div class="harga-row border p-3 mb-3">
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label for="barang_autocomplete_${rowCount}">Barang</label>
              <input type="text" class="form-control barang-autocomplete" id="barang_autocomplete_${rowCount}" placeholder="Cari barang..." required>
              <input type="hidden" name="harga_data[${rowCount}][barang_id]" id="barang_id_${rowCount}" class="barang-id-input">
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="satuan_id_${rowCount}">Satuan</label>
              <select name="harga_data[${rowCount}][satuan_id]" id="satuan_id_${rowCount}" class="form-control" disabled required>
                <option value="">-- Pilih Satuan --</option>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="tipe_harga_${rowCount}">Tipe Harga</label>
              <select name="harga_data[${rowCount}][tipe_harga]" id="tipe_harga_${rowCount}" class="form-control" required>
                <option value="ecer">Ecer</option>
                <option value="grosir">Grosir</option>
                <option value="member">Member</option>
                <option value="promo">Promo</option>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="harga_${rowCount}">Harga</label>
              <input type="number" step="0.01" name="harga_data[${rowCount}][harga]" id="harga_${rowCount}" class="form-control" placeholder="0.00" required>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="status_${rowCount}">Status</label>
              <select name="harga_data[${rowCount}][status]" id="status_${rowCount}" class="form-control" required>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
              </select>
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <label>&nbsp;</label>
              <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
            </div>
          </div>
        </div>
      </div>
    `;
    $('#hargaRows').append(newRow);
    initializeAutocomplete(rowCount);
    rowCount++;
  });

  $(document).on('click', '.remove-row', function() {
    $(this).closest('.harga-row').remove();
  });

  $('#hargaForm').on('submit', function(e) {
    // Basic validation can be added here if needed
  });
});

function initializeAutocomplete(index) {
  $(`.barang-autocomplete[id="barang_autocomplete_${index}"]`).autocomplete({
    source: function(request, response) {
      $.ajax({
        url: '/barang/search',
        data: { q: request.term },
        success: function(data) {
          if (data.status === 'success') {
            response(data.data.map(item => ({
              label: `${item.kode_barang} - ${item.nama_barang}`,
              value: item.nama_barang,
              id: item.id
            })));
          }
        }
      });
    },
    minLength: 2,
    select: function(event, ui) {
      $(this).val(ui.item.value);
      $(`#barang_id_${index}`).val(ui.item.id);
      loadSatuanOptions(index, ui.item.id);
      return false;
    }
  });
}

function loadSatuanOptions(index, barangId) {
  $.ajax({
    url: '/barang/' + barangId + '/satuan',
    success: function(data) {
      if (data.status === 'success') {
        const select = $(`#satuan_id_${index}`);
        select.empty().append('<option value="">Pilih Satuan</option>');

        // Sort satuan by nama_satuan
        data.data.sort((a, b) => a.nama_satuan.localeCompare(b.nama_satuan));

        data.data.forEach(satuan => {
          select.append(`<option value="${satuan.satuan_id}">${satuan.nama_satuan}</option>`);
        });

        select.prop('disabled', false);
      }
    }
  });
}
</script>
@endsection

@include('layout.footer')
