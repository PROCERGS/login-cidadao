// Notifications Infinite Grid Refresh
$('.infinitegrid').on('click', '.load-more', function (event) {
    var url = $(this).attr('href');//.replace('OFFSET', lastId);
    $.get(url, function (data) {
        var button = $('.infinitegrid .load-more');
        var perPage = button.data('per-page');
        var result = $(data).find('.infinitegrid-content .notification');
        var newButton = $(data).find('.load-more');
        button.replaceWith(newButton);

        if (result.size() < perPage) {
            button.hide();
        }
        $('.infinitegrid .infinitegrid-content').append(result);
    });
    event.stopPropagation();
    return false;
});

$('.infinitegrid').on('click', 'a.notification', function (event) {
    $(this).parent().stop().toggleClass('notification-open').promise().done(function() {
        var isOpen = $(this).is('.notification-open');
        if (isOpen) {
            console.log("down");
            $(this).children('.notification-content').hide().slideDown();
        } else {
            $(this).children('.notification-content').slideUp();
        }
    });

    event.stopPropagation();
    return false;
});

/*
$('li.btn-group.notifications').on('shown.bs.dropdown', function () {
    var firstId = $('.component.notifications.small .media:visible').first().data('notification-id');
    var lastId = $('.component.notifications.small .media:visible').last().data('notification-id');

    var url = notification.config.mark_as_read.replace('0', firstId).replace('9', lastId);
    $.ajax({
        url: url,
        type: 'PUT',
        success: function (result) {
            $.each(result.read, function(index, value) {
                $('.component.notifications.small .media[data-notification-id='+value+']').css({ 'background-color': '#f00' });
            });
        }
    });
});
*/