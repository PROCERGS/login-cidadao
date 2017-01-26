/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(document).ready(function () {
    $('form').preventDoubleSubmission();

    var current = $('.panel-compact .heading-container a.btn').not(':visible').get(0);
    var button = $(current).is('.btn-home-login') ? 'btn-home-login' : 'btn-home-register';
    if (undefined !== current) {
        history.replaceState({'button': button}, '', $(current).attr('href'));
    }

    $('.panel-heading .btn').on('click', function (event) {
        event.stopPropagation();
        if ($(this).is('a')) {
            var button = $(this).is('.btn-home-login') ? 'btn-home-login' : 'btn-home-register';
            history.pushState({'button': button}, '', $(this).attr('href'));
        }
        showBoxes(this);
        return false;
    });

    $('input.cpf').mask('000.000.000-00');
    $('input.birthdate').mask('00/00/0000');
});

function showBoxes(element) {
    if ($(element).is('.btn-home-login')) {
        $('.registration').not('a').slideUp('medium', function () {
            $(element).fadeOut('fast', function () {
                $('.btn-home-register').fadeIn();
            });
            $('.login').not('a').slideDown('medium', function () {
                $('.login form input:first').focus();
            });
        });
    } else {
        $('.login').not('a').slideUp('medium', function () {
            $(element).fadeOut('fast', function () {
                $('.btn-home-login').fadeIn();
            });
            $('.registration').not('a').slideDown('medium', function () {
                $('.registration form input:first').focus();
            });
        });
    }
}

window.onpopstate = function (event) {
    if (event.state) {
        showBoxes($("a." + event.state.button));
    }
};
