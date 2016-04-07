/**
 * Created by Kit on 10/19/2015
 */

var app = app || {};

app.url = '/';

app.location = null;

app.getCurrentUserLocation = function() {
    return app.location.coords;
}

app.setCurrentUserLocation = function(position) {
    debugger;
    app.location = position
}

app.isAuthorized = function() {
    return false;
}

$(function () {
    Backbone.View.prototype.close = function(){
        this.remove();
        this.unbind();
    }

    app.MainPanel = new MainPanel();
    app.MainPanelView = new MainPanelView({model: app.MainPanel});
    app.MainPanel.toggleToTaskPanel();

    // check for Geolocation support
    if (navigator.geolocation) {
        console.log('Geolocation is supported!');
        navigator.geolocation.getCurrentPosition(function(position) {debugger; app.location = position;} );
    } else {
        console.log('Geolocation is not supported for this Browser/OS version yet.');
    }
});