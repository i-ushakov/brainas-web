/**
 * Created by Kit Ushakov on 10/20/2015.
 */


var app = app || {};

var TaskCardView = Backbone.View.extend({

    createMode: false,

    infoMessages: {
        emptyMessage: "Message cannot be empty!",
        moreThan100Chars: "Message cannot be more than 100 character!"
    },

    conditionViews: [],

    messageTextArea : null,
    messageView: null,
    messageEditView: null,

    events: {
        'click .task-message-cont': 'editMessage',
        'click .task-description-cont': 'editDescription',
        'click .task-message-edit .cancel-edit-icon': 'cancelEditMessage',
        'click .task-description-edit .cancel-edit-icon': 'cancelEditDescription',
        'click #save-changes-btn': 'save',
        'click #addConditionBtn': 'addConditionHandler',

        'keyup .task-message-edit textarea': 'changeMessageHandler',
        'keyup .task-description-edit textarea': 'changeDescriptionHandler',
    },

    template: _.template( $('#task-card-modal-template').html()),

    initialize: function (options) {
        if (this.model === undefined) {
            return;
        }
        _.bindAll(this, 'onSaveHandler', 'onEventTypeSelectedHandler', 'onConditionCancledHandler', 'onDeleteConditionHandler');
        this.render();
        this.messageTextArea = this.$el.find('.task-message-edit textarea');
        this.messageView = this.$el.find('.task-message-cont');
        this.messageEditView = this.$el.find('.task-message-edit');
        this.cancelEditIcon = this.messageEditView.find('.cancel-edit-icon-cont');
        this.descriptionView = this.$el.find('.task-description-cont');
        this.descriptionEditView = this.$el.find('.task-description-edit');

        this.saveBtn = this.$el.find('#save-changes-btn');
        if (options.createMode == true) {
            this.prepareForNewTask();
            this.createMode = true;
            this.model.set("id", null);
            this.saveBtn.text("Create");
        }

        this.model.on({"save": this.onSaveHandler});
    },

    render: function() {
        var self = this;
        var params = {
            message: this.model.get("message"),
            description: this.model.get("description"),
            createMode: this.createMode
        };
        var modal = $(this.template(params));
        this.setElement(modal.modal('show'));
        $(modal.on('hidden.bs.modal', function () {
            self.close();
        }));
        this.renderConditions();
    },

    refreshCard: function() {
        this.renderConditions();
    },

    renderConditions: function() {
        var self = this;
        self.conditionViews = [];

        self.$el.find('.task-conditions-cont').html('');

        self.model.get("conditions").each(function(condition) {
            if (condition.getType() == 'GPS') {
                var conditionView = new TaskLocationConditionView({
                        model: condition,
                        parent: self
                    }
                );
            } else if (condition.getType() == 'TIME') {
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
            condition.on('conditionWasChanged', self.taskWasChangedHandler);
        });
    },

    editMessage: function() {
        this.messageView.toggle();
        this.messageEditView.toggle();
        this.messageTextArea.css('background-color', '');
    },

    cancelEditMessage() {
        this.messageView.toggle();
        this.messageEditView.toggle();
    },

    cancelEditDescription: function() {
        this.descriptionView.toggle();
        this.descriptionEditView.toggle();
    },

    editDescription: function() {
        this.descriptionView.toggle();
        this.descriptionEditView.toggle();
    },

    changeMessageHandler: function() {
        this.messageTextArea.css('background-color', '');
        $('#save-changes-btn').show();
        if (this.messageTextArea.val().length >= 100) {
            this.addInfoAlert('moreThan100Chars');
        }
    },

    changeDescriptionHandler: function() {
        $('#save-changes-btn').show();
    },

    changeGPSHandler: function() {
        $('#save-changes-btn').show();
    },

    save: function() {
        if (!this.validate()) {
            return false;
        }
        this.model.set("message", this.messageEditView.find("textarea").val());
        this.model.set("description", this.descriptionEditView.find("textarea").val());
        var result = this.model.save();
        $('#save-changes-btn').hide();
    },

    validate: function() {
        if (this.messageTextArea.val() == '') {
            this.messageTextArea.css('background-color', '#ff80a0');
            this.addInfoAlert('emptyMessage');
            return false;
        }
        return true;
    },

    addInfoAlert: function (messageId) {
        if ($('#message-info-' + messageId).size() == 0) {
            $('.alerts-cont').append(
                "<div class='alert alert-info' id='message-info-" + messageId + "'>" +
                "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>" +
                this.infoMessages[messageId] +
                "</div>"
            );
        }
    },

    prepareForNewTask: function() {
        this.messageView.hide();
        this.messageEditView.show();
        this.cancelEditIcon.hide();
    },

    onSaveHandler: function(result) {
        if (result.status == "OK") {
            this.saveBtn.hide();
            if (this.createMode == true) {
                this.$el.modal("hide");
                var newTask = new Task(result.task);
                app.MainPanelView.taskPanelView.model.tasks.add(newTask);
            }
            this.model.update(result.task);
            this.refreshCard();
        }
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

    onEventTypeSelectedHandler: function (obj) {
        var self = this;
        self.renderConditions();
        var addedConditionView = self.conditionViews[self.conditionViews.length - 1];
        addedConditionView.$el.find('.condition-row').trigger("click");
        $('#addConditionBtn').removeClass('disabled');
        $(this.el).on('click', '#addConditionBtn', function() {self.addConditionHandler();});
        $('#save-changes-btn').show();
    },

    onConditionCancledHandler: function(condition) {
        var self = this;
        self.model.get("conditions").remove(condition)
        $('#addConditionBtn').removeClass('disabled');
        $(self.el).on('click', '#addConditionBtn', function() {self.addConditionHandler();});
    },

    collapseAllConditions: function () {
        var self = this;
        _.each(self.conditionViews, function (condition) {
            condition.collapseConditionArea()
        });
    },

    onDeleteConditionHandler: function(condition) {
        this.model.get("conditions").remove(condition);
        $('#save-changes-btn').show();
    },

    taskWasChangedHandler: function() {
        $('#save-changes-btn').show();
    }
});