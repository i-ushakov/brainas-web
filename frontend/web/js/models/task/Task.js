/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var app = app || {};


var Task = Backbone.Model.extend({
    urlRoot: app.url + 'task/save/',

    id : null,
    message : null,
    description : null,
    conditions: [],

    initialize : function(task) {
        this.id = task.id;
        var conditions = [];
        _.each(task.conditions, function(condition) {
            var condition = new Condition(condition);
            conditions.push(condition);
        });
        this.set('conditions', conditions);
    },

    validate: function (attr) {
        //if (!attr.BookName) {
            //return "Invalid BookName supplied."
        //}
    },

    save: function() {
        var self = this;
        data = {
            conditions: JSON.stringify(this.get("conditions")),
            task: JSON.stringify(this),
        };

        $.ajax({
            type: "POST",
            url: this.urlRoot,
            data: data,
            success: function(result){
                if (result.status = "OK") {
                    debugger;
                    self.trigger("save", result);
                    return true;
                }
            },
            dataType: 'json'
        });
    }
});