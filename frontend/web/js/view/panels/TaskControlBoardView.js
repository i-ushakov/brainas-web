/**
 * Created by Kit Ushakov on 8/11/2016.
 */

var app = app || {};

var TasksControlBoardView = Backbone.View.extend({

    events: {
        'change input[name=statuses_filter]': 'onStatusFilterChanged',
        'change select[name=type_of_sort]': 'onSortChanged',

    },

    template: _.template($('#tasks-control-board-template').html()),

    initialize: function() {
    },

    render: function() {
        var params = {};
        statusesFilter = this.model.get('statusesFilter');

        if (statusesFilter.indexOf(this.model.STATUSES.ACTIVE) >= 0) {
            params.active = true;
        }
        if (statusesFilter.indexOf(this.model.STATUSES.WAITING) >= 0) {
            params.waiting = true;
        }
        if (statusesFilter.indexOf(this.model.STATUSES.DISABLED) >= 0) {
            params.disabled = true;
        }
        if (statusesFilter.indexOf(this.model.STATUSES.DONE_STATUS) >= 0) {
            params.done = true;
        }
        if (statusesFilter.indexOf(this.model.STATUSES.CANCELED_STATUS) >= 0) {
            params.canceled = true;
        }
        if (statusesFilter.length == 5) {
            params.all = true;
        }
        this.$el.html(this.template(params));
    },

    onStatusFilterChanged : function(event) {
        if (this.handleAllStatusesCase(event)) {
            return;
        }
        var status = $(event.target).val();
        var statusesFilter = this.model.get('statusesFilter');

        if ($(event.target).prop("checked") == true) {
            this.addStatusToFilter(status, statusesFilter);
        } else {
            this.removeStatusFromFilter(status, statusesFilter);
        }
        this.model.set('statusesFilter', statusesFilter);
        this.model.trigger("change");
    },

    handleAllStatusesCase(event) {
        var status = $(event.target).val();
        if (status == "ALL") {
            if ($(event.target).prop("checked") == true) {
                $('input[name=statuses_filter][value!="ALL"]').prop("checked", true);
                statusesFilter = [];
                this.highlightStatusFilterBlock();
                this.model.addAllToStatusesFilter();
            } else {
                $('input[name=statuses_filter][value!="ALL"]').prop("checked", false);
                this.model.clearStatusesFilter();
                this.fadeStatusFilterBlock();
            }
            this.model.trigger("change");
            return true;
        }
        return false;
    },

    fadeStatusFilterBlock : function() {
        $('#statuses-filter-block').removeClass('panel-primary');
        $('#statuses-filter-block').addClass('panel-info');
    },

    highlightStatusFilterBlock: function() {
        $('#statuses-filter-block').removeClass('panel-info');
        $('#statuses-filter-block').addClass('panel-primary');
    },

    addStatusToFilter: function(status, statusesFilter) {
        var index = statusesFilter.indexOf(status);
        if (index >= 0) {
            return;
        } else {
            statusesFilter.push(status);
        }
    },

    removeStatusFromFilter: function(status, statusesFilter) {
        var index = statusesFilter.indexOf(status);
        if (index >= 0) {
            statusesFilter.splice(index, 1);
        }
    },

    onSortChanged: function() {
        var typeOfSort = $('select[name="type_of_sort"]').val();
        this.model.set('typeOfSort', typeOfSort);
        if (typeOfSort != "CHANGES") {
            this.highlightSortTypeBlock();
        } else {
            this.fadeSortTypeBlock();
        }
        this.model.trigger("change");
    },

    highlightSortTypeBlock: function() {
        $('#type-of-sort-block').removeClass('panel-info');
        $('#type-of-sort-block').addClass('panel-primary');
    },

    fadeSortTypeBlock: function() {
        $('#type-of-sort-block').removeClass('panel-primary');
        $('#type-of-sort-block').addClass('panel-info');
    }
});