$(function() {
    // detect IE to apply css
    function getIe(){var e=-1;if(navigator.appName=="Microsoft Internet Explorer"){var t=navigator.userAgent;var n=new RegExp("MSIE ([0-9]{1,}[.0-9]{0,})");if(n.exec(t)!==null){e=parseFloat(RegExp.$1)}$("body").addClass("ie-"+e);$('input, textarea').placeholder();}}getIe();


    $('.onoffswitch-label').click(function(){
        $(this).parent().toggleClass('onoffswitch-checked');
    });


});