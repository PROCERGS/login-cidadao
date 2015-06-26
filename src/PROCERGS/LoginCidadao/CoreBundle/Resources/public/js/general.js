// Fix navbar/settings nav position on small devices
$(document).ready(function () {
    var navbar = $('#lc-navbar');
    var settingsNav = $(".settings-nav");
    $(window).scroll(function () {
        if (navbar.visible(true)) {
            settingsNav.removeClass("top");
        } else {
            settingsNav.addClass("top");
        }
    })
})

if (typeof String.prototype.repeat !== 'function') {
    String.prototype.repeat = function (num) {
        return new Array(num + 1).join(this);
    };
}
if (typeof String.prototype.capitalize !== 'function') {
    String.prototype.capitalize = function (lc, all) {
        if (all) {
            return this.split(" ").map(function (currentValue, index, array) {
                return currentValue.capitalize(lc);
            }, this).join(" ").split("-").map(
                    function (currentValue, index, array) {
                        return currentValue.capitalize(false);
                    }, this).join("-");
        } else {
            return lc ? this.charAt(0).toUpperCase()
                    + this.slice(1).toLowerCase() : this.charAt(0)
                    .toUpperCase()
                    + this.slice(1);
        }
    };
}
var QueryString = function () {
    // This function is anonymous, is executed immediately and
    // the return value is assigned to QueryString!
    var query_string = {};
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        // If first entry with this name
        if (typeof query_string[pair[0]] === "undefined") {
            query_string[pair[0]] = pair[1];
            // If second entry with this name
        } else if (typeof query_string[pair[0]] === "string") {
            var arr = [query_string[pair[0]], pair[1]];
            query_string[pair[0]] = arr;
            // If third or later entry with this name
        } else {
            query_string[pair[0]].push(pair[1]);
        }
    }
    return query_string;
}();

var validator = {};

