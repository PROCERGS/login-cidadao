var locationSelection = {
    request: null,
    getData: function (element) {
        var data = {
            level: locationSelection.getLevel()
        };
        element = $(element);
        if (element.val() > 0) {
            if (element.is('.city-select')) {
                data.city = element.val();
            } else if (element.is('.state-select')) {
                data.state = element.val();
            } else if (element.is('.country-select')) {
                data.country = element.val();
            }
        }
        return data;
    },
    getLevel: function () {
        if ($('.city-select').is('.location-select')) {
            return 'city';
        }
        if ($('.state-select').is('.location-select')) {
            return 'state';
        }
        if ($('.country-select').is('.location-select')) {
            return 'country';
        }
    }
};
$(document).ready(function () {
    $('form').on('change', '.location-select', function () {
        if (locationSelection.request) {
            locationSelection.request.abort();
        }
        $('.location-select').attr('disabled', 'disabled');
        var url = locationSelection.formUrl;
        var data = locationSelection.getData(this);
        var parent = $(this).parent('.form-group').parent('div');
        locationSelection.request = $.get(url, data, function (data) {
            switch (locationSelection.getLevel()) {
                case 'city':
                    $('.city-select', parent).empty().append($(data).find('.city-select option'));
                case 'state':
                    $('.state-select', parent).empty().append($(data).find('.state-select option'));
                case 'country':
                    $('.country-select', parent).empty().append($(data).find('.country-select option'));
                    break;
            }
        }, 'html').always(function () {
            $('.location-select').removeAttr('disabled');
        });
    });
});
