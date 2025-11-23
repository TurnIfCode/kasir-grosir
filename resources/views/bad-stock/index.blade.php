@include('layout.header')

<div class="container-fluid">
  <h3 class="mb-4">Bad Stock Management</h3>
  <div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-circle"></i> Bad Stock Management
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-exchange-alt"></i> Mutasi Stok GS → BS
                                </h6>
                            </div>
                            <div class="card-body">
                                <form id="mutasiForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="barang_autocomplete" class="form-label">Barang</label>
                                        <input type="text" class="form-control" id="barang_autocomplete" name="barang_nama" placeholder="Ketik nama atau kode barang" required>
                                        <input type="hidden" id="barang_id" name="barang_id" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="satuan" class="form-label">Satuan</label>
                                        <select class="form-control" id="satuan" name="satuan_id" required>
                                            <option value="">Pilih Satuan</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="qty" class="form-label">Qty</label>
                                        <input type="number" class="form-control" id="qty" name="qty" min="1" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="keterangan" class="form-label">Keterangan</label>
                                        <textarea class="form-control" id="keterangan" name="keterangan" rows="2"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-exchange-alt"></i> Mutasi ke BS
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-plus-circle"></i> Barang Pengganti Sales
                                </h6>
                            </div>
                            <div class="card-body">
                                <form id="penggantiForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="supplier_autocomplete_pengganti" class="form-label">Supplier</label>
                                        <input type="text" class="form-control" id="supplier_autocomplete_pengganti" name="supplier_nama" placeholder="Ketik nama supplier" required>
                                        <input type="hidden" id="supplier_id_pengganti" name="supplier_id" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="barang_autocomplete_pengganti" class="form-label">Barang</label>
                                        <input type="text" class="form-control" id="barang_autocomplete_pengganti" name="barang_nama" placeholder="Ketik nama atau kode barang" required>
                                        <input type="hidden" id="barang_id_pengganti" name="barang_id" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="qty_pengganti" class="form-label">Qty</label>
                                        <input type="number" class="form-control" id="qty_pengganti" name="qty" min="1" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="keterangan_pengganti" class="form-label">Keterangan</label>
                                        <textarea class="form-control" id="keterangan_pengganti" name="keterangan" rows="2"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-plus-circle"></i> Catat Pengganti
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-white">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-tags"></i> Kompensasi Sales
                                </h6>
                            </div>
                            <div class="card-body">
                                <form id="kompensasiForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="supplier_autocomplete_kompensasi" class="form-label">Supplier</label>
                                        <input type="text" class="form-control" id="supplier_autocomplete_kompensasi" name="supplier_nama" placeholder="Ketik nama supplier" required>
                                        <input type="hidden" id="supplier_id_kompensasi" name="supplier_id" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="jumlah_kompensasi" class="form-label">Jumlah Kompensasi</label>
                                        <input type="number" class="form-control" id="jumlah_kompensasi" name="jumlah_kompensasi" min="0.01" step="0.01" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="barang_autocomplete_kompensasi" class="form-label">Barang Rusak (Opsional)</label>
                                        <input type="text" class="form-control" id="barang_autocomplete_kompensasi" name="barang_nama" placeholder="Ketik nama atau kode barang">
                                        <input type="hidden" id="barang_id_kompensasi" name="barang_id">
                                    </div>
                                    <div class="mb-3">
                                        <label for="qty_rusak" class="form-label">Qty Rusak (Opsional)</label>
                                        <input type="number" class="form-control" id="qty_rusak" name="qty_rusak" min="1">
                                    </div>
                                    <div class="mb-3">
                                        <label for="keterangan_kompensasi" class="form-label">Keterangan</label>
                                        <textarea class="form-control" id="keterangan_kompensasi" name="keterangan" rows="2"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-tags"></i> Catat Kompensasi
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Tables -->
                <div class="row mt-4">
                    <div class="col-12">
                        <ul class="nav nav-tabs" id="badStockTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="mutasi-tab" data-bs-toggle="tab" data-bs-target="#mutasi" type="button" role="tab" aria-controls="mutasi" aria-selected="true">
                                    <i class="fas fa-exchange-alt"></i> Mutasi Stok
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pengganti-tab" data-bs-toggle="tab" data-bs-target="#pengganti" type="button" role="tab" aria-controls="pengganti" aria-selected="false">
                                    <i class="fas fa-plus-circle"></i> Barang Pengganti
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="kompensasi-tab" data-bs-toggle="tab" data-bs-target="#kompensasi" type="button" role="tab" aria-controls="kompensasi" aria-selected="false">
                                    <i class="fas fa-tags"></i> Kompensasi
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content" id="badStockTabContent">
                            <div class="tab-pane fade show active" id="mutasi" role="tabpanel" aria-labelledby="mutasi-tab">
                                <div class="table-responsive mt-3">
                                    <table class="table table-striped" id="mutasiTable">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Barang</th>
                                                <th>Qty</th>
                                                <th>Dari</th>
                                                <th>Ke</th>
                                                <th>Keterangan</th>
                                                <th>Created By</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pengganti" role="tabpanel" aria-labelledby="pengganti-tab">
                                <div class="table-responsive mt-3">
                                    <table class="table table-striped" id="penggantiTable">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Supplier</th>
                                                <th>Barang</th>
                                                <th>Qty</th>
                                                <th>Keterangan</th>
                                                <th>Created By</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="kompensasi" role="tabpanel" aria-labelledby="kompensasi-tab">
                                <div class="table-responsive mt-3">
                                    <table class="table table-striped" id="kompensasiTable">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Supplier</th>
                                                <th>Jumlah</th>
                                                <th>Barang Rusak</th>
                                                <th>Qty Rusak</th>
                                                <th>Status</th>
                                                <th>Keterangan</th>
                                                <th>Created By</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load initial data
    loadMutasiStok();
    loadBarangPengganti();
    loadKompensasi();

    // Autocomplete for barang (mutasi form)
    $('#barang_autocomplete').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '{{ route("barang.search") }}',
                dataType: 'json',
                data: { q: request.term },
                success: function(data) {
                    if (data.status === 'success') {
                        response($.map(data.data, function(item) {
                            return {
                                label: item.kode_barang + ' - ' + item.nama_barang + (item.barcode ? ' (' + item.barcode + ')' : ''),
                                value: item.nama_barang,
                                id: item.id
                            };
                        }));
                    }
                }
            });
        },
        minLength: 1,
        select: function(event, ui) {
            $('#barang_id').val(ui.item.id);
            $('#barang_autocomplete').val(ui.item.value);
            
            // Fetch satuan konversi for selected barang
            $.ajax({
                url: '{{ url("/bad-stock/satuan-konversi") }}/' + ui.item.id,
                method: 'GET',
                success: function(res) {
                    if (res.success) {
                        var satuanSelect = $('#satuan');
                        satuanSelect.empty();
                        satuanSelect.append('<option value="">Pilih Satuan</option>');
                        res.data.forEach(function(satuan) {
                            satuanSelect.append('<option value="'+ satuan.id +'">' + satuan.nama + '</option>');
                        });
                    }
                }
            });
            
            return false;
        }
    });

    // Autocomplete for supplier (pengganti form)
    $('#supplier_autocomplete_pengganti').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '{{ route("supplier.search") }}',
                dataType: 'json',
                data: { q: request.term },
                success: function(data) {
                    if (data.status === 'success') {
                        response($.map(data.data, function(item) {
                            return {
                                label: item.nama_supplier,
                                value: item.nama_supplier,
                                id: item.id
                            };
                        }));
                    }
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $('#supplier_id_pengganti').val(ui.item.id);
            $('#supplier_autocomplete_pengganti').val(ui.item.value);
            return false;
        }
    });

    // Autocomplete for barang (pengganti form)
    $('#barang_autocomplete_pengganti').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '{{ route("barang.search") }}',
                dataType: 'json',
                data: { q: request.term },
                success: function(data) {
                    if (data.status === 'success') {
                        response($.map(data.data, function(item) {
                            return {
                                label: item.kode_barang + ' - ' + item.nama_barang + (item.barcode ? ' (' + item.barcode + ')' : ''),
                                value: item.nama_barang,
                                id: item.id
                            };
                        }));
                    }
                }
            });
        },
        minLength: 1,
        select: function(event, ui) {
            $('#barang_id_pengganti').val(ui.item.id);
            $('#barang_autocomplete_pengganti').val(ui.item.value);
            return false;
        }
    });

    // Autocomplete for supplier (kompensasi form)
    $('#supplier_autocomplete_kompensasi').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '{{ route("supplier.search") }}',
                dataType: 'json',
                data: { q: request.term },
                success: function(data) {
                    if (data.status === 'success') {
                        response($.map(data.data, function(item) {
                            return {
                                label: item.nama_supplier,
                                value: item.nama_supplier,
                                id: item.id
                            };
                        }));
                    }
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $('#supplier_id_kompensasi').val(ui.item.id);
            $('#supplier_autocomplete_kompensasi').val(ui.item.value);
            return false;
        }
    });

    // Autocomplete for barang (kompensasi form)
    $('#barang_autocomplete_kompensasi').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '{{ route("barang.search") }}',
                dataType: 'json',
                data: { q: request.term },
                success: function(data) {
                    if (data.status === 'success') {
                        response($.map(data.data, function(item) {
                            return {
                                label: item.kode_barang + ' - ' + item.nama_barang + (item.barcode ? ' (' + item.barcode + ')' : ''),
                                value: item.nama_barang,
                                id: item.id
                            };
                        }));
                    }
                }
            });
        },
        minLength: 1,
        select: function(event, ui) {
            $('#barang_id_kompensasi').val(ui.item.id);
            $('#barang_autocomplete_kompensasi').val(ui.item.value);
            return false;
        }
    });

    // Load mutasi stok data
    function loadMutasiStok() {
        $.get('{{ route("bad-stock.mutasi-stok.data") }}', function(data) {
            const tbody = $('#mutasiTable tbody');
            tbody.empty();
            data.data.forEach(item => {
                tbody.append(`
                    <tr>
                        <td>${new Date(item.created_at).toLocaleDateString('id-ID')}</td>
                        <td>${item.barang ? item.barang.nama_barang : '-'}</td>
                        <td>${item.qty}</td>
                        <td>${item.dari_gudang}</td>
                        <td>${item.ke_gudang}</td>
                        <td>${item.keterangan || '-'}</td>
                        <td>${item.creator ? item.creator.name : '-'}</td>
                    </tr>
                `);
            });
        });
    }

    // Load barang pengganti data
    function loadBarangPengganti() {
        $.get('{{ route("bad-stock.barang-pengganti.data") }}', function(data) {
            const tbody = $('#penggantiTable tbody');
            tbody.empty();
            data.data.forEach(item => {
                tbody.append(`
                    <tr>
                        <td>${new Date(item.created_at).toLocaleDateString('id-ID')}</td>
                        <td>${item.supplier ? item.supplier.nama_supplier : '-'}</td>
                        <td>${item.barang ? item.barang.nama_barang : '-'}</td>
                        <td>${item.qty}</td>
                        <td>${item.keterangan || '-'}</td>
                        <td>${item.creator ? item.creator.name : '-'}</td>
                    </tr>
                `);
            });
        });
    }

    // Load kompensasi data
    function loadKompensasi() {
        $.get('{{ route("bad-stock.kompensasi.data") }}', function(data) {
            const tbody = $('#kompensasiTable tbody');
            tbody.empty();
            data.data.forEach(item => {
                const aksiBtn = item.status === 'pending' ?
                    `<button class="btn btn-sm btn-success" onclick="gunakanKompensasi(${item.id})">
                        <i class="fas fa-check"></i> Gunakan
                    </button>` : '-';
                tbody.append(`
                    <tr>
                        <td>${new Date(item.created_at).toLocaleDateString('id-ID')}</td>
                        <td>${item.supplier ? item.supplier.nama_supplier : '-'}</td>
                        <td>Rp ${parseFloat(item.jumlah_kompensasi).toLocaleString('id-ID')}</td>
                        <td>${item.barang ? item.barang.nama_barang : '-'}</td>
                        <td>${item.qty_rusak || '-'}</td>
                        <td>
                            <span class="badge ${item.status === 'pending' ? 'bg-warning' : 'bg-success'}">
                                ${item.status === 'pending' ? 'Pending' : 'Digunakan'}
                            </span>
                        </td>
                        <td>${item.keterangan || '-'}</td>
                        <td>${item.creator ? item.creator.name : '-'}</td>
                        <td>${aksiBtn}</td>
                    </tr>
                `);
            });
        });
    }

    // Submit mutasi form
    $('#mutasiForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: '{{ route("bad-stock.mutasi-stok") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire('Berhasil!', response.message, 'success');
                $('#mutasiForm')[0].reset();
                $('#barang_autocomplete').val('');
                $('#barang_id').val('');
                loadMutasiStok();
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = 'Terjadi kesalahan!';
                if (errors) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                Swal.fire('Error!', errorMessage, 'error');
            }
        });
    });

    // Submit pengganti form
    $('#penggantiForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: '{{ route("bad-stock.barang-pengganti") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire('Berhasil!', response.message, 'success');
                $('#penggantiForm')[0].reset();
                $('#supplier_autocomplete_pengganti').val('');
                $('#supplier_id_pengganti').val('');
                $('#barang_autocomplete_pengganti').val('');
                $('#barang_id_pengganti').val('');
                loadBarangPengganti();
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = 'Terjadi kesalahan!';
                if (errors) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                Swal.fire('Error!', errorMessage, 'error');
            }
        });
    });

    // Submit kompensasi form
    $('#kompensasiForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: '{{ route("bad-stock.kompensasi-sales") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire('Berhasil!', response.message, 'success');
                $('#kompensasiForm')[0].reset();
                $('#supplier_autocomplete_kompensasi').val('');
                $('#supplier_id_kompensasi').val('');
                $('#barang_autocomplete_kompensasi').val('');
                $('#barang_id_kompensasi').val('');
                loadKompensasi();
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = 'Terjadi kesalahan!';
                if (errors) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                Swal.fire('Error!', errorMessage, 'error');
            }
        });
    });
});

// Function to use kompensasi
function gunakanKompensasi(id) {
    Swal.fire({
        title: 'Gunakan Kompensasi?',
        text: 'Apakah Anda yakin ingin menggunakan kompensasi ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Gunakan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `{{ url('/bad-stock/kompensasi') }}/${id}/gunakan`,
                method: 'PATCH',
                success: function(response) {
                    Swal.fire('Berhasil!', response.message, 'success');
                    loadKompensasi();
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Terjadi kesalahan saat menggunakan kompensasi', 'error');
                }
            });
        }
    });
}
</script>

@include('layout.footer')
