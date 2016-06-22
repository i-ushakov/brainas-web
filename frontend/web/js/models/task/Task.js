/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var app = app || {};


var Task = Backbone.Model.extend({
    urlSave: app.url + 'task/save/',
    urlRemove: app.url + 'task/remove/',

    id : null,
    message : null,
    description : null,
    conditions: new Conditions(),

    defaults : {
        preventUpdateFromServer : false
    },

    initialize : function(task) {
        this.id = task.id;
        var conditions = new Conditions();
        _.each(task.conditions, function(condition) {
            var condition = new Condition(condition);
            if (condition.isValid()) {
                conditions.push(condition);
            }
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
            task: JSON.stringify(this)
        };

        $.ajax({
            type: "POST",
            url: this.urlSave,
            data: data,
            success: function(result){
                if (result.status == "OK") {
                    self.trigger("save", result);
                    return true;
                } else if (result.status == "FAILED" && result.type == 'must_be_signed_in'){ // if was logged out on Yii2 side
                    alert("The operation has not been successful! Please repeat your actions after page reloading");
                    location.reload();
                }
            },
            dataType: 'json'
        });
    },

    remove: function() {
        var self = this;
        var data = {
            task: JSON.stringify(self)
        };

        $.ajax({
            type: "POST",
            url: this.urlRemove,
            data: data,
            success: function(result) {
                if (result.status == "OK") {
                    app.MainPanel.taskPanel.tasks.remove(self.id);
                    return true;
                } else if (result.status == "FAILED" && result.type == 'must_be_signed_in'){ // if was logged out on Yii2 side
                    alert("The operation has not been successful! Please repeat your actions after page reloading");
                    location.reload();
                }
            },
            dataType: 'json'
        });
    },

    update: function(task) {
        this.set("id", task.id);
        this.set("message", task.message);
        this.set("description", task.description);
        var conditions = new Conditions();
        _.each(task.conditions, function(condition) {
            var condition = new Condition(condition);
            conditions.push(condition);
        });
        this.set('conditions', conditions);
    }
});