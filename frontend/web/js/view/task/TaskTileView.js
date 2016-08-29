/**
 * Created by Kit Ushakov on 10/20/2015.
 */


var app = app || {};

var TaskTileView = Backbone.View.extend({

    template: _.template( $('#task-tile-template').html()),

    templateAddTaskBtn: _.template( $('#task-tile-new-template').html()),

    addTaskButton: false,

    events: {
        'click .task-tile': 'openTaskCard',
        'click .add-new-task-btn': 'addNewTaskHandler',
        'click .delete-img-cont': 'removeTask',
    },

    initialize: function (options) {
        if (options.addTaskButton == true) {
            this.addTaskButton = true;
            return;
        }
    },

    render: function() {
        if (this.addTaskButton == true) {
            this.$el.html(this.templateAddTaskBtn());
            return this.$el;
        }

        var params = {
            id : this.model.id,
            message : this.model.get("message"),
            picture_id : this.model.get("picture_file_id")
        };
        this.$el.html(this.template(params));
        var taskStatusView = new TaskStatusView({status: this.model.get('status')});
        this.$el.find('.task-status-lbl').append(taskStatusView.render());
        return this.$el;
    },

    openTaskCard: function(e) {
        new TaskCardView({model: this.model});
        e.stopPropagation();
        return false;
    },

    addNewTaskHandler: function() {
        var emptyTask = {};
        emptyTask.message = null;
        emptyTask.description = null;
        new TaskCardView({model: new Task(emptyTask), createMode: true});
    },

    removeTask: function() {
        this.model.remove();
        return false;
    },

    refresh: function() {
        this.render();
    }
});