<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Copy and paste here the "Widget javascript" you downloaded from Developer Console as gitkit-widget.html -->

<script type="text/javascript" src="//www.gstatic.com/authtoolkit/js/gitkit.js"></script>
<link type="text/css" rel="stylesheet" href="//www.gstatic.com/authtoolkit/css/gitkit.css" />
<script type="text/javascript">
    var config = {
        apiKey: 'AIzaSyALoOSAt19qKApEaQxnDEuHxsn9f7Kn46E',
        signInSuccessUrl: '/',
        signInOptions: ["google"],
        oobActionUrl: '/',
        siteName: 'Brain Assistant',

        // Optional - function called after sign in completes and before
        // redirecting to signInSuccessUrl. Return false to disable
        // redirect.
        // callbacks: {
        //  signInSuccess: function(tokenString, accountInfo,
        //    opt_signInSuccessUrl) {
        //      return true;
        //    }
        // },

        // Optional - key for query parameter that overrides
        // signInSuccessUrl value (default: 'signInSuccessUrl')
        // queryParameterForSignInSuccessUrl: 'url'

        // Optional - URL of site ToS (linked and req. consent for signup)
        // tosUrl: 'http://example.com/terms_of_service',

        // Optional - Cookie name (default: gtoken)
        //            NOTE: Also needs to be added to config of the �page with
        //                  sign in button�. See above
        // cookieName: �example_cookie�,

        // Optional - UI configuration for accountchooser.com
        /*acUiConfig: {
         title: 'Sign in to example.com',
         favicon: 'http://example.com/favicon.ico',
         branding: 'http://example.com/account_choooser_branding'
         },
         */

        // Optional - Function to send ajax POST requests to your Recover URL
        //            Intended for CSRF protection, see Advanced Topics
        //      url - URL to send the POST request to
        //     data - Raw data to include as the body of the request
        //completed - Function to call with the object that you parse from
        //            the JSON response text. {} if no response
        /*ajaxSender: function(url, data, completed) {
         },
         */
    };
    // The HTTP POST body should be escaped by the server to prevent XSS
    window.google.identitytoolkit.start(
        '#gitkitWidgetDiv', // accepts any CSS selector
        config,
        'JAVASCRIPT_ESCAPED_POST_BODY');
</script>
<div class="site-login">
    <div id="gitkitWidgetDiv"></div>
</div>
