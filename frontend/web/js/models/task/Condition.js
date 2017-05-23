/**
 * Created by Kit Ushakov on 10/23/2015.
 */


var app = app || {};

var Condition = Backbone.Model.extend({
    type : null,

    defaults : {
        id : null,
        events: {}
    },

    initialize : function(condition) {
        if (condition != null) {
            this.set('id', condition.conditionId);
            this.set('conditionId', condition.conditionId);
            var events = {};
            if (condition.LOCATION) {
                events.LOCATION = new Event(condition.LOCATION);
            } else if (condition.TIME) {
                events.TIME = new Event(condition.TIME);
            }

            this.set('events', events);
        }
    },

    getType: function() {
        if (this.get("events")['LOCATION'] != undefined) {
            return "LOCATION";
        } else if (this.get("events")['TIME'] != undefined) {
            return "TIME";
        }
    },

    validate: function(attributes) {
        this.validationErrors = [];
        var event = attributes.events[Object.keys(attributes.events)[0]];
        if (typeof event === 'undefined') {
            this.validationErrors.push("No events in condition");
        }
        else if (!event.isValid()) {
            this.validationErrors.push(event.validationErrors);
        }
        if(!_.isEmpty(this.validationErrors)) {
            return this.validationErrors;
        }
    }
});