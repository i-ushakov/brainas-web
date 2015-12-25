var app = app || {};

var ConditionTypeSelectorView = Backbone.View.extend({
    template: _.template($('#condition-type-selector-template').html()),

    events: {
        'click .add-gps-event': 'addGPSEvent',
        'click .gps-event-ready': 'GPSEventIsDone'
    },

    initialize: function (condition) {
        _.bindAll(this, 'addGPSEvent');
        this.model = condition;
        this.render();
    },

    render: function() {
        this.$el.html(this.template());
    },

    addGPSEvent: function(e) {
        this.$el.find(".add-gps-event-cont").show();
        this.$el.find("button.add-gps-event").hide();
    },

    GPSEventIsDone: function() {
        this.model.set('id',null);
        this.model.set('conditionId', null);
        event = {};
        event.id = null;
        event.type = "GPS";
        var locationSource = $('[name=location]').val();
        if (locationSource == "current") {
            var location = app.getCurrentUserLocation();
            var params = {lat: location.lat, lng:location.lng, radius: 100};
            event.params = params;
        } else {
            event.set("params", "{1,2,3}");
        }
        var events = {
            GPS: new Event(event) || null
        }
        this.model.set('events', events);
        this.model.trigger("eventWasAdded", this);
        this.remove();
    }
});
