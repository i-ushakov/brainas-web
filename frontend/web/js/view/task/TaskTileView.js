/**
 * Created by Kit Ushakov on 10/20/2015.
 */


var app = app || {};

var TaskTileView = Backbone.View.extend({

    template: _.template( $('#task-tile-template').html()),

    events: {
        'click .task-tile': 'openTaskCard'
    },

    initialize: function (options) {},

    render: function() {
        var params = {
            item : this.model.id,
            message : this.model.message
        };
        this.$el.html(this.template(params));
        return this.$el;
    },

    openTaskCard: function() {
        new TaskCardView({model: this.model});
    }

});