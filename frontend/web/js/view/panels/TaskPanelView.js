/**
 * Created by Kit Ushakov on 10/19/2015.
 */

var app = app || {};

var TaskPanelView = Backbone.View.extend({
    taskTileViews: [],

    events: {
        'click #sing-in-btn': 'signInBtnHandler',
        'click #signinButtonCenter' : 'signInBtnHandler'
    },

    template: _.template($('#task-panel-template').html()),

    initialize: function() {
        _.bindAll(this, 'onTaskSaveHandler', 'rerenderAllTiles');

        var self = this;

        self.model.tasks.on("add", function(model) {
            model.on({"save": self.onTaskSaveHandler});
            model.on({"change": self.onTaskSaveHandler});
            //self.addTask(model);
        });

        self.model.tasks.on('sort', this.rerenderAllTiles)

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

    rerenderAllTiles: function() {
        var self = this;
        self.model.tasks.each(function(task) {
            self.removeTask(task);
            self.addTask(task);
        });
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

    onTaskSaveHandler: function (task) {
        //var task = result.task;
        var taskTileView = this.taskTileViews[task.id];
        if (taskTileView != undefined && taskTileView != null) {
            taskTileView.refresh();
        }
    },

    signInBtnHandler : function () {
        auth2.grantOfflineAccess({'redirect_uri': 'postmessage'}).then(signInCallback);
        return false;
    }

});