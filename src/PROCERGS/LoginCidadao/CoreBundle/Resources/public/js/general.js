$(function() {

    // add bootstrap classes to forms
    $('.form-group input, .form-group select').not('.form-control').addClass('form-control');
    $('.form-group .input-error').not(':empty').parent('.form-group').addClass('has-error');

    // maks inputs
    $('#fos_user_registration_form_cep').mask('00000-000');

    $('#fos_user_registration_form_cpf').mask('000.000.000-00');


});