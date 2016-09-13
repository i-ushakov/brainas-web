/**
 * Created by Kit Ushakov on 8/12/2016.
 */

var app = app || {};


var TasksControlBoard = Backbone.Model.extend({

    STATUSES : {
        ACTIVE: "ACTIVE",
        WAITING : "WAITING",
        TODO : "TODO",
        DISABLED : "DISABLED",
        DONE : "DONE",
        CANCELED : "CANCELED",
    },

    SORT_TYPES : {
        TIME_ADDED_NEWEST : 'TIME_ADDED_NEWEST',
        TIME_ADDED_OLDEST : 'TIME_ADDED_OLDEST',
        LATEST_CHANGES : 'LATEST_CHANGES',
        TASK_TITLE : 'TASK_TITLE'
    },

    defaults : {
        statusesFilter : ["ACTIVE", "WAITING", "TODO"],
        typeOfSort: 'TIME_ADDED_NEWEST'
    },

    addAllToStatusesFilter : function() {
        this.set('statusesFilter', _.values(this.STATUSES));
    },

    clearStatusesFilter : function() {
        this.set('statusesFilter', []);
    },

    getTypeOfSort: function() {
        return this.get('typeOfSort');
    },

    getStatusesFilter: function () {
        return this.get('statusesFilter');
    }
});