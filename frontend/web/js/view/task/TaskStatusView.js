/**
 * Created by kit on 8/29/2016.
 */

var app = app || {};

var TaskStatusView = Backbone.View.extend({

    parent: null,

    map: null,

    marker: null,

    template: _.template($('#task-status-template').html()),

    events: {},

    initialize: function (options) {
        debugger;
        this.status = options.status;
    },

    render: function() {
        this.$el.html(this.template({status: this.status}));
        return this.$el;
    }
});