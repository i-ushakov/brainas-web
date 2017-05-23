/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var app = app || {};


var Event = Backbone.Model.extend({
    type: null,

    params: {},

    defaults : {
        validationErrors: []
    },

    initialize: function(event) {
        this.set('id', event.eventId);
        this.type = event.type;
        this.params = event.params;
    },

    getEventInfo: function() {
        var info = "";
        switch (this.type) {
            case "LOCATION" :
                if (this.params.address != undefined && this.params.address !="") {
                    info = "<span>Location:</span> " + this.params.address;
                } else {
                    info = "<span>Location:</span> " + "{lat:" + this.params.lat.toFixed(3) +
                        ", lng:" + this.params.lng.toFixed(3) + ", rad:" + this.params.radius + "}";
                    //GoogleApiHelper googleApiHelper = ((BrainasApp)BrainasApp.getAppContext()).getGoogleApiHelper();
                    //googleApiHelper.setAddressByLocation((EventGPS)event);
                }
                break;
            case "TIME" :
                info = this.params.datetime;
                break;
        }

        return info;
    },

    validate: function(attributes) {
        this.validationErrors = [];
        if (_.isEmpty(attributes.params)) {
            this.validationErrors.push("Empty params in event with CID = " + this.cid + " and id = " + this.id);
        }
        if(!_.isEmpty(this.validationErrors)) {
            return this.validationErrors;
        }
    }

});