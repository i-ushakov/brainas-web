/**
 * Created by Kit Ushakov on 10/20/2015.
 */


var app = app || {};

var TaskLocationConditionView = Backbone.View.extend({

    parent: null,

    map: null,

    marker: null,

    template: _.template($('#task-condition-template').html()),

    events: {
        'click .condition-row': 'toggleConditionArea',
        'click .delete-condition-icon': 'deleteConditionHandler'
    },

    initialize: function (options) {
        this.parent = options.parent;
        this.params = this.model.get("events").GPS.get("params");
        this.geocoder = new google.maps.Geocoder();
        // For handling a situation when  browser's default location was set.
        if (this.params.address === undefined || this.params.address === "") {
            this.changeLocationParams(new google.maps.LatLng(this.params.lat, this.params.lng));
        }
        this.render();
        _.bindAll(this, 'changeLocationParams');
    },

    render: function() {
        var params = {
            GPS: this.model.get("events").GPS
        };
        this.$el.html(this.template(params));
        return this.$el;
    },

    initializeMap: function() {
        var self = this;
        var mapCanvas = this.$el.find('.google-map')[0];

        var lat = this.params.lat;
        var lng = this.params.lng;
        var myLatLng = new google.maps.LatLng(lat, lng);
        if (lat == 0 && lng == 0) {
            var zoom = 1;
        } else {
            var zoom = 15;
        }

        var mapOptions = {
            center: myLatLng,
            zoom: zoom,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var map = new google.maps.Map(mapCanvas, mapOptions);
        this.fitBounds(map);

        this.marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            title: 'Task location!'
        });

        map.addListener('click', function(e) {
            self.placeMarkerAndPanTo(e.latLng, map);
        });

        this.setAutoComplete(map);
    },

    fitBounds: function(map) {
        var placeId = this.model.get("events").GPS.get("params").placeId;
        if (placeId) {
            var request = {
                placeId: placeId
            };

            service = new google.maps.places.PlacesService(map);
            service.getDetails(request, callback);

            function callback(place, status) {
                if (status == google.maps.places.PlacesServiceStatus.OK) {
                    if (place.geometry.viewport) {
                        map.fitBounds(place.geometry.viewport);
                    }
                }
            }
        }
    },

    setAutoComplete: function(map) {
        var self = this;
        var input = (this.$el.find('#pac-input').get(0));

        self.autocomplete = new google.maps.places.Autocomplete(input);
        self.autocomplete.bindTo('bounds', map);

        self.infowindow = new google.maps.InfoWindow();
        self.autocomplete.addListener('place_changed', function() {
            self.infowindow.close();
            self.marker.setVisible(false);
            var place = self.autocomplete.getPlace();
            if (!place.geometry) {
                window.alert("Autocomplete's returned place contains no geometry");
                return;
            }

            // If the place has a geometry, then present it on a map.
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);  // Why 17? Because it looks good.
            }

            self.marker.setPosition(place.geometry.location);
            self.marker.setVisible(true);

            self.changeLocationParams(place.geometry.location, map);

            var address = '';
            if (place.address_components) {
                address = [
                    (place.address_components[0] && place.address_components[0].short_name || ''),
                    (place.address_components[1] && place.address_components[1].short_name || ''),
                    (place.address_components[2] && place.address_components[2].short_name || '')
                ].join(' ');
            }

            self.infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
            self.infowindow.open(map, self.marker);
        });
    },

    toggleConditionArea: function() {
        this.$el.find('.condition-collapse').toggle();
        this.map = this.map || this.initializeMap();
    },

    collapseConditionArea: function() {
        if (this.$el.find('.condition-collapse').is(":visible")) {
            this.$el.find('.condition-collapse').toggle();
        }
    },

    placeMarkerAndPanTo: function (latLng, map) {
        this.marker.setMap(null);
        this.marker = new google.maps.Marker({
            position: latLng,
            map: map
        });
        this.changeLocationParams(latLng, map);
        map.panTo(latLng);
    },

    changeLocationParams: function(latLng) {
        var self = this;
        var gpsParams = this.model.get("events").GPS.get("params");
        if (gpsParams) {
            gpsParams.lat = latLng.lat();
            gpsParams.lng = latLng.lng();
        }

        this.geocoder.geocode({
            'latLng': latLng
        }, function (results, status) {
            if (status === google.maps.GeocoderStatus.OK) {
                var result = results[0];
                if (result) {
                    var addressArray = results[0].formatted_address.split(",");
                    gpsParams.address = addressArray[0] + ", " + addressArray[1];
                    if (addressArray[2] != undefined) {
                        gpsParams.address = gpsParams.address + ", " + addressArray[2];
                    }
                    gpsParams.placeId = result.place_id;
                } else {
                    alert('No results found');
                }
            } else {
                alert('Geocoder failed due to: ' + status);
            }
        });

        this.parent.changeGPSHandler();
    },

    deleteConditionHandler: function() {
        this.model.trigger("conditionWasRemoved", this.model);
        this.remove();
        return false;
    }
});