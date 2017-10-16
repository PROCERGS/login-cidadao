$(document).ready(function () {
    dynamicForm.idCard.unlockState();
});

$('.id-card-forms-placeholder').on('change', '#form_idcard_state', function () {
    var selectedState = $(this).val();
    var brokeForm = selectedState !== dynamicForm.idCard.loadedFormStateId;
    var fields = $('input', '.id-card-panel').not('#form_idcard_state');

    if (brokeForm) {
        fields.attr('disabled', 'disabled');
        var url = dynamicForm.idCard.formUrl + '&id_card_state_id=' + selectedState + ' .id-card-forms-placeholder';
        console.log(url);

        $('.id-card-forms-placeholder').load(url, function () {
            dynamicForm.idCard.unlockState();
            dynamicForm.idCard.loadedFormStateId = selectedState;
        });

        return;
    } else {
        fields.removeAttr('disabled');
    }
});
