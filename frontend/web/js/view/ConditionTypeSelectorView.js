var app = app || {};

var ConditionTypeSelectorView = Backbone.View.extend({
    template: _.template($('#condition-type-selector-template').html()),

    events: {
        'click .add-gps-event': 'addLocationEvent',
        'click .add-time-event': 'addTimeEvent',
        'click .cancel-condition-icon': 'cancelAddingCondition'
    },

    initialize: function (conditions) {
        _.bindAll(this, 'addLocationEvent');
        this.conditions = conditions;
        this.render();
    },

    render: function() {
        var params = {timeBtn: false};
        if (!this.haveATimeCondition()) {
            params.timeBtn = true;
        }
        this.$el.html(this.template(params));
    },

    addLocationEvent: function(e) {
        condition = this.createEmptyCondition();

        eventJSON = {};
        eventJSON.id = null;
        eventJSON.type = "GPS";

        if (navigator.geolocation) {
            var location = app.getCurrentUserLocation();
            if (location != null) {
                eventJSON.params = {lat: location.latitude, lng: location.longitude, radius: 100};
            } else {
                eventJSON.params = {lat: 0, lng: 0, radius: 100};
            }
        }
        else {
            console.log('Geolocation is not supported for this Browser/OS version yet.');
        }



        var events = {
            GPS: new Event(eventJSON) || null
        }

        condition.set('events', events);
        this.conditions.trigger("eventWasAdded", this);
        this.remove();
    },

    addTimeEvent: function(e) {
        var condition = this.createEmptyCondition();

        eventJSON = {};
        eventJSON.id = null;
        eventJSON.type = "TIME";

        date = new Date();
        eventJSON.params = {datetime:  moment(date).format("DD-MM-YYYY HH:mm:ss"), offset: date.getTimezoneOffset()}
        var events = {
            TIME: new Event(eventJSON) || null
        }

        condition.set('events', events);
        this.conditions.trigger("eventWasAdded", this);
        this.remove();
    },

    createEmptyCondition: function() {
        var condition = new Condition(null);
        condition.set('id',null);
        this.conditions.push(condition);
        return condition;
    },

    cancelAddingCondition: function() {
        this.conditions.trigger("conditionWasCancled", this.conditions);
        this.remove();
    },

    haveATimeCondition: function () {
        var result = false;
        this.conditions.each(function(condition) {
            if (condition.get("events").TIME != undefined) {
                result = true;
            }
        });
        return result;
    }
});
