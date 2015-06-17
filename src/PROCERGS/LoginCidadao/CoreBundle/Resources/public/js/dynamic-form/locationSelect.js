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
        locationSelection.request = $.get(url, data, function (data) {
            locationSelection.last = data;
            switch (locationSelection.getLevel()) {
                case 'city':
                    $('.city-select').replaceWith($(data).find('.city-select').get(0));
                case 'state':
                    $('.state-select').replaceWith($(data).find('.state-select').get(0));
                case 'country':
                    $('.country-select').replaceWith($(data).find('.country-select').get(0));
                    break;
            }
        }, 'html').always(function () {
            $('.location-select').removeAttr('disabled');
        });
    });
});
