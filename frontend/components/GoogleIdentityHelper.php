<?php
namespace frontend\components;
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 7/4/2016
 * Time: 12:45 PM
 */

use \Yii;
use common\models\User;

class GoogleIdentityHelper {
    static public $APP_NAME = "Brain Assistant app";
    static public $CLIENT_SECRET_PATH = "/var/www/brainas.net/backend/config/client_secret_925705811320-cenbqg1fe5jb804116oefl78sbishnga.apps.googleusercontent.com.json";

    static public function getGoogleClient() {
        $client = new \Google_Client();
        $client->setApplicationName(self::$APP_NAME);
        $client->setScopes(implode(' ', array(
            \Google_Service_Drive::DRIVE_APPDATA,
            \Google_Service_Drive::DRIVE_METADATA,
            \Google_Service_Drive::DRIVE_FILE,
            \Google_Service_Drive::DRIVE
        )));
        $client->setAuthConfigFile(self::$CLIENT_SECRET_PATH);
        $client->setDeveloperKey(Yii::$app->params['ServiceAccountKey']);
        $client->setRedirectUri("postmessage");
        $client->setAccessType('offline');
        return $client;
    }

    static public function authenticationOfUser($accessToken) {
        if ($accessToken != null) {
            $client = self::getGoogleClient();
            $client->setAccessToken($accessToken);
            if ($client->isAccessTokenExpired()) {
                    return null;
            }
            $data = $client->verifyIdToken();


            $userEmail = $data['email'];
            if (isset($userEmail)) {
                return $userEmail;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    static public function loginUserInYii($userEmail, $accessToken) {
        if ((!Yii::$app->user->isGuest)) {
            $user = \Yii::$app->user->identity;
        } else {
            $user = User::findOne(['username' => $userEmail]);
            if (!is_null($user)) {
                Yii::$app->user->login($user);
            } else {
                $user = new User();
                $user->username = $userEmail;
                $user->email = $userEmail;
                $user->save();
            }

            Yii::$app->user->login($user);
            self::saveAccessToken($user, $accessToken);
        }
        return $user;
    }

    static public function logoutUserInYii($user) {
        \Yii::$app->session->remove('googleAccessToken');
        $user->access_token = null;
        $user->save();
    }

    static public function saveAccessToken($user, $accessToken) {
        \Yii::$app->session->set('googleAccessToken', json_encode($accessToken));
        $user->access_token = json_encode($accessToken);
        if (isset($accessToken['refresh_token'])) {
            $user->refresh_token = $accessToken['refresh_token'];
        }
        $user->save();
    }

    static public function refreshUserAccessToken() {
        if (Yii::$app->user->isGuest) {
            return;
        }
        $user = Yii::$app->user->identity;
        $client = self::getGoogleClient();
        $accessToken = $user->access_token;
        $client->setAccessToken($accessToken);
        if ($client->isAccessTokenExpired() && isset($user->refresh_token)) {
            $client->refreshToken($user->refresh_token);
            $accessToken = $client->getAccessToken();
            $user->access_token = json_encode($accessToken);
            $user->save();
        }
    }

    static private function actualizeRefreshToken($accessToken) {
        if (isset($accessToken['refresh_token'])) {
            self::saveRefreshTokenInDB($accessToken['refresh_token']);
            return $accessToken;
        } else {
            $accessToken['refresh_token'] = self::retrieveRefreshTokenFromDB($accessToken['refresh_token']);
            return $accessToken;
        }
    }
}