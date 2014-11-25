$(function () {
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

    notification.toggleOpen = function (clicked) {
        // Close other notifications
        $('.content .notification').not(clicked).parent('.notification-line').removeClass('notification-open');
        $('.content .notification-content').slideUp();

        // Animate
        $(clicked).parent('.notification-line').stop().toggleClass('notification-open').promise().done(function () {
            var isOpen = $(this).is('.notification-open');
            if (isOpen) {
                $(this).children('.notification-content').hide().slideDown();
            } else {
                $(this).children('.notification-content').slideUp();
            }
        });

        // Mark as read
        if ($(clicked).is('.notification-unread')) {
            var notificationId = $(clicked).data('notification-id');
            $('.infinitegrid .notification[data-notification-id=' + notificationId + ']')
                    .removeClass('notification-unread')
                    .addClass('notification-read');
            notification.updateNotificationCounters();
        }
    };

    $('.content.notifications .infinitegrid').on('click', 'a.notification', function (event) {
        event.stopPropagation();

        // Do we have this in cache already? If not, get it.
        var loaded = $(this).siblings('.notification-content').data('loaded');
        if (loaded === false || loaded === 'false') {
            var notificationId = $(this).data('notification-id');
            var url = notification.config.read_notification.replace('0', notificationId);

            var notificationCallback = function (clicked, callback) {
                return function (data, textStatus, jqXHR) {
                    $(clicked).siblings('.notification-content')
                            .data('loaded', true).html(data);
                    callback(clicked);
                }
            };
            $.get(url, notificationCallback(this, notification.toggleOpen));
        } else {
            notification.toggleOpen(this);
        }

        return false;
    });
});