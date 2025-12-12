$('#btnSavePrint').click(function() {
    $.ajax({
        url: '/penjualan/store',
        method: 'POST',
        data: $('#formPenjualan').serialize(),
        success: function(response) {
            if (response.success === true) {
                window.location.href = '/penjualan/print/' + response.data.id;
            } else {
                $('#' + response.form).focus().select();
                alert(response.message);
            }
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            alert('Terjadi kesalahan pada server.');
        }
    });
});
