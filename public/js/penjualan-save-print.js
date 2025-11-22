$('#btnSavePrint').click(function() {
    $.ajax({
        url: '/penjualan/store',
        method: 'POST',
        data: $('#formPenjualan').serialize(),
        success: function(res) {
            window.location.href = '/penjualan/print/' + res.id;
        }
    });
});
