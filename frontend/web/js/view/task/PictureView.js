/**
 * Created by kit on 8/21/2017.
 */

var app = app || {};

PictureView = Backbone.View.extend({


    messageLable : null,
    messageEditCont : null,
    messageTextArea : null,
    cancelEditIcon : null,

    events : {
        'mouseenter' : 'showChangePictureBtn',
        'mouseleave' : 'hideChangePictureBtn',
        'click #cancelPictureBtn' : 'cancelChangePicture'
    },

    template: _.template( $('#task-card-picture-template').html()),

    initialize: function () {
        _.bindAll(this,
            'showChangePictureBtn',
            'hideChangePictureBtn',
            'cancelChangePicture',
            'updatePicture'
        );

        this.render();
    },

    render: function () {
        var params = {
            picture_id: this.model.get("picture_file_id"),
        };

        this.$el.html(this.template(params));

        // view elements
        this.$changePictureBtn = this.$el.find('.change-picture-btn');
        this.pictureEl = this.$el.find('img');

        // set unique id
        this.$el.attr('id','taskPictureCont_' + this.model.get('id'));

        // listening chsnges
        this.model.on({"change:picture_file_id": this.updatePicture});
    },

    showChangePictureBtn: function() {
        this.$changePictureBtn.show();
    },

    hideChangePictureBtn: function() {
        this.$changePictureBtn.hide();
    },

    cancelChangePicture: function() {
        this.removeTmpPicture();
        this.setPlaceHolderText();
        $('#savePictureBtn').addClass('disabled');
        $('.picture-picker-block').collapse('toggle');
    },

    setPlaceHolderText: function() {
        $('.picture-placeholder').html("Picture is not selected");
    },

    updatePicture: function () {
        var pictureFileId = this.model.get("picture_file_id");
        if (pictureFileId === undefined) {
            this.pictureEl.attr('src', app.url + "images/pictures.png");
        } else {
            this.pictureEl.attr('src', app.googleDriveImageUrl + this.model.get("picture_file_id"));
        }
    },

    destroy: function () {
        this.undelegateEvents();
        this.$el.removeData().unbind();
        this.remove();
        Backbone.View.prototype.remove.call(this);
    }
});



