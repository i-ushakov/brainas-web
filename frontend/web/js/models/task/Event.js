/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var app = app || {};


var Event = Backbone.Model.extend({
    type: null,

    params: {},

    initialize: function(event) {
        this.type = event.type;
        this.params = event.params
    },

    getEventInfo: function() {
        var info = "";
        if (this.params.address != undefined && this.params.address !="") {
            info = "Location: " + this.params.address;
        } else {
            info = "<span>Location:</span> " + "{lat:" + this.params.lat.toFixed(3) +
                ", lng:" + this.params.lng.toFixed(3) + ", rad:" + this.params.radius + "}";
            //GoogleApiHelper googleApiHelper = ((BrainasApp)BrainasApp.getAppContext()).getGoogleApiHelper();
            //googleApiHelper.setAddressByLocation((EventGPS)event);
        }

        return info;
    }

});