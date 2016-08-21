/**
 * Created by Kit on 10/19/2015
 */

var app = app || {};

app.location = null;

app.getCurrentUserLocation = function() {
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
    return app.singedIn;
}

app.refreshAuthorization = function() {
    $.ajax({
        url: "/site/refresh-authorization",
        success: function(result){
           // Nothing todo
        }
    });
}

app.getCurrentPositionError = function(error) {
    switch(error.code) {
        case error.PERMISSION_DENIED:
            console.log("User denied the request for Geolocation.");
            break;
        case error.POSITION_UNAVAILABLE:
            console.log("Location information is unavailable.");
            break;
        case error.TIMEOUT:
            console.log("The request to get user location timed out.");
            break;
        case error.UNKNOWN_ERROR:
            console.log("An unknown error occurred.");
            break;
    }
}

$(function () {
    app.MainPanel = new MainPanel();
    app.MainPanelView = new MainPanelView({model: app.MainPanel});
    app.MainPanel.toggleToTaskPanel();

    // check for Geolocation support
    if (navigator.geolocation) {
        console.log('Geolocation is supported!');
        navigator.geolocation.getCurrentPosition(function(position) {
            app.location = position;}, app.getCurrentPositionError );
    } else {
        console.log('Geolocation is not supported for this Browser/OS version yet.');
    }

    setInterval(app.refreshAuthorization, 1000 * 60 * 15);
});