/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var app = app || {};


var Task = Backbone.Model.extend({
    id : null,
    message : null,
    description : null,
    conditions: [],

    initialize : function(task) {
        this.id = task.id;
        this.message = task.message;
        this.description = task.description;
        var condition = {GPS: task.conditions[0].GPS};
        var condition = new Condition({GPS: task.conditions[0].GPS});
        this.conditions.push(condition);
    }
});