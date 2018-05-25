var googleMapStyle_dark = [{
		"featureType": "water",
		"elementType": "geometry",
		"stylers": [{"color": "#050506"}, {"lightness": 17}]
	}, {
		"featureType": "landscape",
		"elementType": "geometry",
		"stylers": [{"color": "#050506"}, {"lightness": 20}]
	}, {
		"featureType": "road.highway",
		"elementType": "geometry.fill",
		"stylers": [{"color": "#050506"}, {"lightness": 17}]
	}, {
		"featureType": "road.highway",
		"elementType": "geometry.stroke",
		"stylers": [{"color": "#050506"}, {"lightness": 29}, {"weight": 0.2}]
	}, {
		"featureType": "road.arterial",
		"elementType": "geometry",
		"stylers": [{"color": "#050506"}, {"lightness": 18}]
	}, {
		"featureType": "road.local",
		"elementType": "geometry",
		"stylers": [{"color": "#050506"}, {"lightness": 16}]
	}, {
		"featureType": "poi",
		"elementType": "geometry",
		"stylers": [{"color": "#050506"}, {"lightness": 21}]
	}, {
		"elementType": "labels.text.stroke",
		"stylers": [{"visibility": "on"}, {"color": "#050506"}, {"lightness": 16}]
	}, {
		"elementType": "labels.text.fill",
		"stylers": [{"saturation": 36}, {"color": "#050506"}, {"lightness": 40}]
	}, {"elementType": "labels.icon", "stylers": [{"visibility": "off"}]}, {
		"featureType": "transit",
		"elementType": "geometry",
		"stylers": [{"color": "#050506"}, {"lightness": 19}]
	}, {
		"featureType": "administrative",
		"elementType": "geometry.fill",
		"stylers": [{"color": "#050506"}, {"lightness": 20}]
	}, {
		"featureType": "administrative",
		"elementType": "geometry.stroke",
		"stylers": [{"color": "#050506"}, {"lightness": 17}, {"weight": 1.2}]
	}],

	googleMapStyle_light = [{
		"featureType": "administrative",
		"elementType": "all",
		"stylers": [{"visibility": "on"}, {"saturation": -100}, {"lightness": 20}]
	}, {
		"featureType": "road",
		"elementType": "all",
		"stylers": [{"visibility": "on"}, {"saturation": -100}, {"lightness": 40}]
	}, {
		"featureType": "water",
		"elementType": "all",
		"stylers": [{"visibility": "on"}, {"saturation": -10}, {"lightness": 30}]
	}, {
		"featureType": "landscape.man_made",
		"elementType": "all",
		"stylers": [{"visibility": "simplified"}, {"saturation": -60}, {"lightness": 10}]
	}, {
		"featureType": "landscape.natural",
		"elementType": "all",
		"stylers": [{"visibility": "simplified"}, {"saturation": -60}, {"lightness": 60}]
	}, {
		"featureType": "poi",
		"elementType": "all",
		"stylers": [{"visibility": "off"}, {"saturation": -100}, {"lightness": 60}]
	}, {
		"featureType": "transit",
		"elementType": "all",
		"stylers": [{"visibility": "off"}, {"saturation": -100}, {"lightness": 60}]
	}],

	googleMapStyle_base = [{"stylers": [{"saturation": -100}, {"gamma": 1}]}, {
		"elementType": "labels.text.stroke",
		"stylers": [{"visibility": "off"}]
	}, {
		"featureType": "poi.business",
		"elementType": "labels.text",
		"stylers": [{"visibility": "off"}]
	}, {
		"featureType": "poi.business",
		"elementType": "labels.icon",
		"stylers": [{"visibility": "off"}]
	}, {
		"featureType": "poi.place_of_worship",
		"elementType": "labels.text",
		"stylers": [{"visibility": "off"}]
	}, {
		"featureType": "poi.place_of_worship",
		"elementType": "labels.icon",
		"stylers": [{"visibility": "off"}]
	}, {
		"featureType": "road",
		"elementType": "geometry",
		"stylers": [{"visibility": "simplified"}]
	}, {
		"featureType": "water",
		"stylers": [{"visibility": "on"}, {"saturation": 50}, {"gamma": 0}, {"hue": "#50a5d1"}]
	}, {
		"featureType": "administrative.neighborhood",
		"elementType": "labels.text.fill",
		"stylers": [{"color": "#333333"}]
	}, {
		"featureType": "road.local",
		"elementType": "labels.text",
		"stylers": [{"weight": 0.5}, {"color": "#333333"}]
	}, {"featureType": "transit.station", "elementType": "labels.icon", "stylers": [{"gamma": 1}, {"saturation": 50}]}],

	googleMapStyle_showoff = [{"featureType":"administrative","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"visibility":"on"}]},{"featureType":"administrative","elementType":"labels","stylers":[{"visibility":"on"},{"color":"#716464"},{"weight":"0.01"}]},{"featureType":"administrative.country","elementType":"labels","stylers":[{"visibility":"on"}]},{"featureType":"landscape","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"landscape.natural","elementType":"geometry","stylers":[{"visibility":"simplified"}]},{"featureType":"landscape.natural.landcover","elementType":"geometry","stylers":[{"visibility":"simplified"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"poi","elementType":"geometry.fill","stylers":[{"visibility":"simplified"}]},{"featureType":"poi","elementType":"geometry.stroke","stylers":[{"visibility":"simplified"}]},{"featureType":"poi","elementType":"labels.text","stylers":[{"visibility":"simplified"}]},{"featureType":"poi","elementType":"labels.text.fill","stylers":[{"visibility":"simplified"}]},{"featureType":"poi","elementType":"labels.text.stroke","stylers":[{"visibility":"simplified"}]},{"featureType":"poi.attraction","elementType":"geometry","stylers":[{"visibility":"on"}]},{"featureType":"road","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"visibility":"on"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"visibility":"on"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"visibility":"simplified"},{"color":"#a05519"},{"saturation":"-13"}]},{"featureType":"road.local","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"transit","elementType":"geometry","stylers":[{"visibility":"simplified"}]},{"featureType":"transit.station","elementType":"geometry","stylers":[{"visibility":"on"}]},{"featureType":"water","elementType":"all","stylers":[{"visibility":"simplified"},{"color":"#84afa3"},{"lightness":52}]},{"featureType":"water","elementType":"geometry","stylers":[{"visibility":"on"}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"visibility":"on"}]}];