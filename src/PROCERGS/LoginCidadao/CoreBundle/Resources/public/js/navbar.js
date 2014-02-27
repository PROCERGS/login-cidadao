
$(document).ready(function(){
    $(".navbar .btn-login").click(function(){
        var url = $(this).attr('data-href');
        window.open(url, '', "width=600,height=450");
        return false;
    });
});
