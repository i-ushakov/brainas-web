/**
 * Created by Kit Ushakov on 10/20/2015.
 */


var app = app || {};

var TaskCardView = Backbone.View.extend({

    template: _.template( $('#task-card-modal-template').html()),

    initialize: function () {
        this.render();
    },

    render: function() {
        var params = {
            message: this.model.message,
            description: this.model.description
        };
        this.setElement($(this.template(params)).modal('show'));
        this.addConditions();
    },

    addConditions: function() {
        this.$el.find('.task-conditions-cont').append(new TaskConditionView({model: this.model.conditions[0]}).render());
    }
});