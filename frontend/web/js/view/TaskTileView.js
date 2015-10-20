/**
 * Created by Kit Ushakov on 10/20/2015.
 */


var app = app || {};

var TaskTileView = Backbone.View.extend({

    //tagName: 'div',

    //className: '',

    template: _.template( $('#task-tile-template').html()),

    initialize: function (options) {
        //this.id = this.model.id;
        //this.type = options.search;
        //this.listenTo(this.model, "CheckedRelatedProducts", this.actualizeRelatedButtons);
        //this.listenTo(this.model, "ActualizeTotalPrice", this.actualizeTotalPriceHandler);

        //_.bindAll(this, 'addItemToBasket');
    },


    render: function() {
        debugger;
        var params = {
            item : this.model.id,
            message : this.model.message
        };
        this.$el.html(this.template(params));
        return this.$el;
    },

});