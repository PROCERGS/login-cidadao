// ID Card Infinite Grid Refresh
$('.infinitegrid').on('click', '.id-card-load-more', function (event) {
    event.stopPropagation();
    $(this).button('loading');
    var parent = $(this).closest('.infinitegrid');
    var url = $(this).attr('href');
    $.get(url, function (data) {
        var button = parent.find('.id-card-load-more');
        var perPage = button.data('per-page');
        var result = $(data).find('.infinitegrid-content .row.common-grid-result');
        var newButton = $(data).find('.id-card-load-more');
        if (newButton.length !== 0) {
            button.replaceWith(newButton);
        }
        if (result.size() < perPage || newButton.length === 0) {
            button.parent().hide();
        }
        parent.find('.infinitegrid-content').append(result);
    });
    return false;
});

$('.id-cards').on('click', '.id-card-edit, .id-card-add', function (event) {
    event.stopPropagation();
    var button = $(this);    
    button.attr('disabled', 'disabled');

    var url = button.attr('href') + '?state='+ $('#lc_idcard_select_state').val();
    history.pushState({}, '', url);

    url += ' .id-card-forms-placeholder';
    $('.id-card-forms-placeholder').slideUp().load(url, function () {
        $('.id-card-forms-placeholder').slideDown()
                .siblings('.anchor[name=id-card-forms-placeholder]')
                .ScrollTo();
        button.removeAttr('disabled');
    });
    return false;
});

$('.id-card').on('click', '.remove', function (event) {
    var idCard = $(this).closest('.id-card');
    $('.confirm-removal', idCard).slideDown();
    idCard.addClass('blockHover');
});

$('.id-card .confirm-removal').on('click', '.cancel', function (event) {
    $(this).closest('.confirm-removal').slideUp();
    $(this).closest('.id-card').removeClass('blockHover');
});
