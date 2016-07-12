/**
 * Created by Kit Ushakov on 7/12/2016.
 */

var app = app || {};

var Picture = Backbone.Model.extend({
    urlRemove: app.url + 'picture/remove/',

    remove: function() {
        var self = this;
        var data = {
            picture: JSON.stringify(self)
        };

        $.ajax({
            type: "POST",
            url: this.urlRemove,
            data: data,
            success: function(result) {
                if (result.status == "OK") {
                    return true;
                } else if (result.status == "FAILED" && result.type == 'must_be_signed_in'){ // if was logged out on Yii2 side
                    alert("The operation has not been successful! Please repeat your actions after page reloading");
                    location.reload();
                }
            },
            dataType: 'json'
        });
    },

});