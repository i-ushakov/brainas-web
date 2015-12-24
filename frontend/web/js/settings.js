/**
 * Created by Kit on 10/19/2015
 */

var app = app || {};

app.url = '/frontend/web/',


$(function () {
    Backbone.View.prototype.close = function(){
        this.remove();
        this.unbind();
    };

    //Backbone.Model.prototype.toJSON = function(){
    //};
});