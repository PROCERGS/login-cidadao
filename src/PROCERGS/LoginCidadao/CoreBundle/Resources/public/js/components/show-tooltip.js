/* 
 *  This file is part of the login-cidadao project or it's bundles.
 *  
 *  (c) Guilherme Donato <guilhermednt on github>
 *  
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

function showTooltip(element, title) {
    $(element).tooltip({
        trigger: 'manual',
        placement: 'bottom',
        title: title
    }).tooltip('show');

    $(element).data('tooltip-close-timeout', setTimeout(function () {
        $(element).tooltip('hide')
    }, 3000));
}
