var citizenLogin = {
    navbar: {
        html: {{ html | raw }}
    }
};
var div = '';

citizenLogin.navbar.init = function(removeMargins) {
    citizenLogin.body = document.getElementsByTagName('body')[0];
    citizenLogin.head = document.getElementsByTagName('head')[0];

    if (removeMargins !== false) {
        citizenLogin.body.style.paddingTop = 0;
        citizenLogin.body.style.paddingLeft = 0;
        citizenLogin.body.style.paddingRight = 0;

        citizenLogin.body.style.marginTop = 0;
        citizenLogin.body.style.marginLeft = 0;
        citizenLogin.body.style.marginRight = 0;
    }

    var cssBootstrap = document.createElement('link');
    cssBootstrap.setAttribute('rel', 'stylesheet');
    cssBootstrap.setAttribute('href', 'http://lc.des.dona.to/app_dev.php/css/282c42f_part_1_bootstrap_2.css');

    var cssNavbar = document.createElement('link');
    cssNavbar.setAttribute('rel', 'stylesheet');
    cssNavbar.setAttribute('href', 'http://lc.des.dona.to/app_dev.php/css/282c42f_part_1_navbar_5.css');

    citizenLogin.head.appendChild(cssBootstrap);
    citizenLogin.head.appendChild(cssNavbar);

    div = document.createElement('div');
    div.innerHTML = citizenLogin.navbar.html.navbar;
    citizenLogin.body.insertBefore(div.firstChild, citizenLogin.body.firstChild);
};
