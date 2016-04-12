/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var Tasks = Backbone.Collection.extend({
    model: Task,

    url: '/task/get',

    initialize: function() {
        var self = this;
        this.timer = setInterval(function() {
            self.fetch();
        }, 15000);
    },

    close: function() {
        clearInterval(this.timer);
    }
});