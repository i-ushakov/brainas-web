/**
 * Created by Kit Ushakov on 10/23/2015.
 */


var app = app || {};

var Condition = Backbone.Model.extend({
    type : null,

    default : {
        id : null,
        events: {},
    },

    initialize : function(condition) {
        if (condition != null) {
            this.set('id', condition.conditionId);
            this.set('conditionId', condition.conditionId);
            var events = {};
            if (condition.GPS) {
                events.GPS = new Event(condition.GPS);
            } else if (condition.TIME) {
                events.TIME = new Event(condition.TIME);
            }

            this.set('events', events);
        }
    },

    getType: function() {
        if (this.get("events")['GPS'] != undefined) {
            return "GPS";
        } else if (this.get("events")['TIME'] != undefined) {
            return "TIME";
        }
    }
});