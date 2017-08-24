/**
 * Created by Kit Ushakov on 10/23/2015.
 */


var app = app || {};

var Condition = Backbone.Model.extend({
    type : null,

    defaults : {
        id : null
    },

    initialize : function(condition) {
        if (condition != null) {
            this.set('id', condition.conditionId);
            this.set('conditionId', condition.conditionId);
            this.set('eventType', condition.eventType);
            this.set('params', condition.params);
        }
    },

    getConditionInfo: function() {
        var info = "";
        switch (this.get('eventType')) {
            case "LOCATION" :
                if (this.get('params').address != undefined && this.get('params').address !="") {
                    info = "<span>Location:</span> " + this.get('params').address;
                } else {
                    info = "<span>Location:</span> " + "{lat:" + this.get('params').lat.toFixed(3) +
                        ", lng:" + this.get('params').lng.toFixed(3) + ", rad:" + this.get('params').radius + "}";
                    //GoogleApiHelper googleApiHelper = ((BrainasApp)BrainasApp.getAppContext()).getGoogleApiHelper();
                    //googleApiHelper.setAddressByLocation((EventGPS)event);
                }
                break;
            case "TIME" :
                info = this.get('params').datetime;
                break;
        }

        return info;
    }
});