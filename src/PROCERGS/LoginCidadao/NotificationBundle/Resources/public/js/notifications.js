// This is just a test
$('.load-more').click(function (event) {
    var lastId = $('.component.notifications.small .media').last().data('notification-id');
    $.get(notification.config.navbar_fragment_url + lastId, function (data) {
        var result = $(data).find('.media');
        console.log(result.size());
        if (result.size() < 8) {
            $('.load-more').hide();
        }
        $('.component.notifications.small').append(result);
    });
    event.stopPropagation();
});

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
