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
        'mouseenter .task-picture-cont' : 'showChangePictureBtn',
        'mouseleave .task-picture-cont' : 'hideChangePictureBtn',
        'click .task-picture-cont' : 'showChangePictureBlock',
        'click #cancelPictureBtn' : 'cancelChangePicture',
        'change #pictureUploadBtn' : 'uploadPictureHandler',


        'keyup .task-message-edit textarea': 'changeMessageHandler',
        'keyup .task-description-edit textarea': 'changeDescriptionHandler',
    },

    template: _.template( $('#task-card-modal-template').html()),

    initialize: function (options) {
        _.bindAll(this, 'onSaveHandler', 'onEventTypeSelectedHandler', 'onConditionCancledHandler', 'onDeleteConditionHandler');

        if (this.model === undefined) {
            return;
        }
        this.model.set("preventUpdateFromServer", true);

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

        //this.listenTo(this.model, 'change', this.renderContent); //TODO render internal part of window updatable
        this.model.on({"save": this.onSaveHandler});
    },

    render: function() {
        var self = this;
        var params = {
            message: this.model.get("message"),
            description: this.model.get("description"),
            picture_id: this.model.get("picture_google_drive_id"),
            createMode: this.createMode
        };
        var modal = $(this.template(params).trim())
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
        this.saveBtn.show();
        if (this.messageTextArea.val().length >= 100) {
            this.addInfoAlert('moreThan100Chars');
        }
    },

    changeDescriptionHandler: function() {
        this.saveBtn.show();
    },

    changeGPSHandler: function() {
        this.saveBtn.show();
    },

    save: function() {
        if (!this.validate()) {
            return false;
        }
        this.model.set("message", this.messageEditView.find("textarea").val());
        this.model.set("description", this.descriptionEditView.find("textarea").val());
        var result = this.model.save();
        this.saveBtn.hide();
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
        this.saveBtn.show();
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
        this.saveBtn.show();
    },

    taskWasChangedHandler: function() {
        this.saveBtn.show();
    },

    remove: function(){
        this.model.set("preventUpdateFromServer", false);
    },

    showChangePictureBtn: function() {
        $('.change-picture-btn').show();
    },

    hideChangePictureBtn: function() {
        $('.change-picture-btn').hide();
    },

    showChangePictureBlock: function() {

    },

    cancelChangePicture: function() {
        $('.picture-picker-block').collapse('toggle');
    },

    uploadPictureHandler: function(event) {
        /*$('form[name=upload_picture]').on('submit', function(e) {
            debugger;
            e.preventDefault();
        });
        /*var options=
        {
            target:'#preview',
            url:'https://brainas.com',
            beforeSubmit:  showLoading,
            error: function(e){
                alert("Error: " + e);
            }
        };
        function showLoading(){
            debugger;
            $('#preview').html("<img src='images/loader.gif' alt='Loading.....'/>");
        }
        $('form[name=upload_picture]').ajaxForm(options);*/

        /*$('form[name=upload_picture]').ajaxSubmit();*/


        function readURL(input) {
            if (!window.File || !window.FileReader || !window.FileList || !window.Blob) {
                alert('The File APIs are not fully supported in this browser.');
                return;
            }
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {

                    $('#image').attr('src', e.target.result);
                    $.post('/picture/upload', {imageData:e.target.result}, function(data){
                        debugger;
                        //recieve information back from php through the echo function(not required)

                    });
                };


                debugger;
                reader.readAsDataURL(input.files[0]);
            }
        }

        var inputElement = event.target

        readURL(inputElement);
    },


});