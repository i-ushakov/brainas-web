/**
 * Created by kit on 8/21/2017.
 */

var app = app || {};

PictureUploaderView = Backbone.View.extend({

    tmpPicture : null,

    events : {
        'click #cancelPictureBtn' : 'cancelChangePicture',
        'input #downloadRefInput' : 'onDownloadRefChanged',
        'change #pictureUploadBtn' : 'uploadPictureHandler',
        'click #savePictureBtn' : 'saveNewPicture'
    },

    template: _.template( $('#task-card-uploader-template').html()),

    initialize: function (options) {

        this.parent = options.parent;

        this.render();

        _.bindAll(this,
            'cancelChangePicture',
            'onDownloadRefChanged',
            'uploadPictureHandler',
            'saveNewPicture',
            'pictureUploadCallback'
        );
    },

    render: function () {

        this.$el.html(this.template());

        // view elements
        this.$saveBtn = this.$el.find('#savePictureBtn');
        this.$previewImage = this.$el.find('img#picture-preview');
        this.$previewCont = this.$el.find('.picture-preview-cont');
        this.$picturePlaceholder = this.$el.find('.picture-placeholder');

        // set unique id
        this.$el.attr('id','taskPictureUploaderCont_' + this.model.get('id'))
    },

    cancelChangePicture: function() {
        this.removeTmpPicture();
        this.setPlaceHolderText();
        this.$saveBtn.addClass('disabled');
        this.$el.collapse('toggle');
    },

    onDownloadRefChanged : function () {
        var imageUrl = $("#downloadRefInput").val();
        this.addSpinerLoader();
        PictureHelper.downloadByUrl(imageUrl, this.pictureUploadCallback);
    },

    uploadPictureHandler: function(event) {
        this.addSpinerLoader();

        var uploadedPicture = event.target;

        var result = PictureHelper.upload(uploadedPicture, this.pictureUploadCallback);

        if (result == PictureHelper.TOO_BIG_SIZE_ERORR) {
            this.addInfoAlert('bigPicture');
        } else if (result == PictureHelper.WRONG_PICTURE_FORMAT) {
            this.addInfoAlert('wrongPictureFormat');
        }
    },

    pictureUploadCallback : function(data) {
        var self = this;
        var dataJson = JSON.parse(data);
        if (dataJson.status == "SUCCESS" && dataJson.picture_file_id) {
            self.$saveBtn.removeClass('disabled');
            self.removeTmpPicture();
            self.tmpPicture = {
                pictureName : dataJson.picture_name,
                pictureFileId : dataJson.picture_file_id,
            };
            self.$previewImage.attr('src', app.googleDriveImageUrl + dataJson.picture_file_id);
            self.$previewCont.show();
            self.$picturePlaceholder.hide();
            self.$saveBtn.removeClass('disabled');
            self.setPlaceHolderText();
        } else if (dataJson.code === "bad_url" || dataJson.code === "bad_image_format") {
            self.showErrorIconWithMessage(dataJson.message);
            self.$previewCont.hide();
            self.$picturePlaceholder.show();
        } else {
            this.$previewCont.hide();
            self.$picturePlaceholder.show();
            self.setPlaceHolderText();
        }
    },

    addSpinerLoader: function() {
        this.$saveBtn.addClass('disabled');
        this.$previewCont.hide();
        this.$picturePlaceholder.show();
        this.$picturePlaceholder.html("<div class='loader'></div>");
    },

    removeTmpPicture: function() {
        if (this.tmpPicture) {
            PictureHelper.remove(this.tmpPicture);
            this.tmpPicture = null;
            this.$previewImage.attr('src', '');
            $('#pictureUploadBtn').replaceWith($('#pictureUploadBtn').clone(true));
        }
    },

    setPlaceHolderText: function() {
        this.$picturePlaceholder.html("Picture is not selected");
    },

    saveNewPicture: function() {
        if(this.tmpPicture){
            this.model.set('picture_file_id', this.tmpPicture.pictureFileId);
            this.model.set('picture_name', this.tmpPicture.pictureName);
            this.model.save();
        }
        $('.picture-picker-block').collapse('toggle');
    },

    showErrorIconWithMessage: function(message) {
        this.$picturePlaceholder.html(
            "<div>" + message + "</div><div class='thumbsDownCont'><span class='glyphicon glyphicon-thumbs-down'></span></div>");
    },

    destroy: function () {
        this.removeTmpPicture();
        this.undelegateEvents();
        this.$el.removeData().unbind();
        this.remove();
        Backbone.View.prototype.remove.call(this);
    }
});



