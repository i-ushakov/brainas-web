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

       this.listenTo(this.model, 'change', this.render);
    },

    render: function() {
        this.$el.html(this.template({status: this.model.get('status')}));
        this.$el.attr('id','statusView_' + this.model.get('id'));
        return this.$el;
    },

    destroy: function () {
        this.undelegateEvents();
        this.$el.removeData().unbind();
        this.remove();
        Backbone.View.prototype.remove.call(this);
    }
});