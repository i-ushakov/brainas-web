/**
 * Created by Kit Ushakov on 10/20/2015.
 */


var app = app || {};

var TaskCardView = Backbone.View.extend({

    createMode: false,

    infoMessages: {
        emptyMessage: "Message cannot be empty!",
        moreThan100Chars: "Message cannot be more than 100 character!",
        bigPicture : "You cannot upload picture more than 10 MB",
        wrongPictureFormat : "You can upload only JPG and PNG files"
    },

    messageView: null,

    taskStatusView : null,

    conditionViews: [],

    events: {
        'click .task-description-cont': 'editDescription',
        'click .task-description-edit .cancel-edit-icon': 'cancelEditDescription',
        'click #save-changes-btn': 'save',
        'click #addConditionBtn': 'addConditionHandler',
        'mouseenter .task-picture-cont' : 'showChangePictureBtn',
        'mouseleave .task-picture-cont' : 'hideChangePictureBtn',
        'click .task-picture-cont' : 'showChangePictureBlock',
        'click #cancelPictureBtn' : 'cancelChangePicture',
        'click #savePictureBtn' : 'saveNewPicture',
        'change #pictureUploadBtn' : 'uploadPictureHandler',
        'input #downloadRefInput' : 'onDownloadRefChanged',

        'keyup .task-description-edit textarea': 'changeDescriptionHandler',
    },

    template: _.template( $('#task-card-modal-template').html()),

    initialize: function (options) {
        _.bindAll(this,
            'onSaveHandler',
            'onEventTypeSelectedHandler',
            'onConditionCancledHandler',
            'onDeleteConditionHandler',
            'saveNewPicture',
            'uploadPictureHandler',
            'cancelChangePicture',
            'taskWasChangedHandler',
            'changeMessageHandler',
            'close');

        if (this.model === undefined) {
            return;
        }

        if (options.createMode) {
            this.createMode = options.createMode;
        } else {
            this.model.set("preventUpdateFromServer", true);
        }

        this.render();

        // elements
        this.pictureEl = this.$el.find('.task-picture-cont img');
        this.descriptionView = this.$el.find('.task-description-cont');
        this.descriptionEditView = this.$el.find('.task-description-edit');
        this.saveBtn = this.$el.find('#save-changes-btn');

        if (this.createMode == true) {
            this.model.set("id", null);
            this.saveBtn.text("Create");
        }

        //this.listenTo(this.model, 'change', this.renderContent); //TODO render internal part of window updatable

        this.model.on({"change": this.changeMessageHandler});
        this.model.on({"save": this.onSaveHandler});
    },

    render: function() {
        var self = this;
        var modal = this.renderCard();
        this.setElement(modal.modal('show'));
        $(modal.on('hidden.bs.modal', function () {
            self.close();
        }));

        this.renderConditions();
    },

    renderStatus: function(taskCardEl) {
        var taskStatusEl= taskCardEl.find('.task-status-lbl');
        this.taskStatusView = new TaskStatusView({model: this.model});
        taskStatusEl.append(this.taskStatusView.render());
    },

    renderCard: function() {
        var params = {
            description: this.model.get("description"),
            picture_id: this.model.get("picture_file_id"),
            createMode: this.createMode
        };
        var taskCardEl = $(this.template(params).trim());

        if (this.messageView == null) {
            this.messageView = new TaskMessageView({
                model: this.model,
                createMode: this.createMode,
                el: taskCardEl.find('#messageCont')
            });
        }
        this.renderStatus(taskCardEl);
        return taskCardEl;
    },

    renderConditions: function() {
        var self = this;
        self.conditionViews = [];

        self.$el.find('.task-conditions-cont').html('');

        self.model.get("conditions").each(function(condition) {
            if (condition.getType() == 'LOCATION') {
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

    close: function(){
        this.taskStatusView.remove();
        this.conditionViews.forEach(function (condition) {
            condition.remove();
        });
        this.removeTmpPicture();
        this.undelegateEvents();

        this.$el.removeData().unbind();

        this.remove();
        this.unbind();
        Backbone.View.prototype.remove.call(this);
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
        this.updatePicture();

        if (!this.model.isValid()) {
            this.saveBtn.hide();
            switch (this.model.validationError ) {
                case Task.prototype.erorrTypes.moreThan100Chars :
                    this.addInfoAlert('moreThan100Chars');
                    break;
                case Task.prototype.erorrTypes.emptyMessage :
                    this.messageView.setRedColorWhenError();
                    this.addInfoAlert('emptyMessage');
                    break;
            }
        } else {
            this.saveBtn.show();
        }
    },

    updatePicture: function () {
        var pictureFileId = this.model.get("picture_file_id");
        if (pictureFileId === undefined) {
            this.pictureEl.attr('src', app.url + "images/pictures.png");
        } else {
            this.pictureEl.attr('src', app.googleDriveImageUrl + this.model.get("picture_file_id"));
        }
    },

    changeDescriptionHandler: function() {
        this.saveBtn.show();
    },

    changeLOCATIONHandler: function() {
        this.saveBtn.show();
    },

    save: function() {
        if (!this.model.isValid()) {
            return false;
        }
        this.model.set("description", this.descriptionEditView.find("textarea").val());
        var result = this.model.save();
        this.saveBtn.hide();
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

    onSaveHandler: function(result) {
        if (result.status == "OK") {
            this.saveBtn.hide();
            if (this.createMode == true) {
                this.$el.modal("hide");
                var newTask = new Task(result.task);
                app.MainPanelView.taskPanelView.model.tasks.add(newTask);
            }
            this.tmpPicture = null;
            this.model.update(result.task,  {silent: true}); //TODO we won't needthis late
            this.renderConditions();
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

    saveNewPicture: function() {
        if(this.tmpPicture){
            this.model.set('picture_file_id', this.tmpPicture.pictureFileId);
            this.model.set('picture_name', this.tmpPicture.pictureName);
            this.model.save();
        }
        $('.picture-picker-block').collapse('toggle');
    },

    cancelChangePicture: function() {
        this.removeTmpPicture();
        this.setPlaceHolderText();
        $('#savePictureBtn').addClass('disabled');
        $('.picture-picker-block').collapse('toggle');
    },

    removeTmpPicture: function() {
        if (this.tmpPicture) {
            var pictureForRemove = new Picture({
                task_id: null,
                name: this.tmpPicture.pictureName,
                file_id: this.tmpPicture.pictureFileId
            });
            pictureForRemove.remove();
            this.tmpPicture = null;
            $('img#picture-preview').attr('src', '');
            $('#pictureUploadBtn').replaceWith($('#pictureUploadBtn').clone(true));

        }
    },

    uploadPictureHandler: function(event) {
        var self = this;
        function readURL(input) {
            // TODO move to separate function
            // and use this http://stackoverflow.com/questions/12281775/get-data-from-file-input-in-jquery
            if (!window.File || !window.FileReader || !window.FileList || !window.Blob) {
                alert('The File APIs are not fully supported in this browser.');
                return;
            }

            if (input.files && input.files[0]) {
                // max size 10 MB for picture
                if (input.files[0].size > 10485760) {
                    self.addInfoAlert('bigPicture');
                    return;
                }

                if (input.files[0].type != "image/jpeg" && input.files[0].type != "image/png") {
                    self.addInfoAlert('wrongPictureFormat');
                    return;
                }
                var reader = new FileReader();
                /*var fd = new FormData(document.getElementById("uploadTaskPicture"));
                fd.append("CustomField", "This is some extra data");

                $.ajax({
                    url: "/picture/upload",
                    type: "POST",
                    data: fd,
                    processData: false,  // tell jQuery not to process the data
                    contentType: 'application/x-www-form-urlencoded',   // tell jQuery not to set contentType
                    success: function(response){
                        console.log("Response was "  + response);
                    },
                    failure: function(result){
                        console.log("FAILED");
                        console.log(result);
                    }
                });*/
                reader.onload = function (e) {
                    self.addSpinerLoader();
                    $('#image').attr('src', e.target.result);
                    $.post('/picture/upload', {imageData:e.target.result}, function(data){
                        $('#savePictureBtn').removeClass('disabled');
                        dataJson = JSON.parse(data);
                        if (dataJson.status == "SUCCESS" && dataJson.picture_file_id) {
                            self.removeTmpPicture();
                            self.tmpPicture = {
                                pictureName : dataJson.picture_name,
                                pictureFileId : dataJson.picture_file_id,
                            }
                            $('img#picture-preview').attr('src', app.googleDriveImageUrl + dataJson.picture_file_id);
                            $('.picture-preview-cont').show();
                            $('.picture-placeholder').hide();
                            $('#savePictureBtn').removeClass('disabled');
                            self.setPlaceHolderText();
                        } else {
                            $('.picture-preview-con').hide();
                            $('.picture-placeholder').show();
                            self.setPlaceHolderText();
                        }
                        //recieve information back from php through the echo function(not required)

                    }).fail(function(data) {
                    });
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

        var inputElement = event.target
        readURL(inputElement);
    },


    onDownloadRefChanged : function () {
        var self = this;
        var imageUrl = $("#downloadRefInput").val();
        this.addSpinerLoader();
        $.post('/picture/download', {imageUrl:imageUrl}, function(data){
            dataJson = JSON.parse(data);
            if (dataJson.status == "SUCCESS" && dataJson.picture_file_id) {
                $('#savePictureBtn').removeClass('disabled');
                self.removeTmpPicture();
                self.tmpPicture = {
                    pictureName : dataJson.picture_name,
                    pictureFileId : dataJson.picture_file_id,
                }
                $('img#picture-preview').attr('src', app.googleDriveImageUrl + dataJson.picture_file_id);
                $('.picture-preview-cont').show();
                $('.picture-placeholder').hide();
                $('#savePictureBtn').removeClass('disabled');
                self.setPlaceHolderText();
            } else if (dataJson.code === "bad_url" || dataJson.code === "bad_image_format") {
                self.showErrorIconWithMessage(dataJson.message);
                $('.picture-preview-cont').hide();
                $('.picture-placeholder').show();
            } else {
                $('.picture-preview-cont').hide();
                $('.picture-placeholder').show();
                self.setPlaceHolderText();
            }
            //recieve information back from php through the echo function(not required)

        }).always(function(data) {

        });
    },

    addSpinerLoader: function() {
        $('#savePictureBtn').addClass('disabled');
        $('.picture-preview-cont').hide();
        $('.picture-placeholder').show();
        $('.picture-placeholder').html("<div class='loader'></div>");
    },

    removeSpinnerLoader: function() {

    },

    setPlaceHolderText: function() {
        $('.picture-placeholder').html("Picture is not selected");
    },

    showErrorIconWithMessage: function(message) {
        $('.picture-placeholder').html(
            "<div>" + message + "</div><div class='thumbsDownCont'><span class='glyphicon glyphicon-thumbs-down'></span></div>");
    }
});