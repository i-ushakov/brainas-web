/**
 * Created by Kit Ushakov on 10/19/2015.
 */

var app = app || {};

var MainPanelView = Backbone.View.extend({

    el : '#main-panel',

    template: _.template($('#main-panel-template').html()),

    taskPanelView: null,

    taskPanelViewEl: '#task-panel',

    initialize: function() {
        this.render();
        this.taskPanelView = new TaskPanelView({model: this.model.taskPanel, el: this.taskPanelViewEl});
        this.taskPanelView.render();

        this.listenTo(this.model, "taskPanel:selected", this.showTaskPanel);
        _.bindAll(this, 'showTaskPanel');
    },

    render: function() {
        this.$el.html(this.template());
    },

    showTaskPanel: function() {
        this.taskPanelView.$el.show();
    }
});