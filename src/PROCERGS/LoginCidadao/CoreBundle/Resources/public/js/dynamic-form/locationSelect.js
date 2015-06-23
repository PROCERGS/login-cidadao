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
    },
    prepareField: function (filter, parent, data) {
        var element = $(filter, parent);
        var oldId = element.attr('id');
        var oldName = element.attr('name');
        element.closest('div')
                .data('field-id', oldId)
                .data('field-name', oldName);

        element.replaceWith($(data).find(filter)).attr('id', oldId)
                .attr('name', oldName);
        return;
    }
};
$(document).ready(function () {
    $('form').on('change', '.location-select:not(.location-text)', function () {
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
                    locationSelection.prepareField('.city-select', parent, data);
                case 'state':
                    locationSelection.prepareField('.state-select', parent, data);
                case 'country':
                    locationSelection.prepareField('.country-select', parent, data);
                    break;
            }
        }, 'html').always(function () {
            $('.location-select').removeAttr('disabled');
        });
    });
});
