/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var app = app || {};

var MainPanel = Backbone.Model.extend({

    defaults : {
        tasksControlPanel : null
    },

    taskPanel: null,

    initialize: function() {
        this.set('tasksControlBoard', new TasksControlBoard());
        this.taskPanel = new TaskPanel({tasksControlBoard : this.get('tasksControlBoard')});
        _.bindAll(this, 'toggleToTaskPanel');
    },

    toggleToTaskPanel: function() {
        var statusesFilter = this.get('tasksControlBoard').get('statusesFilter');
        var params = {
            statusesFilter : statusesFilter
        }

        this.taskPanel.tasks.set("fetchOptions", params);
        this.taskPanel.loadTasks();
        this.trigger("taskPanel:selected");
    }
});