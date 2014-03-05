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
String.prototype.repeat = function( num ) {
    return new Array( num + 1 ).join( this );
};
var validador = {};
validador.isValidCpf = function (cpf)
{
    var cleanup = new RegExp('[. -A-Za-z]', 'g');
    var checkRepeat = new RegExp('([0-9])\\1{10}', 'g');

    cpf = cpf.replace(cleanup, '');
    var digitoUm = 0;
    var digitoDois = 0;

    if (cpf.length !== 11 || checkRepeat.test(cpf)) {
        return false;
    }

    for (var i = 0, x = 10; i <= 8; i++, x--) {
        digitoUm += cpf[i] * x;
    }
    for (var i = 0, x = 11; i <= 9; i++, x--) {
        var iStr = i.toString();
        if (iStr.repeat(11) === cpf) {
            return false;
        }
        digitoDois += cpf[i] * x;
    }

    var calculoUm = ((digitoUm % 11) < 2) ? 0 : 11 - (digitoUm % 11);
    var calculoDois = ((digitoDois % 11) < 2) ? 0 : 11 - (digitoDois % 11);
    if (calculoUm.toString() !== cpf[9] || calculoDois.toString() !== cpf[10]) {
        return false;
    }
    return true;
};
validador.formatCPF = function (element, cpf)
{
    if ($.isNumeric(cpf) && cpf.length === 11) {
        var cpfRegex = new RegExp('([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{2})', 'gi');
        cpf = cpf.replace(cpfRegex, '$1.$2.$3-$4');
        element.val(cpf).data('masked', true);
    }
};
validador.onKeyUpMultiformat = function (obj, e)
{
    var c= String.fromCharCode(e.which);
    var isWordcharacter = c.match(/\w/);

    var val = $(obj).val().trim();
    var masked = $(obj).data('masked');
    var cpf = val;
    var cleanup = new RegExp('[. \\-]', 'gi');
    cpf = cpf.replace(cleanup, '');


    if (!isWordcharacter && e.keyCode !== 0) {
    	validador.formatCPF($(obj), cpf);
        return;
    }

    if ($.isNumeric(cpf)) {
        if (cpf.length > 11) {
            cpf = cpf.substr(0,11);
        }
        validador.formatCPF($(obj), cpf);
    } else {
        if (masked === true) {
            val = val.replace(/([0-9]{3})[.]([0-9]{3})[.]([0-9]{3})-([0-9]{2})/, '$1$2$3$4');
            $(obj).val(val);
        }
    }

    return false;
};
$(function() {

    responsive( $('#register-aside'), $('#register-box') );

    // add bootstrap classes to forms
    $('.form-group input, .form-group select').not('.form-control').addClass('form-control');
    $('.form-group .input-error').not(':empty').parent('.form-group').addClass('has-error has-feedback');
    $('form .has-error .form-control').on('focusin', function() {
        $(this).parent('.form-group').removeClass('has-error has-feedback');
    });

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

    $('.file-upload .btn-upload').on('click', function() {
        $(this).siblings('input[type="file"]').trigger('click');
    });

    $('.file-upload input[type="file"]').change(function() {
        var val = $(this).val();
        $(this).siblings('.file-name').html(val.match(/[^\\/]+$/)[0]);
    });

});

$(window).resize(function() {
    responsive( $('#register-aside'), $('#register-box') );
});