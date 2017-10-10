/**
 * Created by kit on 9/27/2017.
 */

var app = app || {};

var ConditionsPanelView = Backbone.View.extend({

    conditionViews: [],

    events : {
        'click #addConditionBtn': 'addConditionHandler'
    },

    template: _.template( $('#conditions-panel-template').html()),

    initialize: function (options) {
        _.bindAll(this,
            'onDeleteConditionHandler',
            'addConditionHandler',
            'onEventTypeSelectedHandler'
        );

        var self = this;

        this.render();

        this.$el.attr('id','conditionsCont_' + self.model.get('id'));

    },

    render: function () {
        var self = this;
        self.$el.html(this.template());

        self.model.get("conditions").each(function(condition) {
            if (condition.get('eventType') == 'LOCATION') {
                var conditionView = new TaskLocationConditionView({
                        model: condition,
                        parent: self
                    }
                );
            } else if (condition.get('eventType') == 'TIME') {
                var conditionView = new TaskTimeConditionView({
                        model: condition,
                        parent: self
                    }
                );
            }

            self.$el.find('.task-conditions-cont').append(
                conditionView.render());
            self.conditionViews.push(conditionView);

            condition.on('conditionWasRemoved', self.onDeleteConditionHandler);
        });
    },

    onDeleteConditionHandler: function(condition) {
        this.model.get("conditions").remove(condition);
        this.model.trigger('change', this.model);
    },

    addConditionHandler: function(event) {
        this.collapseAllConditions();
        this.$el.find('#addConditionBtn').addClass('disabled');
        $(this.el).off('click', '#addConditionBtn');

        var conditions = this.model.get("conditions");
        conditions.on('eventWasAdded', this.onEventTypeSelectedHandler);
        conditions.on('conditionWasCancled', this.onConditionCancledHandler);

        var conditionSelectorView = new ConditionTypeSelectorView(conditions);
        this.$el.find('.condition-type-selector-cont').html(conditionSelectorView.$el);
    },

    collapseAllConditions: function () {
        var self = this;
        _.each(self.conditionViews, function (condition) {
            condition.collapseConditionArea()
        });
    },

    onEventTypeSelectedHandler: function (obj) {
        var self = this;
        self.render();
        var addedConditionView = self.conditionViews[self.conditionViews.length - 1];
        addedConditionView.$el.find('.condition-row').trigger("click");
        $('#addConditionBtn').removeClass('disabled');
        $(this.el).on('click', '#addConditionBtn', function() {self.addConditionHandler();});
        this.model.trigger('change', this.model);
    },

    onConditionCancledHandler: function(condition) {
        var self = this;
        self.model.get("conditions").remove(condition);
        $('#addConditionBtn').removeClass('disabled');
        $(self.el).on('click', '#addConditionBtn', function() {self.addConditionHandler();});
    },

    destroy: function () {
        this.conditionViews.forEach(function (condition) {
            condition.destroy();
        });

        this.undelegateEvents();
        this.$el.removeData().unbind();
        this.remove();
        Backbone.View.prototype.remove.call(this);
    }
});