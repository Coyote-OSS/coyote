$(function() {
    'use strict';

    $.fn.geoip = function() {
        return this.each(function() {
            $(this).hover(function() {
                var $this = $(this);

                if (!$(this).data('original-title')) {
                    $.getJSON('//geo-ip.pl/1.0/ip/' + $(this).text() + '?callback=?', {}, function(json) {
                        $this.attr('data-original-title', json.country + ', ' + json.city).tooltip('show');
                    });
                }
            });
        });
    };
});
