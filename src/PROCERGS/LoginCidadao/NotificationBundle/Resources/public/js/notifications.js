// This is just a test
$('.load-more').click(function (event) {
    var lastId = $('.component.notifications.small .media').last().data('notification-id');
    $.get('http://lc.des.dona.to/app_dev.php/notifications/navbar/fragment/' + lastId, function (data) {
        var result = $(data).find('.media');
        console.log(result.size());
        if (result.size() < 8) {
            $('.load-more').hide();
        }
        $('.component.notifications.small').append(result);
    });
    event.stopPropagation();
});
