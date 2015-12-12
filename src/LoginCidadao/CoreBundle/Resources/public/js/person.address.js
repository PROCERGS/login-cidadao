$(document).ready(function() {
    $('.btn.address-remove').on('click', function() {
        var id = $(this).data('address-id');
        $('.remove-box[data-address-id=' + id + ']').slideDown();
    });
    $('.remove-box .cancel').on('click', function() {
        $(this).closest('.remove-box').slideUp();
    });

    if (typeof addressEdit != 'undefined') {
        $(document.createElement('option')).val('').attr('data-custom-option', 1).text(addressEdit.addStateMessage).hide().insertAfter($(addressEdit.stateId).find('option[value=""]').first());
        $(document.createElement('option')).val('').attr('data-custom-option', 1).text(addressEdit.addCityMessage).hide().insertAfter($(addressEdit.cityId).find('option[value=""]').first());

        $(addressEdit.countryId).on('change', function(event) {
            $(addressEdit.stateId).find('option[value!=""]').remove();
            $(addressEdit.stateId).trigger('change');
            $(addressEdit.stateId).hide();
            $(addressEdit.stateSteppeId).val(addressEdit.loadMessage).show().attr('disabled', 'disabled');
            if ($(this).val()) {
                $.ajax({
                    type : 'GET',
                    url : addressEdit.urlSearchCountry + '/' + $(this).val(),
                    dataType : 'json',
                    success : function(data, textStatus, jqXHR) {
                        if (data.length) {
                            $(addressEdit.stateId).show();
                            $(addressEdit.stateSteppeId).hide();
                            $(addressEdit.stateSteppeId).val('').removeAttr('disabled');
                            $(addressEdit.stateId).append(data.map(function(val) {
                                return $(document.createElement('option')).val(val.id).text(val.name)
                            }));
                            $(addressEdit.stateId).find('option:selected').removeAttr('selected');
                            $(addressEdit.stateId).find('option[value=""]').first().attr('selected', 'selected');
                            if ($(addressEdit.countryId).val() == $(addressEdit.preferredcountriesId).val()) {
                                $(addressEdit.stateId).find('option[data-custom-option]').hide();
                            } else {
                                $(addressEdit.stateId).find('option[data-custom-option]').show();
                            }
                        } else {
                            $(addressEdit.stateSteppeId).val('').removeAttr('disabled');
                        }
                    }
                });
            } else {
                $(addressEdit.stateSteppeId).val('').removeAttr('disabled');
            }
        });
        $(addressEdit.stateId).on('change', function(event) {
            $(addressEdit.cityId).find('option[value!=""]').remove();
            $(addressEdit.cityId).hide();
            $(addressEdit.citySteppeId).val(addressEdit.loadMessage).show().attr('disabled', 'disabled');
            if ($(this).val()) {
                $.ajax({
                    type : 'GET',
                    url : addressEdit.urlSearchState + '/' + $(this).val(),
                    dataType : 'json',
                    success : function(data, textStatus, jqXHR) {
                        if (data.length) {
                            $(addressEdit.cityId).show();
                            $(addressEdit.citySteppeId).hide();
                            $(addressEdit.citySteppeId).val('').removeAttr('disabled');
                            $(addressEdit.cityId).append(data.map(function(val) {
                                return $(document.createElement('option')).val(val.id).text(val.name)
                            }));
                            $(addressEdit.cityId).find('option:selected').removeAttr('selected');
                            $(addressEdit.cityId).find('option[value=""]').first().attr('selected', 'selected');
                            if ($(addressEdit.countryId).val() == $(addressEdit.preferredcountriesId).val()) {
                                $(addressEdit.cityId).find('option[data-custom-option]').hide();
                            } else {
                                $(addressEdit.cityId).find('option[data-custom-option]').show();
                            }
                        } else {
                            $(addressEdit.citySteppeId).val('').removeAttr('disabled');
                        }
                    }
                });
            } else {
                $(addressEdit.citySteppeId).val('').removeAttr('disabled');
                if ($(event.target).find('[data-custom-option="1"]:selected').length) {
                    $(addressEdit.stateId).hide();
                    $(addressEdit.stateSteppeId).show();
                    $(addressEdit.stateSteppeId).focus();
                }
            }
        });
        $(addressEdit.cityId).on('change', function(event) {
            if ($(event.target).find('[data-custom-option="1"]:selected').length) {
                $(addressEdit.cityId).hide();
                $(addressEdit.citySteppeId).show();
                $(addressEdit.citySteppeId).focus();
            }
        });
        if ($(addressEdit.countryId).val() == '') {
            $(addressEdit.countryId).trigger('change');
        }

    }
    if (typeof selectors != 'undefined') {
        selectors.prepareQuery = function(selectorName) {
            return function(url, query) {
                var country_id = $('.countries option:selected').val();
                var state_id = $('.states option:selected').val();
                if (country_id > 0 || state_id > 0) {
                    filter = '?country_id=' + country_id + '&state_id=' + state_id;
                } else {
                    filter = '';
                }
                url = selectors[selectorName].url;
                console.log(url.replace('%QUERY', query) + filter);
                return url.replace('%QUERY', query) + filter;
            }
        };

        selectors.clearOnChange = function(data, name) {
            var next = $('[data-selector=' + name + ']').data('selector-next');
            if (undefined !== next) {
                var nextField = $('[data-selector=' + next + ']');
                nextField.typeahead('val', null);
                if (data !== null) {
                    nextField.data('selector-filter', data.id);
                }
                selectors.clearOnChange(null, next);
            }
        };

        $.each($('[data-selector]'), function() {
            var selectorName = $(this).data('selector');
            selectors[selectorName].bloodhound = new Bloodhound({
                datumTokenizer : Bloodhound.tokenizers.obj.whitespace('value'),
                queryTokenizer : Bloodhound.tokenizers.whitespace,
                remote : {
                    'url' : selectors[selectorName].url,
                    'replace' : selectors.prepareQuery(selectorName)
                },
            });
            selectors[selectorName].bloodhound.initialize();
            $(this).typeahead(null, {
                name : selectorName,
                displayKey : selectors[selectorName].displayKey,
                source : selectors[selectorName].bloodhound.ttAdapter(),
                templates : {
                    empty : [ '<div class="empty-message">', $(this).data('empty-message'), '</div>' ].join('\n'),
                    suggestion : Handlebars.compile(selectors[selectorName].template)
                }
            }).on('typeahead:selected', function(obj, data, name) {
                selectors.clearOnChange(data, name);
                console.log($('[data-selector=' + name + ']').typeahead('val'));
                console.log(data);
            });
        });
    }
});
