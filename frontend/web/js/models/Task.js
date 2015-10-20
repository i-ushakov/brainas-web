/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var app = app || {};


var Task = Backbone.Model.extend({
    id : null,
    message : null,

    initialize : function(task) {
        this.id = task.id;
        this.message = task.message;
    }
});