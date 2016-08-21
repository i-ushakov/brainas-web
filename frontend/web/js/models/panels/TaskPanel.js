/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var app = app || {};

var TaskPanel = Backbone.Model.extend({

    tasks : null,

    tasksControlBoard: null,

    initialize: function(options) {
        this.tasksControlBoard = options.tasksControlBoard;
        this.listenTo(this.tasksControlBoard, "change", this.loadTasks);
        this.tasks = new Tasks();

        _.bindAll(this, 'loadTasks');
    },

    loadTasks: function() {
        var self = this;
        // If user is signed in, get tasks
        if (app.singedIn == true) {
            this.tasks.fetch({
                success: function () {
                }
            });
        }
    }
});