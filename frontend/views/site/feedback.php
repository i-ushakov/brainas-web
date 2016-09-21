<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\ContactForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;

$this->title = 'Contact';
$this->params['breadcrumbs'][] = $this->title;
?>
<div id="contact-card-cont" class="col-md-12">
    <div class="col-md-1"></div>
    <div class="col-md-10">
        <div id="feedback-form" class="panel panel-default">
            <div class="panel-heading">Form for feedback (Please report about bugs and give your suggestions here)</div>
            <div class="panel-body">
                <input type="text" class="form-control" id="subject_of_feedback" placeholder="Subject">
                <br/>
                <textarea class="form-control" rows="10" id="message_of_feedback" placeholder="Message"></textarea>
                <br/>
                <div id="" class="input-group">
                    <span class="input-group-addon" id="sizing-addon1">Your contact email:</span>
                    <input type="email" class="form-control" id="contact_email" placeholder="yourcontact@mail.com" value="<?= $this->params['userEmail']?>">
                </div>
                <br/>
                <div><button type="submit" class="btn btn-default" id="send_feedback_btn">Send feedback</button></div>
            </div>
        </div>
    <div class="col-md-1"></div>
    </div>
</div>


<script>
    $(document).ready(function() {
        $("#send_feedback_btn").click(function () {
            $.ajax({
                type: "POST",
                url: '/site/feedback',
                data: {
                    subject : $('#subject_of_feedback').val(),
                    message : $('#message_of_feedback').val(),
                    contactemail : $('#contact_email').val()
                },
                success: function (result) {
                    if (result.status == 'success') {
                        $('#feedback-form .panel-body').html('Your message was successfully sent. Thank you for cooperation.')
                    } else {
                        $('#feedback-form .panel-body').html('Sending has failed.')
                    }
                },
                dataType: 'json'
            });
        });
    });
</script>