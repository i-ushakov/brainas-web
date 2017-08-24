/**
 * Created by Kit Ushakov on 10/20/2015.
 */


var app = app || {};

var TaskTileView = Backbone.View.extend({

    template: _.template( $('#task-tile-template').html()),

    templateAddTaskBtn: _.template( $('#task-tile-new-template').html()),

    addTaskButton: false,

    className: 'task-tile-cont',

    events: {
        'click .task-tile': 'openTaskCard',
        'click .add-new-task-btn': 'addNewTaskHandler',
        'click .delete-img-cont': 'removeTask',
    },

    taskStatusView : null,

    initialize: function (options) {
        _.bindAll(this, 'refresh');

        this.addTaskButton = options.addTaskButton;

        this.render();

        // elements
        this.messageEl = this.$el.find('.task-message');
        this.pictureEl = this.$el.find('.task-tile-image-cont img');

        if (this.model) {
            this.model.on({"change": this.refresh});
        }

        return this;
    },

    render: function() {
        if (this.addTaskButton == true) {
            this.$el.html(this.templateAddTaskBtn());
            return this;
        }

        this.renderTile();
        this.addStatusView();

        return this;
    },

    renderTile: function() {
        var params = {
            id : this.model.id,
            message : this.model.get("message"),
            picture_id : this.model.get("picture_file_id")
        };
        this.$el.html(this.template(params));
    },

    addStatusView:function () {
        this.taskStatusView = new TaskStatusView({model: this.model});
        this.$el.find('.task-status-lbl').append(this.taskStatusView.render());
    },

    openTaskCard: function(e) {
        new TaskCardView({model: this.model});
        e.stopPropagation();
        return false;
    },

    addNewTaskHandler: function() {
        var newTaskData = {};
        newTaskData.message = null;
        newTaskData.description = null;
        newTaskData.status = "TODO";
        new TaskCardView({model: new Task(newTaskData), createMode: true});
    },

    removeTask: function() {
        this.model.remove();
        return false;
    },

    refresh: function() {
        this.messageEl.html(this.model.get('message'));
        var pictureFileId = this.model.get("picture_file_id");
        if (pictureFileId === undefined) {
            this.pictureEl.attr('src', app.url + "images/pictures.png");
        } else {
            this.pictureEl.attr('src', app.googleDriveImageUrl + this.model.get("picture_file_id"));
        }
    }
});