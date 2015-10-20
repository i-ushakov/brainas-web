/**
 * Created by Kit Ushakov on 10/19/2015.
 */

var app = app || {};

var MainPanelView = Backbone.View.extend({

    el : '#main-panel',

    template: _.template($('#main-panel-template').html()),

    initialize: function() {
        _.bindAll(this, 'loadTaskPanel');

        this.listenTo(this.model, "ToggleToTaskPanel", this.loadTaskPanel);

       // this.model = app.MainPanel;

        this.render();
    },

    render: function() {
        debugger;
        var params = {};
        this.$el.html(this.template(params));
    },

    loadTaskPanel: function() {
        debugger;
        this.taskPanelView = new TaskPanelView({model: this.model.taskPanel});
    }
});