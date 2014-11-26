$(document).ready(function () {
    $('.btn.address-remove').on('click', function () {
        var id = $(this).data('address-id');
        $('.remove-box[data-address-id=' + id + ']').slideDown();
    });
    $('.remove-box .cancel').on('click', function () {
        $(this).closest('.remove-box').slideUp();
    });
});

selectors.prepareQuery = function (selectorName) {
    return function (url, query) {
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

selectors.clearOnChange = function (data, name) {
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

$.each($('[data-selector]'), function () {
    var selectorName = $(this).data('selector');
    selectors[selectorName].bloodhound = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            'url': selectors[selectorName].url,
            'replace': selectors.prepareQuery(selectorName)
        },
    });
    selectors[selectorName].bloodhound.initialize();
    $(this).typeahead(null, {
        name: selectorName,
        displayKey: selectors[selectorName].displayKey,
        source: selectors[selectorName].bloodhound.ttAdapter(),
        templates: {
            empty: [
                '<div class="empty-message">',
                $(this).data('empty-message'),
                '</div>'
            ].join('\n'),
            suggestion: Handlebars.compile(selectors[selectorName].template)
        }
    }).on('typeahead:selected', function (obj, data, name) {
        selectors.clearOnChange(data, name);
        console.log($('[data-selector=' + name + ']').typeahead('val'));
        console.log(data);
    });
});
