/**
 * Created by Kit Ushakov on 7/12/2016.
 */

var app = app || {};

var PictureHelper = {};

PictureHelper.urlRemove = app.url + 'picture/remove/';
PictureHelper.MAX_UPLOAD_FILE_SIZE = 10485760;
PictureHelper.TOO_BIG_SIZE_ERORR = "TOO_BIG_SIZE_ERORR";
PictureHelper.WRONG_PICTURE_FORMAT = "WRONG_PICTURE_FORMAT";

PictureHelper.remove = function (tmpPicture) {
    var data = {
        picture: JSON.stringify(tmpPicture)
    };

    $.ajax({
        type: "POST",
        url: PictureHelper.urlRemove,
        data: data,
        success: function(result) {
            if (result.status == "OK") {
                return true;
            } else if (result.status == "FAILED" && result.type == 'must_be_signed_in'){ // if was logged out on Yii2 side
                alert("The operation has not been successful! Please repeat your actions after page reloading");
                location.reload();
            }
        },
        dataType: 'json'
    });
};

PictureHelper.upload = function (input, callback) {
    if (!window.File || !window.FileReader || !window.FileList || !window.Blob) {
        alert('The File APIs are not fully supported in this browser.');
        return;
    }

    if (input.files && input.files[0]) {
        // max size 10 MB for picture
        if (input.files[0].size > PictureHelper.MAX_UPLOAD_FILE_SIZE) {
            return PictureHelper.TOO_BIG_SIZE_ERORR;
        }

        if (input.files[0].type != "image/jpeg" && input.files[0].type != "image/png") {
            return PictureHelper.WRONG_PICTURE_FORMAT;
        }
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#image').attr('src', e.target.result);
            $.post('/picture/upload', {imageData:e.target.result}, function(data){
                callback(data);
            }).fail(function(data) {});
        };

        reader.readAsDataURL(input.files[0]);
    }
};

PictureHelper.downloadByUrl = function (imageUrl, callback) {
    $.post('/picture/download', {imageUrl:imageUrl}, function(data) {
            callback(data);
    }).always(function(data) {});
};
