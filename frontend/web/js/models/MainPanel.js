/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var app = app || {};

var MainPanel = Backbone.Model.extend({
    taskPanel: null,

    initialize: function() {
        this.taskPanel = new TaskPanel();

        //this.order = new Order();

        //this.bonusCard = new BonusCard();
        //this.promoCode = new PromoCode();

        //this.searchModalBlock = new SearchModalBlock();

        //this.listenTo(this.bonusCard, "bonusPointsChanged", this.actualizeOrderPrice);

       _.bindAll(this, 'toggleToTaskPanel');
    },

    toggleToTaskPanel: function() {
        this.trigger("ToggleToTaskPanel");
    }
});