/**
 * Created by Kit on 10/19/2015
 */

var app = app || {};

app.url = '/',

app.getCurrentUserLocation = function() {
    var location = {};
    location.lat = 55.595865;
    location.lng = 38.113754;
    return location;
}

$(function () {
    Backbone.View.prototype.close = function(){
        this.remove();
        this.unbind();
    }

    app.MainPanel = new MainPanel();
    app.MainPanelView = new MainPanelView({model: app.MainPanel});
    app.MainPanel.toggleToTaskPanel();
});