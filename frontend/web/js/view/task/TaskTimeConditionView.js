/**
 * Created by Kit Ushakov on 10/20/2015.
 */


var app = app || {};

var TaskTimeConditionView = Backbone.View.extend({

    parent: null,

    map: null,

    marker: null,

    template: _.template($('#task-time-condition-template').html()),

    events: {
        'click .condition-row': 'toggleConditionArea',
        'click .delete-condition-icon': 'deleteConditionHandler'
    },

    tEvents : {},

    initialize: function (options) {
        this.parent = options.parent;
        this.event = this.model.get("events").TIME || null;
        this.render();
    },

    render: function() {
        var params = {
            event: this.event
        };
        this.$el.html(this.template(params));
        return this.$el;
    },

    toggleConditionArea: function() {
        this.$el.find('.condition-collapse').toggle();
        //this.map = this.map || this.initializeMap();
        this.initializeDatePicker();
    },

    collapseConditionArea: function() {
        if (this.$el.find('.condition-collapse').is(":visible")) {
            this.$el.find('.condition-collapse').toggle();
        }
    },

    initializeDatePicker: function() {
        var self = this;
        var datetime = this.event.get("params").datetime;
        this.$el.find(".datetimepicker").datetimepicker({
            inline: true,
            sideBySide: true,
            defaultDate: moment(datetime, "DD-MM-YYYY hh:mm:ss")
        });

        this.$el.find(".datetimepicker").on("dp.change", function (e) {
            params = {datetime: e.date.format('DD-MM-YYYY hh:mm:ss'), offset: e.date.utcOffset()};
            self.event.set("params", params);
            self.model.trigger("conditionWasChanged", this.model);
        });
    },

    deleteConditionHandler: function() {
        this.model.trigger("conditionWasRemoved", this.model);
        this.remove();
        return false;
    }
});