
$(document).ready(function(){
    $(".navbar .navbar-nav .btn.login").click(function(){
        var url = $(this).attr('href');
        window.open(url, '', "width=400,height=400");
        return false;
    });
});
