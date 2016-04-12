/**
 * Created by Kit on 10/19/2015
 */

var app = app || {};

app.url = '/';

app.location = null;

app.getCurrentUserLocation = function() {
    debugger;
    if (app.location !== null && app.location !== undefined) {
        return app.location.coords;
    } else {
        return null;
    }
}

app.setCurrentUserLocation = function(position) {
    app.location = position
}

app.isAuthorized = function() {
    return false;
}

app.refreshAuthorization = function() {
    $.ajax({
        url: "/site/refresh-authorization",
        success: function(result){
           // Nothing todo
        }
    });
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
        navigator.geolocation.getCurrentPosition(function(position) {app.location = position;} );
    } else {
        console.log('Geolocation is not supported for this Browser/OS version yet.');
    }

    setInterval(app.refreshAuthorization, 1000 * 60 * 15);
});