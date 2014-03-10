var pageWidth;

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
var validador = {};
validador.isValidCpf = function (cpf)
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
validador.cep = { 'parent': validador };
validador.cep.urlQuery = '/lc_consultaCep2';
validador.cep.findByCep = function (obj, callback) {
    //console.trace();    
    var cleanup = new RegExp('[. \\-]', 'gi');
    var val = obj.value.replace(cleanup, '');
     $.ajax({
         type: "GET",
         dataType: "json",
         url: validador.cep.urlQuery,
         data: {'cep' : val },
         success : function (data1, textStatus, jqXHR) {             
             if (data1.code > 0) {
                 validador.check.error(obj, $('label[for='+obj.id+']').text() +' invalido');
                 return;
             }             
             if (data1.itens && data1.itens.length && callback) {
                 callback(data1.itens[0]);
             }
             validador.check.success(obj);
         }
     });
};
validador.cep.popupConsult = function (obj, evt, cepField, callback) {
    var url = $(obj).attr('href') + '?cepField='+cepField+'&callback='+callback;
    window.open(url, '', "width=600,height=450");
};

validador.mask = { 'parent': validador };
validador.mask.int = function (e){    
    var charCode = e.which || e.keyCode;
    if ((charCode < 48 || charCode > 57) && (charCode != 8 && charCode != 46)) {
        e.returnValue = false;
        return false;
    }
    return true;
};
validador.mask.format = function(obj, mask, e) {
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

validador.check = { 'parent': validador };
validador.check.mobile = function (obj, e){
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
validador.check.cep = function (obj, e){    
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
validador.check.cpf = function (obj, e) {
    if (obj.value.length) {
        if(!this.parent.isValidCpf(obj.value))    {
            this.error(obj, $('label[for='+obj.id+']').text() + ' invalido!');
            return false;
        }
    }
    this.success(obj);
    return true;
};
validador.check.error = function (obj, msg) {    
    var parent = $(obj).parent();
    parent.addClass('has-error has-feedback');
    parent.find('.input-error').html('<ul><li>'+msg+'</li></ul>');
};
validador.check.success = function (obj) {    
    var parent = $(obj).parent();
    parent.removeClass('has-error has-feedback');
    parent.find('.input-error').html('');
};

$(function() {

    // add bootstrap classes to forms
    $('.form-group input, .form-group select').not('.form-control').addClass('form-control');
    $('.form-group .input-error').not(':empty').parent('.form-group').addClass('has-error has-feedback');
    $('form .has-error .form-control').on('focusin', function() {
        $(this).parent('.form-group').removeClass('has-error has-feedback');
    });

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
                        $('#applications ul').removeClass('grid').addClass('list');
                        break;
                    case 'grid':
                        $('#applications ul').removeClass('list').addClass('grid');
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
