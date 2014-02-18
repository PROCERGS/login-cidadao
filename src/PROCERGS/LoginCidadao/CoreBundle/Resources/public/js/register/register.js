var pageWidth;

function responsive(aside, signUp) {
    pageWidth = $(window).width();
    if( pageWidth < 992 ){
        aside.remove();
        signUp.after(aside);
    } else {
        aside.remove();
        signUp.before(aside);
    }
}

$(document).ready(function(){
    $('#fos_user_registration_form_username').blur(function(){
        $elm = $(this);
        $.ajax({
            type: "GET",
            dataType: "json",
            url: validateUsernameUrl,
            data: { username: $(this).val() }
        })
        .done(function(result) {
            if(result.valid){
                $elm.parent().removeClass('has-error').find('.input-error').html("");
            }else{
                $elm.parent().addClass('has-error').find('.input-error').html('<ul><li>'+result.message+'</li></ul>');
            }
            return false;
        });
    });

    responsive( $('#register-aside'), $('#sign-up') );
});

$(window).resize(function() {
    responsive( $('#register-aside'), $('#sign-up') );
});