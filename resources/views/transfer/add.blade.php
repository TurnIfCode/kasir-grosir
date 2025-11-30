@include('layout.header')
<div class="container-fluid">
  <h3 class="mb-4">Tambah Transfer</h3>
  <div class="card p-4 mt-4">
    <form id="addTransferForm" action="#" method="POST">
      @csrf
      <div class="mb-3">
        <label for="bank_asal" class="form-label">Bank Asal</label>
        <select class="form-control" id="bank_asal" name="bank_asal" required>
          <option value="">Pilih Bank Asal</option>
          @foreach($kasSaldo as $kas)
            <option value="{{ $kas->id }}">{{ $kas->kas }}</option>
          @endforeach
        </select>
      </div>
      <div class="mb-3">
        <label for="bank_tujuan" class="form-label">Bank Tujuan*</label>
        <input type="text" class="form-control" id="bank_tujuan" name="bank_tujuan" required placeholder="Masukkan bank tujuan">
      </div>
      <div class="mb-3">
        <label for="nominal_transfer" class="form-label">Nominal Transfer*</label>
        <input type="number" step="0.01" class="form-control" id="nominal_transfer" name="nominal_transfer" value="10000" required placeholder="Masukkan nominal transfer">
      </div>
      <div class="mb-3">
        <label for="admin_bank" class="form-label">Admin Bank*</label>
        <input type="number" step="0.01" class="form-control" id="admin_bank" name="admin_bank" value="0" required placeholder="Masukkan biaya admin bank">
      </div>
      <div class="mb-3">
        <label for="margin" class="form-label">Margin*</label>
        <input type="number" step="0.01" class="form-control" id="margin" name="margin" value="5000" required placeholder="Masukkan margin">
      </div>
      <div class="mb-3">
        <label for="grand_total" class="form-label">Grand Total</label>
        <input type="number" class="form-control" id="grand_total" name="grand_total" readonly>
      </div>
      <div class="mb-3">
        <label for="catatan" class="form-label">Catatan</label>
        <textarea name="catatan" id="catatan" class="form-control" placeholder="Masukkan catatan (opsional)"></textarea>
      </div>
      <button type="submit" id="btnSave" class="btn btn-primary">Simpan</button>
    </form>
  </div>
</div>

<script>
$(document).ready(function() {
  // Function to calculate grand total
  function calculateGrandTotal() {
    var nominal = parseFloat($('#nominal_transfer').val()) || 0;
    var admin = parseFloat($('#admin_bank').val()) || 0;
    var margin = parseFloat($('#margin').val()) || 0;
    var total = nominal + admin + margin;
    var formatted = total.toLocaleString('id-ID');
    $('#grand_total').val(formatted);
  }

  // Calculate on page load
  calculateGrandTotal();

  // Calculate on input change
  $('#nominal_transfer, #admin_bank, #margin').on('input', function() {
    calculateGrandTotal();
  });

  $("#btnSave").click(function() {
    $('#addTransferForm').validate({
      rules: {
        bank_asal: {
          required: true
        },
        bank_tujuan: {
          required: true
        },
        nominal_transfer: {
          required: true,
          number: true,
          min: 0
        },
        admin_bank: {
          required: true,
          number: true,
          min: 0
        },
        margin: {
          required: true,
          number: true,
          min: 0
        }
      },
      messages: {
        bank_asal: {
          required: "Bank asal wajib diisi"
        },
        bank_tujuan: {
          required: "Bank tujuan wajib diisi"
        },
        nominal_transfer: {
          required: "Nominal transfer wajib diisi",
          number: "Nominal transfer harus berupa angka",
          min: "Nominal transfer tidak boleh negatif"
        },
        admin_bank: {
          required: "Admin bank wajib diisi",
          number: "Admin bank harus berupa angka",
          min: "Admin bank tidak boleh negatif"
        },
        margin: {
          required: "Margin wajib diisi",
          number: "Margin harus berupa angka",
          min: "Margin tidak boleh negatif"
        }
      },
      submitHandler: function(form) {
        $.ajax({
          url: "{{ route('transfer.store') }}",
          type: "POST",
          data: $(form).serialize(),
          success: function(response) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message
            }).then(function() {
              setTimeout(() => {
                window.location.href = "{{ route('transfer.add') }}";
              }, 500);
            });
          },
          error: function(xhr) {
            var response = xhr.responseJSON;
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: response ? response.message : 'Terjadi kesalahan'
            });
          }
        });
      }
    });
  });
});
</script>

@include('layout.footer')
