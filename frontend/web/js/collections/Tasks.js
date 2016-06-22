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

        _.bindAll(this, 'parse');
    },

    close: function() {
        clearInterval(this.timer);
    },

    parse: function(response, options) {
        var collection = this;
        if (response.status == "FAILED") {
            return this.models;
        }
        var tasks = [];
        _.each(response, function(task) {
            collection.get(task.id);
            var taskModel;
            var currentTaskModel = collection.get(task.id);
            if (currentTaskModel && currentTaskModel.get("preventUpdateFromServer")) {
                taskModel = currentTaskModel;
            } else {
                taskModel = new Task(task);
            }

            tasks.push(taskModel);
        });
        this.set(tasks);
        return this.models;
    }
});