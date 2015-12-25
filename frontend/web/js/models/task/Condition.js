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
            this.unset('conditionId');
            this.unset('GPS');
            this.set('conditionId', condition.conditionId);
            var events = {
                GPS: new Event(condition.GPS) || null
            }
            this.set('events', events);
        }
    }
});