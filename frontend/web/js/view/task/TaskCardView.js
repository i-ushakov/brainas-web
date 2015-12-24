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

    messageTextArea : null,
    messageView: null,
    messageEditView: null,

    events: {
        'click .task-message-cont': 'editMessage',
        'click .cancel-edit-icon': 'cancelEditMessage',
        'click #save-changes-btn': 'save',
        'keyup .task-message-edit textarea': 'changeMessageHandler',
    },

    template: _.template( $('#task-card-modal-template').html()),

    initialize: function (options) {
        _.bindAll(this, 'onSaveHandler');
        this.render();
        this.messageTextArea = this.$el.find('.task-message-edit textarea');
        this.messageView = this.$el.find('.task-message-cont');
        this.messageEditView = this.$el.find('.task-message-edit');
        this.cancelEditIcon = this.messageEditView.find('.cancel-edit-icon-cont');
        debugger;
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

    addConditions: function() {
        var self = this;
        _.each(self.model.get("conditions"), function(condition) {
            self.$el.find('.task-conditions-cont').append(
            new TaskConditionView({
                    model: condition,
                    parent:self}
            ).render());})

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

    changeMessageHandler: function() {
        this.messageTextArea.css('background-color', '');
        $('#save-changes-btn').show();
        if (this.messageTextArea.val().length >= 100) {
            this.addInfoAlert('moreThan100Chars');
        }
    },

    changeGPSHandler: function() {
        $('#save-changes-btn').show();
    },

    save: function() {
        if (!this.validate()) {
            return false;
        }
        this.model.set("message", this.messageEditView.find("textarea").val());
        var result = this.model.save();
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
                debugger;
                app.MainPanelView.taskPanelView.model.tasks.add(new Task(result.task))
            }
        }
    }
});