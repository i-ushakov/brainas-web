/**
 * Created by Kit on 10/19/2015
 */

var app = app || {};

app.url = '/frontend/web/',


$(function () {
    Backbone.View.prototype.close = function(){
        this.remove();
        this.unbind();
    }

    app.MainPanel = new MainPanel();
    app.MainPanelView = new MainPanelView({model: app.MainPanel});
    app.MainPanel.toggleToTaskPanel();
});