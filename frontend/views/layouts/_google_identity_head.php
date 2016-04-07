<!-- 1: Load the Google Identity Toolkit helpers -->
<?php
use common\models\User;
use common\infrastructure\SingInLog;

$pathToGITFolder = __DIR__ .'/../../identity-toolkit-php-master/';
$pathToConfigFolder = __DIR__ .'/../../config/';
set_include_path(get_include_path() . PATH_SEPARATOR . $pathToGITFolder . 'vendor/google/apiclient/src');
require_once $pathToGITFolder . 'vendor/autoload.php';
$gitkitClient = Gitkit_Client::createFromFile($pathToConfigFolder . 'gitkit-server-config.json');
$gitkitUser = $gitkitClient->getUserInRequest();

if (is_null($gitkitUser)) {
    if (!Yii::$app->user->isGuest) {
        Yii::$app->user->logout();
    }
} elseif (Yii::$app->user->isGuest) {
    $userEmail = $gitkitUser->getEmail();
    $user = User::findOne(['username' => $userEmail]);
    if (!is_null($user)) {
        Yii::$app->user->login($user);
    } else {
        $user = new User();
        $user->username = $userEmail;
        $user->email = $userEmail;
        $user->save();
    }
    // Logging user entering
    $singInLog = new SingInLog();
    $singInLog->user_id = $user->id;
    $singInLog->datetime = date("Y-m-d H:i:s");
    $singInLog->save();
}
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
            /*dropDownMenu: [
                {
                    'label': 'Sign out',
                    'handler': function() {google.identitytoolkit.signOut();}
                },
                /*{
                    'label': 'Check Configuration',
                    'url': '/config'
                },*/
                //{
                   // 'label': 'Manage Account',
                   // 'handler': function() {google.identitytoolkit.manageAccount();}
                //},]

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
<!-- End modification -->



