/**
 * Created by Kit Ushakov on 8/12/2016.
 */

var app = app || {};


var TasksControlBoard = Backbone.Model.extend({

    STATUSES : {
        ACTIVE: "ACTIVE",
        WAITING : "WAITING",
        DISABLED : "DISABLED",
        DONE : "DONE",
        CANCELED : "CANCELED",
    },

    SORT_TYPES : {
        LAST_CHANGES : 'CHANGES',
        CREATION_TIME : 'CREATION',
        TASK_TITLE : 'TITLE'
    },

    defaults : {
        statusesFilter : ["ACTIVE", "WAITING", "DISABLED"],
        typeOfSort: 'CHANGES'
    },

    addAllToStatusesFilter : function() {
        this.set('statusesFilter', _.values(this.STATUSES));
    },

    clearStatusesFilter : function() {
        this.set('statusesFilter', []);
    }
});