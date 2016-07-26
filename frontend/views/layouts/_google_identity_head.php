<!-- 1: Load the Google Identity Toolkit helpers -->
<?php
use common\models\User;
use common\infrastructure\SingInLog;

if (!Yii::$app->user->isGuest) {
    \frontend\components\GoogleIdentityHelper::refreshUserAccessToken();
}
?>

<script type=text/javascript>
    var auth2;
    var checkAuthInterval;

    function start() {
        gapi.load('auth2', function() {
            auth2 = gapi.auth2.init({
                client_id: '925705811320-cenbqg1fe5jb804116oefl78sbishnga.apps.googleusercontent.com',
                scope: 'email https://www.googleapis.com/auth/drive https://www.googleapis.com/auth/drive.appfolder'
            });
            auth2.isSignedIn.listen(signinChanged);
        });
    }

    function signinChanged() {
        if (app.singedIn) {
            if (auth2.isSignedIn.get()) {
                startCheckingIsUserSignIn(auth2);
            } else {
                signOutCallback();
            }
        }
    }

    function startCheckingIsUserSignIn() {
        checkAuthInterval = setInterval(function() {
            if (!auth2.isSignedIn.get()) {
                signOutCallback();
            }
        }, 1000 * 7);
    }


    function stopCheckingIsUserSignIn() {
        clearInterval(checkAuthInterval);
    }

    function signInCallback(authResult) {
        if (authResult['code']) {
            // Hide the sign-in button now that the user is authorized, for example:
            $('#signinButton').attr('style', 'display: none');

            // Send the code to the server
            $.ajax({
                type: 'POST',
                url: '/site/sign-in',
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
            url: '/site/sign-out',
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




