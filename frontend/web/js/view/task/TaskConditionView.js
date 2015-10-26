/**
 * Created by Kit Ushakov on 10/20/2015.
 */


var app = app || {};

var TaskConditionView = Backbone.View.extend({

    map: null,

    template: _.template($('#task-condition-template').html()),

    events: {
        'click .task-condition': 'collapseConditionArea'
    },

    tEvents : {},

    initialize: function () {
        this.tEvents['GPS'] = this.model.get('GPS') || null;
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
        var mapCanvas = this.$el.find('.google-map')[0];
        var myLatLng = new google.maps.LatLng(this.tEvents['GPS'].params.lat, this.tEvents['GPS'].params.lng);

        var mapOptions = {
            center: myLatLng,
            zoom: 15,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }

        var map = new google.maps.Map(mapCanvas, mapOptions);
        var marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            title: 'Task location!'
        });
    },

    collapseConditionArea: function() {
        this.$el.find('.condition-collapse').toggle();
        this.map = this.map || this.initializeMap();
    }
});