/**
 * Created by Kit Ushakov on 10/19/2015.
 */

var app = app || {};

var TaskPanelView = Backbone.View.extend({

    el : '#task-panel',

    template: _.template($('#task-panel-template').html()),

    initialize: function() {
        var self = this;

        self.model.tasks.on("add", function(model) {
            self.addTask(model);
        });

        self.model.tasks.on("remove", function(model) {
            //$('tr[id=' + model.id + ']').remove();
        });

        self.render();

        self.model.loadTasks();
    },

    render: function() {
        debugger;
        var params = {};
        this.$el.html(this.template(params));
        return this;
    },

    addTask: function(task) {
        var self = this;
        var taskEl = new TaskTileView({model : task}).render();
        self.$el.append(taskEl);
    }
});