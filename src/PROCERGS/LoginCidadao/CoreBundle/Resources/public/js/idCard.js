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

$('.infinitegrid').on('click', '.edit-row-grid', function (event) {
    event.stopPropagation();
    var button = $(this);
    button.attr('disabled', 'disabled');

    var url = button.attr('href');
    history.pushState({}, '', url);

    url += ' .id-card-forms-placeholder';
    $('.id-card-forms-placeholder').slideUp().load(url, function () {
        $('.id-card-forms-placeholder').slideDown();
        button.removeAttr('disabled');
    });
    return false;
});

$('.infinitegrid').on('click', 'button.remove-row-grid', function (event) {
    var infiniteGrid = $(this).closest('.infinitegrid');
    $('.confirm-removal[data-id=' + $(this).data('id') + ']', infiniteGrid).slideDown();
});

$('.infinitegrid .confirm-removal').on('click', '.cancel', function (event) {
    $(this).closest('.confirm-removal').slideUp();
});
