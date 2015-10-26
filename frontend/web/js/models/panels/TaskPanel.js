/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var app = app || {};

var TaskPanel = Backbone.Model.extend({

    tasks : null,

    initialize: function() {
        this.tasks = new Tasks();
    },

    loadTasks: function() {
        var self = this;
        this.tasks.fetch({success: function(){}});
    }
});