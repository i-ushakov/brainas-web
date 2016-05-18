/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var Tasks = Backbone.Collection.extend({
    model: Task,

    url: '/task/get',

    initialize: function() {
        var self = this;
        this.timer = setInterval(function() {
            self.fetch();
        }, 15000);
    },

    close: function() {
        clearInterval(this.timer);
    },

    parse: function(response, options) {
        if (response.status == "FAILED") {
            return this.models;
        }
        var tasks = [];
        _.each(response, function(task) {
            var taskModel = new Task(task)
            tasks.push(taskModel);
        });
        this.set(tasks);
        return this.models;
    }
});