validator.isValidCpf = function (cpf)
{
    var checkRepeat = new RegExp('([0-9])\\1{10}', 'g');
    var cleanup = new RegExp('[. \\-]', 'gi');
    cpf = cpf.replace(cleanup, '');

    var digitoUm = 0;
    var digitoDois = 0;

    if (cpf.length !== 11 || !$.isNumeric(cpf) || checkRepeat.test(cpf)) {
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

validator.formatCPF = function (element, cpf)
{
    if ($.isNumeric(cpf) && cpf.length === 11) {
        var cpfRegex = new RegExp('([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{2})', 'gi');
        cpf = cpf.replace(cpfRegex, '$1.$2.$3-$4');
        element.val(cpf).data('masked', true);
    }
};

validator.onKeyUpMultiformat = function (obj, e)
{
    var c = String.fromCharCode(e.which);
    var isWordcharacter = c.match(/\w/);

    var val = $(obj).val().trim();
    var masked = $(obj).data('masked');
    var cpf = val;
    var cleanup = new RegExp('[. \\-]', 'gi');
    cpf = cpf.replace(cleanup, '');


    if (!isWordcharacter && e.keyCode !== 0) {
        validator.formatCPF($(obj), cpf);
        return;
    }

    if ($.isNumeric(cpf)) {
        if (cpf.length > 11) {
            cpf = cpf.substr(0, 11);
        }
        validator.formatCPF($(obj), cpf);
    } else {
        if (masked === true) {
            val = val.replace(/([0-9]{3})[.]([0-9]{3})[.]([0-9]{3})-([0-9]{2})/, '$1$2$3$4');
            $(obj).val(val);
        }
    }

    return false;
};

validator.mask = {'parent': validator};
validator.mask.int = function (e) {
    var charCode = e.which || e.keyCode;
    if ((charCode < 48 || charCode > 57) && (charCode != 8 && charCode != 46)) {
        e.returnValue = false;
        return false;
    }
    return true;
};
validator.mask.format = function (obj, mask, e) {
    var masked;
    var numerics = obj.value.toString().replace(/\-|\.|\/|\(|\)| /g, "");
    var pos = 0;
    var newVal = "";
    var sizeMask = numerics.length;

    if (e && e.keyCode != 8) {
        for (var i = 0; i <= sizeMask; i++) {
            masked = ((mask.charAt(i) == "-") || (mask.charAt(i) == ".") || (mask
                    .charAt(i) == "/"));
            masked = masked
                    || ((mask.charAt(i) == "(") || (mask.charAt(i) == ")") || (mask
                            .charAt(i) == " "));
            if (masked) {
                newVal += mask.charAt(i);
                sizeMask++;
            } else {
                newVal += numerics.charAt(pos);
                pos++;
            }
        }
        obj.value = newVal;
        return true;
    } else {
        return true;
    }
};

validator.check = {'parent': validator};
validator.check.mobile = function (obj, e) {
    if (obj.value.length) {
        var cel = obj.value.toString().replace(/\-|\.|\/|\(|\)| /g, "");
        if (cel.lengh < 8 || !$.isNumeric(cel)) {
            this.error(obj, $('label[for=' + obj.id + ']').text() + ' invalido!');
            return false;
        }
    }
    this.success(obj);
    return true;
};
validator.check.cep = function (obj, e) {
    if (obj.value.length) {
        var exp = /\d{2}\.\d{3}\-\d{3}/;
        if (!exp.test(obj.value)) {
            this.error(obj, $('label[for=' + obj.id + ']').text() + ' invalido!');
            return false;
        }
    }
    this.success(obj);
    return true;

};
validator.check.cpf = function (obj, e) {
    if (obj.value.length) {
        if (!this.parent.isValidCpf(obj.value)) {
            this.error(obj, $('label[for=' + obj.id + ']').text() + ' invalido!');
            return false;
        }
    }
    this.success(obj);
    return true;
};
validator.check.error = function (obj, msg) {
    var parent = $(obj).parent();
    parent.addClass('has-error has-feedback');
    parent.find('.input-error').html('<ul><li>' + msg + '</li></ul>');
};
validator.check.success = function (obj) {
    var parent = $(obj).parent();
    parent.removeClass('has-error has-feedback');
    parent.find('.input-error').html('');
};

function zeroPadding(str, size) {
    str = str.toString();
    return str.length < size ? zeroPadding("0" + str, size) : str;
}

//$(document).ajaxSend(lcAutoLoader.start);
//$(document).ajaxComplete(lcAutoLoader.stop);
function Pwindow(options) {
    var opts = {
        id: '__default',
        label: '',
        type: 'get',
        dataType: 'html',
        selector: null,
        _selector: function () {
            return opts.selector || $('#' + opts.id + ' .modal-content');
        },
        beforeSend: function () {
            $('#' + opts.id).modal('hide');
            opts._selector().html('');
        },
        success: function (data, textStatus, jqXHR) {
            opts._selector().html(data);
        },
        complete: function () {
            $('#' + opts.id).modal('show');
        }
    };
    opts = $.extend(true, {}, opts, options);
    if (!$('#' + opts.id).length) {
        var html = '<div id="' + opts.id + '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="' + opts.label + '" aria-hidden="true">' +
                '<div class="modal-dialog"><div class="modal-content"></div></div></div>';
        $('.settings-content').append(html);
    }
    if (!opts.url) {
        return alert('dunno');
    }
    $.ajax(opts);
}
var lcInfiniteGrid = {
    /* aqui eh funcao que cria a instancia do infintyscroll quando clica num botao*/
    "scrollNextButton": function (event) {
        /* escondendo o botao */
        $(this).addClass('infinitescroll-loading');
        /*o data-retrive eh onde esta armazedo o id do elemento que vamos criar a instancia do scroll, eh onde esta a grid */
        lcInfiniteGrid.common($(this).attr('data-retrive'));
        $($(this).attr('data-retrive')).infinitescroll('retrieve');
    },
    /* aqui colocamos metodo para criar instancias do infinitescroll*/
    "common": function (_id) {
        /*testamos se ja tem o bind do plugin infinitescroll 'https://github.com/paulirish/infinite-scroll', pois as vezes o elemento eh trocado e perde  a instancia */
        if (!$(_id).data('infinitescroll')) {
            /*opcoes padroes para um grid padrao usado o html criado pelo grid_layout.html.twig*/
            var opts = {
                debug: false,
                navSelector: _id + ' .pagination',
                nextSelector: _id + ' .pagination a:last',
                itemSelector: _id + ' .row.common-grid-result',
                contentSelector: _id + ' .tab-pane.active',
                bufferPx: 0,
                state: {
                    /* tem que dizer qual o numero da pagina atual*/
                    currPage: Number($(_id).attr('data-grid-currpage'))
                },
                loading: {
                    /*aqui é para colocar um loader se quiser*/
                    msg: $('<div></div>'),
                    img: null
                },
                pathParse: function (path, currPage) {
                    /* aqui colocamos uma expressao para filtrar da url qual o numero da pagina, pois isso importa para o plugin*/
                    var matches = path.match(/^(.*[?|&]page=)\d+(.*|$)/);
                    if (matches) {
                        return matches.slice(1);
                    }
                }
            };
            /* fazemos um merge dos parametros extras que podem vir da grid*/
            var extraOpts = $(_id).data('grid-extra-opts');
            if (extraOpts) {
                if (extraOpts.binder) {
                    extraOpts.binder = $(extraOpts.binder);
                }
            }
            opts = $.extend(true, {}, opts, extraOpts);
            $(_id).infinitescroll(opts,
                    function (newElements, data, url) {
                        /*somente uma funcao de callback para ver se ja atingimos a ultima pagina para tirar o botao paginador*/
                        var isLast = false;
                        for (x in newElements) {
                            if (isLast = newElements[x].classList.contains("row-last")) {
                                break;
                            }
                        }
                        if (isLast) {
                            $(_id + ' .infinitescroll-next-button').removeClass('infinitescroll-loading');
                        }
                    });
            /* aqui testamos se é para usar o autoscroll ou nao */
            if (extraOpts && extraOpts.behavior && extraOpts.behavior == 'local') {
                /*console.log('here');*/
            } else {
                /*isso aqui eh para tirar o autoscroll*/
                $(window).unbind('.infscr');
            }
        }
    },
    "startUp": function () {
        /*funcao para criar instancia ao iniciar a pagina, important quando temo autoscroll sem o botao paginador*/
        lcInfiniteGrid.common('#' + $(this).attr('id'));
    }
}
/* conjunto de funcao para fazer um multiplo select, sem a listagem de todas as opcoes ao mesmo tempo */
var lcAcWidget = {
    /* funcao que dispara quando se clica no botao filtra do multiplo select */
    onSearch: function (event) {
        var self = $(this);
        /*aqui pegamos os parametro de filtragem via ajax*/
        var opts = self.data('ac-attr');
        opts = opts.filter;
        var data = {};
        /* montamos os dados que vao ser enviado via ajax*/
        for (var x in opts.extra_form_prop) {
            data[x] = $('#' + opts.extra_form_prop[x]).val();
        }
        data[opts.search_prop] = $(this).parent().prev().val();
        $.ajax({
            type: 'get',
            url: opts.route,
            data: {"ac_data": data},
            dataType: 'html',
            success: function (data, textStatus, jqXHR) {
                /*aqui recebemos uma grid e disparamos os infinityscroll */
                self.parents().find('.ac-magicbox .ac-scrollspy-opts').html(data);
                lcInfiniteGrid.common('#' + $(data).attr('id'));
            }
        });
    },
    /*aqui eh acao do botao do multiple select, q quando diparado retira o item da grid de pesquisa e coloca na grid dos selecionados*/
    onClickFilteredItem: function (event) {
        var src = '.ac-scrollspy-opts';
        var target = '.ac-scrollspy-opts-selected .common-grid-results .tab-pane.active';
        var id = '#' + $(this).attr('id');
        if (!$(target).length || $(target + " .row:has(" + id + "):last").length) {
            $(src + " .row:has(" + id + "):last").remove();
        } else {
            $(src + " .row:has(" + id + "):last").appendTo(target);
        }
    },
    /* aqui eh acao do botao do multiple select, q quando diparado retira o item da grid dos selecionado e coloca na grid de pesquisa*/
    onClickSelectedItem: function (event) {
        var target = '.ac-scrollspy-opts .common-grid-results .tab-pane.active';
        var src = '.ac-scrollspy-opts-selected';
        var id = '#' + $(this).attr('id');
        if (!$(target).length || $(target + " .row:has(" + id + "):last").length) {
            $(src + " .row:has(" + id + "):last").remove();
        } else {
            $(src + " .row:has(" + id + "):last").appendTo(target);
        }
    },
    /* aqui é quando clico para poder abrir o multiplo select*/
    onClickSearchEnable: function (event) {
        $('.ac-search-loader').addClass('show');
        /* pegamos a referencia de qual elemento vamos criar nossa instancia do multiplesect*/
        var _id = '#' + $(this).attr('data-ac-reference');
        var mb = $(_id + ' + .ac-magicbox');
        $(_id).parent().find('.ac-tags-toolbar').toggleClass('in');
        /* checamos se o ja esta aberto ou nao o multipleselect*/
        if (mb.hasClass('in')) {
            mb.toggleClass('in');
            mb.find('.ac-scrollspy-opts-selected').html();
            mb.find('.ac-scrollspy-opts').html();
            return;
        }
        var opts = mb.find('[data-ac-attr]').data('ac-attr');
        /* criar uma funcao de callback apenas para disparar na instanciacao do nosso componente, para nao termos grid de selectionado vazia*/
        var callback = function () {
            var data = {};
            /*montamos os dados que serao enviados via ajax */
            if (opts.selected.extra_form_prop) {
                for (var x in opts.selected.extra_form_prop) {
                    data[x] = $('#' + opts.selected.extra_form_prop[x]).val();
                }
            }
            $.ajax({
                type: 'get',
                url: opts.selected.route,
                /*encapsulamos numa unica variavel os dados do ajax para faciliar a vida quando a grid infinita tiver que ficar repopulando os dados*/
                data: {"ac_data": data},
                dataType: 'html',
                success: function (data, textStatus, jqXHR) {
                    console.log(data);
                    $('.ac-search-loader').removeClass('show');
                    /*pegamos a grid de retorno e colocamos dentro da div de exibicao*/
                    mb.find('.ac-scrollspy-opts-selected').html(data);
                    mb.toggleClass('in');
                    $('html, body').animate({scrollTop: mb.offset().top});
                }
            });
        }
        /* aqui temos um aquecimento caso nao tenhamos os itens ja selecionados*/
        var warmup = mb.find('.ac-scrollspy-opts:empty');
        if (warmup.length) {
            var data = {};
            /*montamos os dados que serao enviados via ajax*/
            for (var x in opts.filter.extra_form_prop) {
                data[x] = $('#' + opts.filter.extra_form_prop[x]).val();
            }
            $.ajax({
                type: 'get',
                url: opts.filter.route,
                /*encapsulamos numa unica variavel os dados do ajax para faciliar a vida quando a grid infinita tiver que ficar repopulando os dados*/
                data: {"ac_data": data},
                dataType: 'html',
                success: function (data, textStatus, jqXHR) {
                    /*colocamos os dos dos na grid e disparmos o inifinity scroll*/
                    warmup.html(data);
                    lcInfiniteGrid.common('#' + $(data).attr('id'));
                    callback();
                }
            });
        } else {
            callback();
        }

    },
    /* funcao apenas para fechar o componente*/
    onClickSearchCancel: function (event) {
        var _id = '#' + $(this).attr('data-ac-reference');
        var mb = $(_id + ' + .ac-magicbox');
        mb.toggleClass('in');
        $(_id).parent().find('.ac-tags-toolbar').toggleClass('in');
    },
    /*funcao para pegar os itens selecionados e converte-los para um select:multiple para poder ser enviado via formulario*/
    onClickSearchCommit: function (event) {
        /*limpamos os dados que estao presente no select:multiple*/
        var _id = '#' + $(this).attr('data-ac-reference');
        $(_id + ' option').remove();
        /*limpamos as representações visuais de labels das opções do select:multiple*/
        var mb = $(_id + ' + .ac-magicbox');
        var tag = mb.parent().find('.ac-tags-toolbar');
        tag.children().remove();
        var opts = mb.find('[data-ac-attr]').data('ac-attr');
        /*pegamos os dados que estao na grid dos selecionados e colocamos eles select:multiple e criamos suas representacoes visuais*/
        mb.find('.ac-scrollspy-opts-selected .ac-search-select').each(function (a, b) {
            /*pegamos os dados ocultos da grid*/
            var data = $(b).data('row');
            /*criamso as opcoes para colocar no select:multiple*/
            $(document.createElement('option')).attr('selected', 'selected').attr('value', data[opts.property_value]).text(data[opts.property_text]).appendTo(_id);
            /*criamso as "tags" que sao representacoes visuais das opcoes do select:multiple*/
            $(document.createElement('div')).addClass('btn-group').append(
                    $(document.createElement('span')).addClass('label label-info').attr('type', 'button').text(data[opts.property_text])
                    ).appendTo(tag);
        });
        /*exibimos a barra com as novas "tags" representando os itens do select:multiple*/
        mb.toggleClass('in');
        $(_id).parent().find('.ac-tags-toolbar').toggleClass('in');
    },
};
$(function () {

    $('.file-upload .btn-upload').on('click', function () {
        $('input[type="file"]', '.file-upload').trigger('click');
    });

    $('.file-upload input[type="file"]').change(function () {
        var fileName = $(this).val().match(/[^\\/]+$/)[0];
        $('.file-upload .activity-desc span.upload strong').html(fileName).parent().show();
        $(this).siblings('.file-name').html(fileName);
    });

    $('.file-upload .buttons-toggle input[type=radio]').on('change', function () {
        $('.file-upload .activity-desc>span').hide();
        var file = $('input[type="file"]', '.file-upload');
        if ($('.use-facebook').is(":checked")) {
            file.wrap('<form>').parent('form').trigger('reset');
            file.unwrap();
            $('.file-upload .activity-desc span.facebook-pic').show();
        } else {
            if (file.val() !== "") {
                var fileName = file.val().match(/[^\\/]+$/)[0];
                $('.file-upload .activity-desc span.upload strong').html(fileName).parent().show();
            }
        }
    });

    // sidebar behavior on mobile
    $('#toggle-settings-nav').on('click', function () {
        $('body').toggleClass('menu-open', 'no-scroll');
    });

    $(document).on('click', 'a.link-popup', function (event) {
        event.preventDefault();
        var e = $(this);
        var u = e.attr('data-href') ? e.attr('data-href') : e.attr('href');
        if (u) {
            window.open(u, '_blank', e.attr('data-specs'));
        }
        return false;
    });
    $(document).on('submit', '.form-ajax', function (event) {
        event.preventDefault();
        var e = $(this);
        $.ajax({
            type: e.attr('method'),
            url: e.attr('action'),
            data: e.serialize(),
            dataType: 'html',
            success: function (data, textStatus, jqXHR) {
                $(e.attr('ajax-target')).html(data);
                history.pushState({infiniteGrid: data}, '', '');
            }
        });
    });
    // Ajax history fix
    var currentState = history.state;
    if (currentState && currentState.infiniteGrid) {
        $("#ajax-result").html(currentState.infiniteGrid);
    }


    $(document).on('click', '.infinitescroll-next-button', lcInfiniteGrid.scrollNextButton);
    $('[data-infinite-grid="true"]').each(lcInfiniteGrid.startUp);
    $("[data-enable-switch='1']").bootstrapSwitch();
    $(document).on('click', "[data-ac-attr]", lcAcWidget.onSearch);
    $(document).on('click', ".ac-scrollspy-opts .ac-search-select", lcAcWidget.onClickFilteredItem);
    $(document).on('click', ".ac-scrollspy-opts-selected .ac-search-select", lcAcWidget.onClickSelectedItem);
    $(document).on('click', ".ac-search-enable", lcAcWidget.onClickSearchEnable);
    $(document).on('click', ".ac-search-cancel", lcAcWidget.onClickSearchCancel);
    $(document).on('click', ".ac-search-commit", lcAcWidget.onClickSearchCommit);
    $(document).on('change mousedown', 'select[readonly="readonly"]', function (event) {
        event.stopPropagation();
        event.preventDefault();
        $(this).val($(this).parents().find('[selected="selected"]').val());
    });
});

// navbar - notifications
$(function () {
    var navbarCount = $("#lc-navbar-ul .notification-total-unread-badge");

    $("#navbarUnread .notification-unread").on("click", function (event) {
        event.preventDefault();
        var self = $(this);
        var url = notificationUrl;
        url += '?' + $.param($.parseJSON(self.attr('data-row')));

        Pwindow({
            id: 'notification-modal',
            url: url,
            dataType: 'json',
            selector: $('#notification-modal .modal-body'),
            success: function (data, textStatus, jqXHR) {
                if (!data) {
                    return;
                }
                if (data.wasread) {
                    var count = parseInt(navbarCount.text()) - 1;
                    $('.notification-total-unread-badge').text(count);
                    if (self.data('row').client_id) {
                        var sideSelector = $('#inbox-count-unread-' + self.data('row').client_id);
                        if (sideSelector.length) {
                            sideSelector.contents().first().replaceWith(document.createTextNode(parseInt(sideSelector.text()) - 1));
                        }
                    }
                }
                if (!data.htmltpl) {
                    data.htmltpl = '';
                }
                this.selector.html(data.htmltpl);
            }
        });

    });
});