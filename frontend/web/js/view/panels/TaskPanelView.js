/**
 * Created by Kit Ushakov on 10/19/2015.
 */

var app = app || {};

var TaskPanelView = Backbone.View.extend({

    template: _.template($('#task-panel-template').html()),

    initialize: function() {
        var self = this;

        self.model.tasks.on("add", function(model) {
            self.addTask(model);
        });

        self.model.tasks.on("remove", function(model) {
            debugger;
            self.removeTask(model);
        });
    },

    render: function() {
        this.$el.html(this.template());
        this.addNewTaskButton();
    },

    addTask: function(task) {
        var taskEl = new TaskTileView({model : task}).render();
        this.$el.find(".add-new-task-btn").after(taskEl);
    },

    removeTask: function(task) {
        this.$el.find("#task-tile-view-" + task.id).remove();
    },

    addNewTaskButton: function() {
        var taskEl = new TaskTileView({addTaskButton: true}).render();
        this.$el.append(taskEl);
    }
});