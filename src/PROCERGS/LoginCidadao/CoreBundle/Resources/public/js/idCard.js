// ID Card Infinite Grid Refresh
$('.infinitegrid').on('click', '.id-card-load-more', function (event) {
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
            button.addClass('disabled').attr('href', '#').html(messages.form.id_card.allIdsLoaded);
        }
        parent.find('.infinitegrid-content').append(result);
    });
    event.stopPropagation();
    return false;
});
