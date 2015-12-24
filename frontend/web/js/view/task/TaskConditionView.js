/**
 * Created by Kit Ushakov on 10/20/2015.
 */


var app = app || {};

var TaskConditionView = Backbone.View.extend({

    parent: null,

    map: null,

    marker: null,

    template: _.template($('#task-condition-template').html()),

    events: {
        'click .condition-row': 'collapseConditionArea'
    },

    tEvents : {},

    initialize: function (options) {
        this.parent = options.parent;
        this.tEvents['GPS'] = this.model.get("events").GPS || null;
        this.render();
    },

    render: function() {
        var params = {
            GPS: this.tEvents['GPS']
        };
        this.$el.html(this.template(params));
        return this.$el;
    },

    initializeMap: function() {
        var self = this;
        var mapCanvas = this.$el.find('.google-map')[0];
        var myLatLng = new google.maps.LatLng(this.tEvents['GPS'].get("params").lat, this.tEvents['GPS'].get("params").lng);

        var mapOptions = {
            center: myLatLng,
            zoom: 15,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }

        var map = new google.maps.Map(mapCanvas, mapOptions);
        this.marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            title: 'Task location!'
        });

        map.addListener('click', function(e) {
            self.placeMarkerAndPanTo(e.latLng, map);
        });
    },

    collapseConditionArea: function() {
        this.$el.find('.condition-collapse').toggle();
        this.map = this.map || this.initializeMap();
    },

    placeMarkerAndPanTo: function (latLng, map) {
        this.marker.setMap(null);
        this.marker = new google.maps.Marker({
            position: latLng,
            map: map
        });
        this.changeGPSParams(latLng);
        this.parent.changeGPSHandler();
        map.panTo(latLng);
    },

    changeGPSParams: function(latLng) {
        var gpsParams = this.model.get("events").GPS.get("params");
        if (gpsParams) {
            gpsParams.lat = latLng.lat();
            gpsParams.lng = latLng.lng();
        }
    }
});