<!-- 1: Load the Google Identity Toolkit helpers -->
<?php
$pathToGITFolder = __DIR__ .'/../../identity-toolkit-php-master/';
$pathToConfigFolder = __DIR__ .'/../../config/';
set_include_path(get_include_path() . PATH_SEPARATOR . $pathToGITFolder . 'vendor/google/apiclient/src');
require_once $pathToGITFolder . 'vendor/autoload.php';

$gitkitClient = Gitkit_Client::createFromFile($pathToConfigFolder . 'gitkit-server-config.json');
$gitkitUser = $gitkitClient->getUserInRequest();
?>

<!-- Begin custom code copied from Developer Console -->
<!-- Note: this is just an example. The html you download from Developer Console will be tailored for your site -->
<script type="text/javascript" src="//www.gstatic.com/authtoolkit/js/gitkit.js"></script>
<link type=text/css rel=stylesheet href="//www.gstatic.com/authtoolkit/css/gitkit.css" />
<script type=text/javascript>
    window.google.identitytoolkit.signInButton(
        '#navbar', // accepts any CSS selector
        {
            widgetUrl: "/site/login",
            signOutUrl: "/",
            // Optional - Begin the sign-in flow in a popup window
            //popupMode: true,

            // Optional - Begin the sign-in flow immediately on page load.
            //            Note that if this is true, popupMode param is ignored
            //loginFirst: true,

            // Optional - Cookie name (default: gtoken)
            //            NOTE: Also needs to be added to config of ‘widget
            //                  page’. See below
            //cookieName: ‘example_cookie’,
        }
    );
</script>
<!-- End custom code copied from Developer Console -->


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
        //            NOTE: Also needs to be added to config of the ‘page with
        //                  sign in button’. See above
        // cookieName: ‘example_cookie’,

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
<!-- End modification -->



