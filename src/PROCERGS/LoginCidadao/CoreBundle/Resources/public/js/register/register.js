$(document).ready(function(){
    $('#fos_user_registration_form_email').blur(function(){
        $elm = $(this);
        
        $.ajax({
            type: "GET",
            dataType: "json",
            url: emailAvailableUrl,
            data: { 
                email: $(this).val()
            }
        })
        .done(function(result) {
            if(result.valid){
                $elm.parent().removeClass('has-error').find('.input-error').html("");
            }else{
                $elm.parent().addClass('has-error').find('.input-error').html('<ul><li>'+result.message+'</li></ul>');
            }
            return false;
        });
    });

});