/**
 * Created by Kit Ushakov on 10/19/2015.
 */

var app = app || {};

var TaskPanelView = Backbone.View.extend({

    template: _.template($('#task-panel-template').html()),

    initialize: function() {
        var self = this;

        self.model.tasks.on("add", function(model) {
            self.addTask(model);
        });

        self.model.tasks.on("remove", function(model) {
            //$('tr[id=' + model.id + ']').remove();
        });
    },

    render: function() {
        this.$el.html(this.template());
    },

    addTask: function(task) {
        var taskEl = new TaskTileView({model : task}).render();
        this.$el.append(taskEl);
    }
});