$(document).ready(function () {
    $('.btn.address-remove').on('click', function () {
        var id = $(this).data('address-id');
        $('.remove-box[data-address-id=' + id + ']').slideDown();
    });
    $('.remove-box .cancel').on('click', function () {
        $(this).closest('.remove-box').slideUp();
    });
});

if ($('.city-selector').length) {
    var cities = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: cityTypeaheadSearchUrl,
        prefetch: cityTypeaheadPrefetchUrl,
    });
    cities.initialize();
    $('.city-selector').typeahead(null, {
        name: 'city',
        displayKey: function (city) {
            return city.name + ', ' + city.state.acronym;
        },
        source: cities.ttAdapter(),
        templates: {
            empty: [
                '<div class="empty-message">',
                $('.city-selector').data('empty-message'),
                '</div>'
            ].join('\n'),
            suggestion: Handlebars.compile('<p><strong>{\{name}}</strong>, {\{state.acronym}}</p>')
        }
    });
}