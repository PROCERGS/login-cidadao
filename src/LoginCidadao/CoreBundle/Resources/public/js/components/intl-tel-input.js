/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var findNthNumber = function (str, n) {
    var offset = 0;
    while (n > 0) {
        offset += str.substr(offset).search(/\d/) + 1;
        n--;
    }
    return offset;
};

var getRelativePosition = function (str, absPos) {
    return str.substring(0, absPos).replace(/[^\d]/g, '').length;
};

$(document).ready(function () {
    $(".intl-tel").intlTelInput({
        preferredCountries: intlTelInputPreferredCountries,
        utilsScript: intlTelInputUtilsScriptUrl
    });


    $(".intl-tel").on('input', function () {
        var currentFormat = ($(this).val()[0] === "+") ? intlTelInputUtils.numberFormat.INTERNATIONAL : intlTelInputUtils.numberFormat.NATIONAL;

        var currVal = $(this).val(),
            newVal = $(this).intlTelInput("getNumber", currentFormat),
            absStart = findNthNumber(newVal, getRelativePosition(currVal, this.selectionStart)),
            absEnd = findNthNumber(newVal, getRelativePosition(currVal, this.selectionEnd));

        $(this).val(newVal);

        this.setSelectionRange(absStart, absEnd);
    });

    $("form").submit(function () {
        $('.intl-tel').each(function () {
            $(this).val($(this).intlTelInput("getNumber", intlTelInputUtils.numberFormat.E164));
        });
    });
});
