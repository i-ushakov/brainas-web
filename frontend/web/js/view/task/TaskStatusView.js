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
        _.bindAll(this, 'render');

       this.model.on({"change": this.render});
    },

    render: function() {
        this.$el.html(this.template({status: this.model.get('status')}));
        return this.$el;
    }
});