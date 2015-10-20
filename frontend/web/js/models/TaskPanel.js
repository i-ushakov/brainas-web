/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var app = app || {};

var TaskPanel = Backbone.Model.extend({
    tasks : null,

    initialize: function() {

        this.tasks = new Tasks();

        //this.order = new Order();

        //this.bonusCard = new BonusCard();
        //this.promoCode = new PromoCode();

        //this.searchModalBlock = new SearchModalBlock();

        //this.listenTo(this.bonusCard, "bonusPointsChanged", this.actualizeOrderPrice);

       // _.bindAll(this, '', '');
    },

    loadTasks: function() {
        var self = this;
        //this.tasks.fetch();
        debugger;
        //var tasks = this.tasks.fetch();
        console.log(this.tasks.models); // => 0 (collection being fetched)
        this.tasks.fetch({success: function(){
            console.log(self.tasks.models); // => 2 (collection have been populated)
        }});
        //this.tasks.add(new Task({id:1,massage: "Test"}))
    }
});