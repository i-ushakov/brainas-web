<!-- 1: Load the Google Identity Toolkit helpers -->
<?php
use common\models\User;
use common\infrastructure\SingInLog;

//$pathToGITFolder = __DIR__ .'/../../identity-toolkit-php-master/';
//$pathToConfigFolder = __DIR__ .'/../../config/';
//set_include_path(get_include_path() . PATH_SEPARATOR . $pathToGITFolder . 'vendor/google/apiclient/src');
//require_once $pathToGITFolder . 'vendor/autoload.php';
//$gitkitClient = Gitkit_Client::createFromFile($pathToConfigFolder . 'gitkit-server-config.json');
//$gitkitUser = $gitkitClient->getUserInRequest();
//$accessToken = $gitkitClient->getTokenString();

if (!Yii::$app->user->isGuest) {
    \frontend\components\GoogleIdentityHelper::refreshUserAccessToken();
}
?>

<script type=text/javascript>
    function start() {
        gapi.load('auth2', function() {
            auth2 = gapi.auth2.init({
                client_id: '925705811320-cenbqg1fe5jb804116oefl78sbishnga.apps.googleusercontent.com',
                scope: 'email https://www.googleapis.com/auth/drive https://www.googleapis.com/auth/drive.appfolder'
            });
        });


    }

    function signInCallback(authResult) {
        if (authResult['code']) {

            // Hide the sign-in button now that the user is authorized, for example:
            $('#signinButton').attr('style', 'display: none');

            // Send the code to the server
            $.ajax({
                type: 'POST',
                url: 'https://brainas.com/site/sign-in',
                contentType: 'application/octet-stream; charset=utf-8',
                success: function(result) {
                    window.location.reload();
                    // Handle or verify the server response.
                },
                processData: false,
                data: authResult['code']
            });
        } else {
            // There was an error.
        }
    }

    function signOutCallback() {
            // Hide the sign-in button now that the user is authorized, for example:
            $('#signinButton').attr('style', 'display: none');

            // Send the code to the server
            $.ajax({
                type: 'POST',
                url: 'https://brainas.com/site/sign-out',
                contentType: 'application/octet-stream; charset=utf-8',
                success: function(result) {
                    window.location.reload();
                    // Handle or verify the server response.
                },
                processData: false
            });
    }

    $(document).ready(function() {
        $('.google-signin-btn').on('click', function () {
            auth2.grantOfflineAccess({'redirect_uri': 'postmessage'}).then(signInCallback);
            return false;
        });

        $('#signoutButton').click(function () {
            auth2.signOut().then(function () {
                console.log('User signed out.');
                signOutCallback();
            });
        });
    });


</script>




