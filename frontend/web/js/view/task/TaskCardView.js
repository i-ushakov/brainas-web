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

    // inner views
    messageView: null,
    descriptionView: null,
    conditionsPanelView: null,
    taskStatusView : null,
    pictureView: null,
    pictureUploaderView: null,

    events: {
        'click #save-changes-btn': 'save'
    },

    template: _.template( $('#task-card-modal-template').html()),

    initialize: function (options) {
        _.bindAll(this,
            'onSaveHandler',
            'changeTaskHandler',
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
        this.saveBtn = this.$el.find('#save-changes-btn');

        if (this.createMode == true) {
            this.model.set("id", null);
            this.saveBtn.text("Create");
        }

        //this.listenTo(this.model, 'change', this.renderContent); //TODO render internal part of window updatable

        this.model.on({"change": this.changeTaskHandler});
        this.model.on({"save": this.onSaveHandler});
    },

    render: function() {
        var self = this;
        var modal = this.renderCard();
        this.setElement(modal.modal('show'));
        $(modal.on('hidden.bs.modal', function () {
            self.close();
        }));
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

        // picture view
        this.pictureView = new PictureView({
                model: this.model,
                el: taskCardEl.find('#taskPictureCont')
            }
        );

        // message
        if (this.messageView == null) {// TODO maybe we don't need this IF
            this.messageView = new TaskMessageView({
                model: this.model,
                createMode: this.createMode,
                el: taskCardEl.find('#messageCont')
            });
        }

        // picture uploader view
        this.pictureUploaderView = new PictureUploaderView({
                model: this.model,
                el: taskCardEl.find('#pictureUploaderCont'),
                parent: this
            }
        );

        // description
        this.descriptionView = new TaskDescriptionView({
            model: this.model,
            el: taskCardEl.find('#taskDescriptionCont')
        });

        //conditions panel
        this.conditionsPanelView = new ConditionsPanelView({
            model: this.model,
            el: taskCardEl.find('#taskConditionsPanel')
        });

        this.renderStatus(taskCardEl);
        return taskCardEl;
    },

    close: function() {
        this.model.set("preventUpdateFromServer", false);

        this.undelegateEvents();

        this.$el.removeData().unbind();

        this.remove();
        this.unbind();
        Backbone.View.prototype.remove.call(this);

        //remove iternal Views
        this.taskStatusView.destroy();
        this.pictureView.destroy();
        this.messageView.destroy();
        this.descriptionView.destroy();
        this.conditionsPanelView.destroy();
    },

    changeTaskHandler: function() {
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

    save: function() {
        if (!this.model.isValid()) {
            return false;
        }
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
            this.conditionsPanelView.render();
        }
    },

    remove: function(){
        this.model.set("preventUpdateFromServer", false);
    }
});