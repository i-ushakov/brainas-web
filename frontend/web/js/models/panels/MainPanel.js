/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var app = app || {};

var MainPanel = Backbone.Model.extend({

    taskPanel: null,

    initialize: function() {
        this.taskPanel = new TaskPanel();

        _.bindAll(this, 'toggleToTaskPanel');
    },

    toggleToTaskPanel: function() {
        this.taskPanel.loadTasks();
        this.trigger("taskPanel:selected");
    }
});