var app = app || {};

var ConditionTypeSelectorView = Backbone.View.extend({
    template: _.template($('#condition-type-selector-template').html()),

    events: {
        'click .add-gps-event': 'addLocationCondition',
        'click .add-time-event': 'addTimeCondition',
        'click .cancel-condition-icon': 'cancelAddingCondition'
    },

    initialize: function (conditions) {
        _.bindAll(this, 'addLocationCondition', 'addTimeCondition');
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

    addLocationCondition: function(e) {
        condition = this.createEmptyCondition();
        condition.set('eventType', 'LOCATION');

        if (navigator.geolocation) {
            var location = app.getCurrentUserLocation();
            if (location != null) {
                var params = {lat: location.latitude, lng: location.longitude, radius: 100};
            } else {
                var params = {lat: 0, lng: 0, radius: 100};
            }
        }
        else {
            console.log('Geolocation is not supported for this Browser/OS version yet.');
        }

        condition.set('params', params);
        this.conditions.trigger("eventWasAdded", this);
        this.remove();
    },

    addTimeCondition: function(e) {
        var condition = this.createEmptyCondition();
        condition.set('eventType', 'TIME');

        date = new Date();
        var params = {datetime:  moment(date).format("DD-MM-YYYY HH:mm:ss"), offset: date.getTimezoneOffset()}

        condition.set('params', params);
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
            if (condition.get("eventType") == 'TIME') {
                result = true;
            }
        });
        return result;
    }
});
