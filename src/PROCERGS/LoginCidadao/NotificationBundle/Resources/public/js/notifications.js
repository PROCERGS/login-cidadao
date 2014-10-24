// Notifications Infinite Grid Refresh
$('.infinitegrid').on('click', '.load-more', function (event) {
    var parent = $(this).closest('.infinitegrid');
    var url = $(this).attr('href');//.replace('OFFSET', lastId);
    $.get(url, function (data) {
        var button = parent.find('.load-more');
        var perPage = button.data('per-page');
        var result = $(data).find('.infinitegrid-content .notification-line');
        var newButton = $(data).find('.load-more');
        button.replaceWith(newButton);

        if (result.size() < perPage) {
            button.hide();
        }
        parent.find('.infinitegrid-content').append(result);
    });
    event.stopPropagation();
    return false;
});

$('.content.notifications .infinitegrid').on('click', 'a.notification', function (event) {
    event.stopPropagation();

    $('.content .notification').not(this).parent('.notification-line').removeClass('notification-open');
    $('.content .notification-content').slideUp();

    $(this).parent('.notification-line').stop().toggleClass('notification-open').promise().done(function() {
        var isOpen = $(this).is('.notification-open');
        if (isOpen) {
            $(this).children('.notification-content').hide().slideDown();
        } else {
            $(this).children('.notification-content').slideUp();
        }
    });

    if ($(this).is('.notification-unread')) {
        var notificationId = $(this).data('notification-id');
        var url = notification.config.mark_as_read.replace('0', notificationId);
        $.ajax({
            url: url,
            type: 'PUT',
            success: function (result) {
                $.each(result.read, function (index, value) {
                    $('.infinitegrid .notification[data-notification-id=' + value + ']')
                            .removeClass('notification-unread')
                            .addClass('notification-read');
                });
            }
        });
    }

    return false;
});
