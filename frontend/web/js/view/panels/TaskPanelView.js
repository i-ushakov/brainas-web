/**
 * Created by Kit Ushakov on 10/19/2015.
 */

var app = app || {};

var TaskPanelView = Backbone.View.extend({
    taskTileViews: [],

    events: {
        'click #sing-in-btn': 'signInBtnHandler',
    },

    template: _.template($('#task-panel-template').html()),

    initialize: function() {
        _.bindAll(this, 'onTaskSaveHandler');

        var self = this;

        self.model.tasks.on("add", function(model) {
            model.on({"save": self.onTaskSaveHandler});
            self.addTask(model);
        });

        self.model.tasks.on("remove", function(model) {
            self.removeTask(model);
        });
    },

    render: function() {
        this.$el.html(this.template());
        if (app.singedIn == true) {
            this.addNewTaskButton();
        } else {
            this.$el.find('#user-not-logged-block').show();
        }
    },

    addTask: function(task) {
        var taskTileView = new TaskTileView({model : task})
        this.$el.find(".add-new-task-btn").after(taskTileView.render());
        this.taskTileViews[task.id] = taskTileView;
    },

    removeTask: function(task) {
        this.$el.find("#task-tile-view-" + task.id).remove();
        this.taskTileViews[task.id] = null;
    },

    addNewTaskButton: function() {
        var taskEl = new TaskTileView({addTaskButton: true}).render();
        this.$el.append(taskEl);
    },

    onTaskSaveHandler: function (result) {
        var task = result.task;
        var taskTileView = this.taskTileViews[task.id];
        if (taskTileView != undefined && taskTileView != null) {
            taskTileView.refresh();
        }
    },

    signInBtnHandler: function(event) {
        $(event.toElement).prop('disabled', true)
        $(".gitkit-id-submit").trigger('click');
    }

});