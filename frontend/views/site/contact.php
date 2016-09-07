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
        <div id="contact-card" class="panel panel-default">
            <div class="panel-heading">Creator of Brain Assistant Project</div>
            <div class="panel-body">
                <div id="contact-card-right-part" class="col-md-3">
                    <div class="row">
                        <div id="kit-ushakov-img-cont">
                            <img id="kit-ushakov-img" src="../images/kit_ushakov.jpg" class="col-md-12"/>
                        </div>
                    </div>
                    <div class="row">
                        <div id="kit-ushakov-name-cont">
                            <div id="kit-ushakov-name">Kit Ushakov</div>
                        </div>
                    </div>
                </div>
                <div id="contact-card-left-part" class="col-md-9">
                    <ul class="nav nav-tabs">
                        <li role="presentation" class="active"><a href="#about-me-info-block">About me</a></li>
                        <li role="presentation"><a href="#contact-info-block">Cantact info</a></li>
                    </ul>
                    <div class="tab-content">
                        <div id="about-me-info-block" class="tab-pane fade in active">
                            Hi! My name is Kit Ushakov. I am a full-stack web developer with 7 years experience in web area and 10 years as a programmer at whole.
                            My skills in web development: Linux, Apache, PHP, MySQL, JavaScript/JQuery, HTML/CSS.<br/><br/>
                            I also have experience in developing applications for Android
                        </div>
                        <div id="contact-info-block" class="tab-pane fade">
                            <div class="row">
                                <div class="col-md-2">Web-site:</div>
                                <div class="col-md-2"><a href="http://www.kitushakov.com" target="_blank">www.kitushakov.com</a></div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">Facebook:</div>
                                <div class="col-md-2"><a href="https://www.facebook.com/kit.ushakov" target="_blank">kit.ushakov</a></div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">Email:</div>
                                <div class="col-md-2"><a href="mailto:kit.ushakoff@gmail.com">kit.ushakoff@gmail.com</a></div>
                            </div>
                        </div>
                    </div>
                    <div id="kit_logo_cont"><img src="../images/kit_logo_150.jpg"></div>
                </div>
        </div>
    </div>
    <div class="col-md-1"></div>
</div>


    <script>
        $(document).ready(function(){
            $(".nav-tabs a").click(function(){
                $(this).tab('show');
            });
        });
        </script>