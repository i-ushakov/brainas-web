/**
 * Created by Kit Ushakov on 10/23/2015.
 */


var app = app || {};


var Condition = Backbone.Model.extend({
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
            } else {
                events.GPS = null;
            }

            this.set('events', events);
        }
    }
});