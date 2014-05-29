if (typeof String.prototype.repeat !== 'function') {
    String.prototype.repeat = function( num ) {
        return new Array( num + 1 ).join( this );
    };
}
if (typeof String.prototype.capitalize !== 'function') {
    String.prototype.capitalize = function(lc, all) {
        if (all) {
            return this.split(" ").map(function(currentValue, index, array) {
                return currentValue.capitalize(lc);
            }, this).join(" ").split("-").map(
                    function(currentValue, index, array) {
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
      for (var i=0;i<vars.length;i++) {
        var pair = vars[i].split("=");
            // If first entry with this name
        if (typeof query_string[pair[0]] === "undefined") {
          query_string[pair[0]] = pair[1];
            // If second entry with this name
        } else if (typeof query_string[pair[0]] === "string") {
          var arr = [ query_string[pair[0]], pair[1] ];
          query_string[pair[0]] = arr;
            // If third or later entry with this name
        } else {
          query_string[pair[0]].push(pair[1]);
        }
      }
        return query_string;
    } ();

var validator = {};

validator.isValidCpf = function(cpf)
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

validator.formatCPF = function(element, cpf)
{
    if ($.isNumeric(cpf) && cpf.length === 11) {
        var cpfRegex = new RegExp('([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{2})', 'gi');
        cpf = cpf.replace(cpfRegex, '$1.$2.$3-$4');
        element.val(cpf).data('masked', true);
    }
};

validator.onKeyUpMultiformat = function(obj, e)
{
    var c= String.fromCharCode(e.which);
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
            cpf = cpf.substr(0,11);
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

validator.cep = {'parent': validator};
validator.cep.urlQuery = '/lc_consultaCep2';
validator.cep.findByCep = function(obj, callback) {
    if (obj.value === '') {
        validator.check.success(obj);
      return;
    }
    var cleanup = new RegExp('[. \\-]', 'gi');
    var val = obj.value.replace(cleanup, '');
    if (val === '' || val.length !== 8) {
        validator.check.error(obj, $('label[for=' + obj.id + ']').text() + ' invalido');
        return;
    }
     $.ajax({
         type: "GET",
         dataType: "json",
        url: validator.cep.urlQuery + '/' + val
    }).done(function(result) {
        if (result.code !== 200) {
            return this.fail(result);
             }
        if (result.items && result.items.length && callback) {
            callback(result.items[0]);
             }
        validator.check.success(obj);
    }).fail(function(result) {
        if (result.code !== 200) {
            validator.check.error(obj, $('label[for=' + obj.id + ']').text() + ' invalido');
            return;
         }
     });
};
validator.cep.popupConsult = function(obj, evt, callback) {
    var url = $(obj).attr('href') + '?callback='+callback;
    window.open(url, '', "width=600,height=450");
};

validator.mask = {'parent': validator};
validator.mask.int = function(e) {
    var charCode = e.which || e.keyCode;
    if ((charCode < 48 || charCode > 57) && (charCode != 8 && charCode != 46)) {
        e.returnValue = false;
        return false;
    }
    return true;
};
validator.mask.format = function(obj, mask, e) {
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
validator.check.mobile = function(obj, e) {
    if (obj.value.length) {
        var cel = obj.value.toString().replace(/\-|\.|\/|\(|\)| /g, "");
        if(cel.lengh < 8 || !$.isNumeric(cel)) {
            this.error(obj, $('label[for='+obj.id+']').text() + ' invalido!');
            return false;
        }
    }
    this.success(obj);
    return true;
};
validator.check.cep = function(obj, e) {
    if (obj.value.length) {
        var exp = /\d{2}\.\d{3}\-\d{3}/;
        if(!exp.test(obj.value)) {
            this.error(obj, $('label[for='+obj.id+']').text() + ' invalido!');
            return false;
        }
    }
    this.success(obj);
    return true;

};
validator.check.cpf = function(obj, e) {
    if (obj.value.length) {
        if(!this.parent.isValidCpf(obj.value))    {
            this.error(obj, $('label[for='+obj.id+']').text() + ' invalido!');
            return false;
        }
    }
    this.success(obj);
    return true;
};
validator.check.error = function(obj, msg) {
    var parent = $(obj).parent();
    parent.addClass('has-error has-feedback');
    parent.find('.input-error').html('<ul><li>'+msg+'</li></ul>');
};
validator.check.success = function(obj) {
    var parent = $(obj).parent();
    parent.removeClass('has-error has-feedback');
    parent.find('.input-error').html('');
};

function zeroPadding(str, size) {
    str = str.toString();
    return str.length < size ? zeroPadding("0" + str, size) : str;
    }
function checkVoterRegistration(inscricao) {
  var paddedInsc = inscricao;
  var dig1 = 0;
  var dig2 = 0;
  var tam = paddedInsc.length;
  var digitos = paddedInsc.substr(tam - 2, 2);
  var estado = paddedInsc.substr(tam - 4, 2);
  var titulo = paddedInsc.substr(0, tam - 2);
  var exce = (estado == '01') || (estado == '02');
  dig1 = (titulo.charCodeAt(0) - 48) * 9 + (titulo.charCodeAt(1) - 48) * 8
      + (titulo.charCodeAt(2) - 48) * 7 + (titulo.charCodeAt(3) - 48) * 6
      + (titulo.charCodeAt(4) - 48) * 5 + (titulo.charCodeAt(5) - 48) * 4
      + (titulo.charCodeAt(6) - 48) * 3 + (titulo.charCodeAt(7) - 48) * 2;
  var resto = (dig1 % 11);
  if (resto == 0) {
    if (exce) {
      dig1 = 1;
    } else {
      dig1 = 0;
    }
  } else {
    if (resto == 1) {
      dig1 = 0;
    } else {
      dig1 = 11 - resto;
    }
  }
  dig2 = (titulo.charCodeAt(8) - 48) * 4 + (titulo.charCodeAt(9) - 48) * 3
      + dig1 * 2;
  resto = (dig2 % 11);
  if (resto == 0) {
    if (exce) {
      dig2 = 1;
    } else {
      dig2 = 0;
    }
  } else {
    if (resto == 1) {
      dig2 = 0;
    } else {
      dig2 = 11 - resto;
    }
  }
  if ((digitos.charCodeAt(0) - 48 == dig1)
      && (digitos.charCodeAt(1) - 48 == dig2)) {
    return true; // Titulo valido
  } else {
    return false;
  }
}

$(function() {

    // add bootstrap classes to forms
    $('.form-group input, .form-group select').not('.form-control').addClass('form-control');
    $('.form-group .input-error').not(':empty').parent('.form-group').addClass('has-error has-feedback');
    $('form .has-error .form-control').on('focusin', function() {
        $(this).parent('.form-group').removeClass('has-error has-feedback');
    });

    $('.file-upload .btn-upload').on('click', function() {
        $(this).siblings('input[type="file"]').trigger('click');
    });

    $('.file-upload input[type="file"]').change(function() {
        var val = $(this).val();
        $(this).siblings('.file-name').html(val.match(/[^\\/]+$/)[0]);
    });

    $('#toggle-settings-nav').on('click', function() {
      $('.settings-nav, .settings-content').toggleClass('menu-open');
    });
    $('.nfgpopup').on('click', function (event){
      event.preventDefault();
        window.open($(this).attr('data-href'),'_blank', 'toolbar=0,location=0,scrollbars=no,resizable=no,top=0,left=500,width=400,height=750');
        return false;
    });
    $(document).on('click', 'a.link-popup', function (event){
        event.preventDefault();
        var e = $(this);
        var u = e.attr('data-href') ? e.attr('data-href') : e.attr('href');
        if (u) {
        	window.open(u,'_blank', e.attr('data-specs'));
        }
        return false;
      });
    $(document).on('submit', '.form-ajax', function(event){
    	event.preventDefault();
    	var e = $(this);
    	$.ajax({
    		type: e.attr('method'),
    		url: e.attr('action'),
    		data: e.serialize(),
    		dataType : 'html',
    		success : function(data, textStatus, jqXHR) {
    			$(e.attr('ajax-target')).html(data);
    		}
        });
    })
});
