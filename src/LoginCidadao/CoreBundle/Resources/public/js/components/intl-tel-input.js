/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(document).ready(function () {
    $(".intl-tel").intlTelInput({
        preferredCountries: ["br"],
        utilsScript: intlTelInputUtilsScriptUrl
    });


    $(".intl-tel").on('blur', function () {
        var currentFormat = ($(this).val()[0] === "+") ? intlTelInputUtils.numberFormat.INTERNATIONAL : intlTelInputUtils.numberFormat.NATIONAL;
        $(this).val($(this).intlTelInput("getNumber", currentFormat));
    });

    $("form").submit(function () {
        $('.intl-tel').each(function () {
            $(this).val($(this).intlTelInput("getNumber", intlTelInputUtils.numberFormat.E164));
        });
    });
});
