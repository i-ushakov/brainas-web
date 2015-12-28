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
        'click .add-condition-btn': 'addConditionHandler',

        'keyup .task-message-edit textarea': 'changeMessageHandler',
        'keyup .task-description-edit textarea': 'changeDescriptionHandler',
    },

    template: _.template( $('#task-card-modal-template').html()),

    initialize: function (options) {
        _.bindAll(this, 'onSaveHandler', 'onConditionReadyHandler');
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
        this.addConditions();
    },

    refreshCard: function() {
        this.addConditions();
    },

    addConditions: function() {
        var self = this;
        self.conditionViews = [];

        self.$el.find('.task-conditions-cont').html('');

        if (self.model.get("conditions").length == 0) {
            self.$el.find('.task-conditions-cont').append("<div class='task-condition empty-condition add-condition-btn'>" +
                "<div class='plus-condition'>+</div><div class='add-condition'>Add condition</div>" +
                "<div class='clear'></div>" +
                "</div>");
        }

        _.each(self.model.get("conditions"), function(condition) {
            var conditionView = new TaskConditionView({
                    model: condition,
                    parent:self}
            );
            self.$el.find('.task-conditions-cont').append(
                conditionView.render());
            self.conditionViews.push(conditionView);
        })
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
        var conditions = this.model.get("conditions");
        var condition = new Condition(null);
        condition.on('eventWasAdded', this.onConditionReadyHandler);
        conditions.push(condition);
        var conditionSelectorView = new ConditionTypeSelectorView(condition);
        $(this.el).off('click', '.add-condition-btn');
        $(".add-condition-btn").html(conditionSelectorView.$el);
    },

    onConditionReadyHandler: function (obj) {
        this.addConditions();
        var addedConditionView = this.conditionViews[this.conditionViews.length - 1];
        addedConditionView.$el.find('.condition-row').trigger("click");
        $('#save-changes-btn').show();
    }
});