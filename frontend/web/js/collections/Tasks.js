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
        var self = this;
        _.each(response, function(task) {
            self.remove(task.id)
            self.add(new Task(task));
        });

        return this.models;
    }
});