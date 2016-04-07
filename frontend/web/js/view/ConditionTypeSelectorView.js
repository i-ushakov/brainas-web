var app = app || {};

var ConditionTypeSelectorView = Backbone.View.extend({
    template: _.template($('#condition-type-selector-template').html()),

    events: {
        'click .add-gps-event': 'addLocationEvent',
        'click .cancel-condition-icon': 'cancelAddingCondition'
    },

    initialize: function (condition) {
        _.bindAll(this, 'addLocationEvent');
        this.model = condition;
        this.render();
    },

    render: function() {
        this.$el.html(this.template());
    },

    addLocationEvent: function(e) {
        this.model.set('id',null);
        eventJSON = {};
        eventJSON.id = null;
        eventJSON.type = "GPS";

        if (navigator.geolocation) {
            console.log('Geolocation is supported!');
        }
        else {
            console.log('Geolocation is not supported for this Browser/OS version yet.');
        }

        var location = app.getCurrentUserLocation();
        if (location != null) {
            eventJSON.params = {lat: location.latitude, lng: location.longitude, radius: 100};
        } else {
            eventJSON.params = {lat: 0, lng: 0, radius: 100};
        }

        var events = {
            GPS: new Event(eventJSON) || null
        }

        this.model.set('events', events);
        this.model.trigger("eventWasAdded", this);
        this.remove();
    },

    cancelAddingCondition: function() {
        this.model.trigger("conditionWasCancled", this.model);
        this.remove();
    }
});
