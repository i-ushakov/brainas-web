/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var app = app || {};

var MainPanel = Backbone.Model.extend({

    defaults : {
        tasksControlBoard : null
    },

    taskPanel: null,

    initialize: function() {
        this.set('tasksControlBoard', new TasksControlBoard());
        this.taskPanel = new TaskPanel({tasksControlBoard : this.get('tasksControlBoard')});
        _.bindAll(this, 'toggleToTaskPanel');
    },

    toggleToTaskPanel: function() {
        this.taskPanel.loadTasks();
        this.trigger("taskPanel:selected");
    },

    getTypeOfSort: function() {
        var tasksControlBoard = this.get('tasksControlBoard');
        var typeOfSort = tasksControlBoard.getTypeOfSort();
        return typeOfSort;
    },

    getStatusesFilter: function() {
        var tasksControlBoard = this.get('tasksControlBoard');
        var statusesFilter = tasksControlBoard.getStatusesFilter();
        return statusesFilter;
    }
});