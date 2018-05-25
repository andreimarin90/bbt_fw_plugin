jQuery(window).load(function() {
    if(jQuery('.bbt_admin_map').length !== 0) {
        jQuery('.map').each(function (index) {
            var $map = jQuery(this),
                markers = [],
                type = $map.data('mapType') || 'roadmap',
                zoom = $map.data('mapZoom') || 14,
                style = $map.data('mapStyle') || [],
                scrollwheel = $map.data('mapScrollWheel') || 0,
                markerImg = $map.data('mapMarker') || '',
                markerImgSize = $map.data('mapMarkerSize') || [],
                markerAnchor = $map.data('mapMarkerAnchor') || [];

            buildMarkers();

            function buildMarkers() {

                var data = $map.data(),
                    dataArray = [],
                    addresses = [],
                    titles = [],
                    htmls = [],
                    coords = [];

                for (var prop in data) {
                    if (data.hasOwnProperty(prop)) {
                        dataArray[prop] = data[prop];
                    }
                }

                for (var prop2 in dataArray) {
                    if (dataArray.hasOwnProperty(prop2)) {
                        if (~prop2.indexOf('mapAddress')) {
                            addresses = dataArray[prop2].split(';');
                        } else if (~prop2.indexOf('mapCoords')) {
                            var c = dataArray[prop2].split(';');

                            if (c.length > 1) {
                                coords.push({
                                    latitude: c[0],
                                    longitude: c[1]
                                });
                            }
                        } else if (~prop2.indexOf('mapTitle')) {
                            titles.push(dataArray[prop2]);
                        }

                        if (~prop2.indexOf('mapHtml')) {
                            htmls = dataArray[prop2].split(';');
                        }
                    }
                }

                for (var i = addresses.length - 1; i >= 0; i--) {
                    var marker = {
                        address: addresses[i],
                        title: titles[i] ? titles[i] : '',
                        html: htmls[i] ? htmls[i] : '',
                        draggable: true,
                        onDragEnd: function (event) {
                            if ($map.hasClass('js-get-coords')) {
                                $map.siblings('.bbt_map_input').val(event.latLng.lat() + ',' + event.latLng.lng());
                            }
                        }
                    };

                    if (markerImg) {
                        marker.icon = {
                            image: markerImg,
                            iconsize: markerImgSize,
                            iconanchor: markerAnchor
                        }
                    }

                    if (addresses[i].length) {
                        markers.push(marker);
                    }
                }

                for (var j = coords.length - 1; j >= 0; j--) {
                    var marker2 = {
                        latitude: coords[j] ? coords[j].latitude : '',
                        longitude: coords[j] ? coords[j].longitude : '',
                        title: titles[j] ? titles[j] : ''
                    };

                    if (markerImg) {
                        marker2.icon = {
                            image: markerImg,
                            iconsize: markerImgSize,
                            iconanchor: markerAnchor
                        }
                    }

                    markers.push(marker2);
                }
            }

            if (style && window['googleMapStyle_' + style] && window['googleMapStyle_' + style].length) {
                style = window['googleMapStyle_' + style];
            }

            setTimeout(function () {
                if (markers[0].address) {
                    $map.gMap({
                        maptype: type,
                        address: markers[0].address,
                        latitude: 'fit',
                        longitude: 'fit',
                        zoom: 'fit',
                        styles: style,
                        scrollwheel: scrollwheel,
                        markers: markers,
                        zoomControl: true,
                        mapTypeControl: false,
                        scaleControl: false,
                        streetViewControl: false,
                        rotateControl: false
                    });
                } else if (markers[0].latitude) {
                    $map.gMap({
                        maptype: type,
                        latitude: markers[0].latitude,
                        longitude: markers[0].longitude,
                        zoom: zoom,
                        styles: style,
                        scrollwheel: scrollwheel,
                        markers: markers
                    });
                }
            }, 1000 * index);
        });
    }
});