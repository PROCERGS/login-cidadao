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

$(function() {

    responsive( $('#register-aside'), $('#register-box') );

    // add bootstrap classes to forms
    $('.form-group input, .form-group select').not('.form-control').addClass('form-control');
    $('.form-group .input-error').not(':empty').parent('.form-group').addClass('has-error');

    // maks inputs
    $('#fos_user_registration_form_cep').mask('00000-000');

    $('#fos_user_registration_form_cpf').mask('000.000.000-00');


    // switch display application list
    $('.app-toggle .btn').on('click', function() {

        if ( !$(this).hasClass('active') ) {

            $('.app-toggle .btn').removeClass('active');
            $(this).addClass('active');
            $('#applications .list-group').css({'margin-top': '-30px', 'opacity' : 0 });

            var self = $(this);
            setTimeout( function(){
                switch (self.data("display")) {
                    case 'list':
                        $('#applications ul').removeClass('icon').addClass('list');
                        break;
                    case 'icon':
                        $('#applications ul').removeClass('list').addClass('icon');
                        break;
                }
                $('#applications .list-group').css({'margin-top' : 0, 'opacity' : 1});
            }, 1000);

        }
    });

    // close for msg-popup - error, alert, warning
    $('.msg-popup').on('click', '.btn', function() {
        $('.msg-popup').fadeOut();
    });

    $('#profile'). on('click', '.edit', function(e) {
        e.preventDefault();
        $('#profile input').attr('readonly', false);
        $('#profile .info').addClass('open');
    });

    $('#profile'). on('click', '.toggle', function(e) {
        e.preventDefault();
        $(this).toggleClass('open');
        $('#profile .info').toggleClass('open');
    });


});

$(window).resize(function() {
    responsive( $('#register-aside'), $('#register-box') );
});