/**
 * Created by Kit Ushakov on 10/23/2015.
 */


var app = app || {};


var Condition = Backbone.Model.extend({
    id : null,

    events: {},


    initialize : function(condition) {
        this.id = condition.id;
        this.events = {
            GPS: new Event(condition.GPS) || null
        }
    }
});