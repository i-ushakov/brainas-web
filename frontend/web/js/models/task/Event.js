/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var app = app || {};


var Event = Backbone.Model.extend({
    type: null,

    params: {},

    initialize : function(event) {
        this.type = event.type;
        this.params = event.params
    }
});