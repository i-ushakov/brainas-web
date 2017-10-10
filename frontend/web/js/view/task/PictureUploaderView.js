/**
 * Created by kit on 8/21/2017.
 */

var app = app || {};

PictureUploaderView = Backbone.View.extend({


    messageLable : null,
    messageEditCont : null,
    messageTextArea : null,
    cancelEditIcon : null,

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
            'saveNewPicture'
        );
    },

    render: function () {

        this.$el.html(this.template());

        // view elements
        this.$saveBtn = this.$el.find('#savePictureBtn');

        // set unique id
        this.$el.attr('id','taskPictureUploaderCont_' + this.model.get('id'))
    },

    cancelChangePicture: function() {
        this.parent.removeTmpPicture();
        this.parent.setPlaceHolderText();
        this.$saveBtn.addClass('disabled');
        this.$el.collapse('toggle');
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

        var inputElement = event.target;
        readURL(inputElement);
    },

    addSpinerLoader: function() {
        $('#savePictureBtn').addClass('disabled');
        $('.picture-preview-cont').hide();
        $('.picture-placeholder').show();
        $('.picture-placeholder').html("<div class='loader'></div>");
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

    setPlaceHolderText: function() {
        $('.picture-placeholder').html("Picture is not selected");
    },

    removeSpinnerLoader: function() {

    },

    saveNewPicture: function() {
        if(this.tmpPicture){
            this.model.set('picture_file_id', this.tmpPicture.pictureFileId);
            this.model.set('picture_name', this.tmpPicture.pictureName);
            this.model.save();
        }
        $('.picture-picker-block').collapse('toggle');
    },

    destroy: function () {
        this.removeTmpPicture();

        this.undelegateEvents();
        this.$el.removeData().unbind();
        this.remove();
        Backbone.View.prototype.remove.call(this);
    }
});



