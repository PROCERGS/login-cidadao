// Notifications Infinite Grid Refresh
$('.infinitegrid').on('click', '.notification-load-more', function (event) {
    $(this).button('loading');
    var parent = $(this).closest('.infinitegrid');
    var url = $(this).attr('href');
    $.get(url, function (data) {
        var button = parent.find('.notification-load-more');
        var perPage = button.data('per-page');
        var result = $(data).find('.infinitegrid-content .notification-line');
        var newButton = $(data).find('.notification-load-more');
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

    $(this).parent('.notification-line').stop().toggleClass('notification-open').promise().done(function () {
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

                // Update notifications counts
                var url = notification.config.count_unread;
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (result) {
                        $.each(result, function (index, value) {
                            if (index === 'total') {
                                $('.notification-total-unread-badge').html(value);
                            } else {
                                var client = value.id;
                                var count = value.total;
                                $('.notification-count-client[data-client-id=' + client + ']').html(count);
                            }
                        });
                    }
                });
            }
        });
    }

    return false;
});
