/**
 * Created by Kit Ushakov on 10/19/2015.
 */

var app = app || {};

var MainPanelView = Backbone.View.extend({

    events: {
        'click #top-panel-categories-tab': 'showMessageAboutNextVersions',
        'click #top-panel-settings-tab': 'showMessageAboutNextVersions',
        'click #top-panel-users-tab': 'showMessageAboutNextVersions'
    },

    el : '#main-panel',

    template: _.template($('#main-panel-template').html()),

    taskPanelView: null,

    taskPanelViewEl: '#task-panel',
    tasksControlBoardViewEl : '#task-controls-board',

    initialize: function() {
        this.render();
        this.taskPanelView = new TaskPanelView({model: this.model.taskPanel, el: this.taskPanelViewEl});
        this.taskPanelView.render();
        this.tasksControlBoard = new TasksControlBoardView({model: this.model.get('tasksControlBoard'), el: this.tasksControlBoardViewEl});
        this.tasksControlBoard.render();

        this.listenTo(this.model, "taskPanel:selected", this.showTaskPanel);
        _.bindAll(this, 'showTaskPanel');
    },

    render: function() {
        this.$el.html(this.template());
        if (app.isAuthorized()) {
            this.showTopControlPanel();
        }
    },

    showTopControlPanel : function() {
        $('#top-control-panel').show()
    },

    showTaskPanel: function() {
        // TODO show control task panel in top
        this.taskPanelView.$el.show();
    },

    showMessageAboutNextVersions: function() {
        BootstrapDialog.show({
            title: '',
            message: 'This section will be available in next versions'
        });
    }
});