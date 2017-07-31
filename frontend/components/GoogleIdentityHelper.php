<?php
namespace frontend\components;
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 7/4/2016
 * Time: 12:45 PM
 */

use common\components\BAException;
use \Yii;
use common\models\User;
use yii\debug\models\search\Log;

class GoogleIdentityHelper {
    const APP_NAME = "Brain Assistant app";
    const CLIENT_SECRET_PATH = "/var/www/brainas.net/backend/config/client_secret_925705811320-cenbqg1fe5jb804116oefl78sbishnga.apps.googleusercontent.com.json";
    const PROBLEM_WITH_REFRESH_TOKEN_MSG = "Access token expired and we cannot to refresh it (may be refresh_token is broken)";
    const NO_REFRESH_TOKEN_MSG = "Access token expired havn't refresh_token";

    static public function getGoogleClient() {
        $client = new \Google_Client();
        $client->setApplicationName(self::APP_NAME);
        $client->setScopes(implode(' ', array(
            \Google_Service_Drive::DRIVE_APPDATA,
            \Google_Service_Drive::DRIVE_METADATA,
            \Google_Service_Drive::DRIVE_FILE,
            \Google_Service_Drive::DRIVE
        )));
        $client->setAuthConfigFile(self::CLIENT_SECRET_PATH);
        $client->setDeveloperKey(Yii::$app->params['ServiceAccountKey']);
        $client->setRedirectUri("postmessage");
        $client->setAccessType('offline');
        return $client;
    }

    static public function getGoogleClientWithToken(User $user) {
        $client = self::getGoogleClient();
        $client->setAccessToken($user->access_token);
        if ($client->isAccessTokenExpired()) {
            if (isset($user->refreshToken->refresh_token)) {
                $refreshedAccessToken = $client->refreshToken($user->refreshToken->refresh_token);
                try {
                    $client->setAccessToken($refreshedAccessToken);
                } catch (\InvalidArgumentException $e) {
                    throw new BAException(self::PROBLEM_WITH_REFRESH_TOKEN_MSG, BAException::INVALID_PARAM_EXCODE, $e);
                }
                $user->access_token = json_encode($client->getAccessToken());
                $user->refresh_token = $refreshedAccessToken['refresh_token'];
                $user->save();
            } else {
                throw new BAException(self::NO_REFRESH_TOKEN_MSG, BAException::NOT_ENOUGH_DATA, null);
            }
        }
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
}