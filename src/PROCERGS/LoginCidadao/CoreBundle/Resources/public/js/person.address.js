$(document).ready(function () {
    $('.btn.address-remove').on('click', function () {
        var id = $(this).data('address-id');
        $('.remove-box[data-address-id=' + id + ']').slideDown();
    });
    $('.remove-box .cancel').on('click', function () {
        $(this).closest('.remove-box').slideUp();
    });
});
