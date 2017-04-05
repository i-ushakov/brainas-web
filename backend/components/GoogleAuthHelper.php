<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 6/7/2016
 * Time: 12:38 PM
 */

namespace backend\components;

use Google_Client;
use Yii;
use common\models\User;


class GoogleAuthHelper {
    public static $jsonGoogleClientConfig = "/var/www/brainas.net/backend/config/client_secret_925705811320-cenbqg1fe5jb804116oefl78sbishnga.apps.googleusercontent.com.json";

    static public function getGoogleClient() {
        $client = new Google_Client();
        $client->setAuthConfigFile(self::$jsonGoogleClientConfig);
        //$client->setRedirectUri("http://brainas.net/backend/web/connection/");
        $client->setRedirectUri("http://brainas.net/site/login");
        $client->setScopes("https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/drive.appdata");
        $client->setAccessType('offline'); //online
        //$client->setApprovalPrompt('force');

        return $client;
    }

    static public function getGoogleClientWithToken($accessToken) {
        $client = self::getGoogleClient();
        $client->setAccessToken($accessToken);
        if ($client->isAccessTokenExpired() && isset($accessToken[refresh_token])) {
            $accessToken = $client->refreshToken($accessToken[refresh_token]);
            $client->setAccessToken($accessToken);
        }
        return $client;
    }

    static public  function getAccessTokenByCode($code) {
        $client = self::getGoogleClient();
        if ($code != null) {
            $client->authenticate($code);
            $token = $client->getAccessToken();
        } else {
            throw new HttpException(471 ,'Code wasn\'t sent');
        }

        // Get user's date (email) and user id in our system
        $data = $client->verifyIdToken();
        $userEmail = $data['email'];
        $userId = self::getUserIdByEmail($userEmail);
        $token = self::actualizeRefreshToken($token, $userId);
        return $token;
    }

    static public function verifyUserAccess($token) {
        $authInfo = array();
        // Create Google client and get accessToken
        $client = GoogleAuthHelper::getGoogleClient();
        $client->setAccessToken($token);
        if ($client->isAccessTokenExpired()) {
            if (isset($token['refresh_token'])) {
                $token = $client->refreshToken($token['refresh_token']);
                $client->setAccessToken($token);
            } else {
                throw new InvalidArgumentException();
            }
        }

        // Get user's date (email) and user id in our system
        $data = $client->verifyIdToken();
        $userEmail = $data['email'];
        $userId = self::getUserIdByEmail($userEmail);

        $token = self::actualizeRefreshToken($token, $userId);
        $authInfo['userId'] = $userId;
        $authInfo['userEmail'] = $data['email'];
        $authInfo['token'] = $token;
        return $authInfo;
    }

    /*
     *  Save refresh token if exist or retrieve from database and send to client
     */
    static private function actualizeRefreshToken($token, $userId) {
        if (isset($token['refresh_token'])) {
            self::saveRefreshToken($token['refresh_token'], $userId);
        } else {
            $refreshToken = self::getRefreshToken($userId);
            if ($refreshToken != null) {
                $token['refresh_token'] = $refreshToken;
            }
        }
        return $token;
    }

    static private function getUserIdByEmail($userEmail) {
        $user = User::findOne(['username' => $userEmail]);
        if (!empty($user)) {
            return $user->id;
        } else {
            $user = new User();
            $user->username = $userEmail;
            $user->email = $userEmail;
            $user->save();
            return $user->id;
        }
    }

    static private function saveRefreshToken($refreshToken, $userId) {
        $params = [
            ':user_id' => $userId,
            ':refresh_token' => $refreshToken,
        ];

        Yii::$app->db->createCommand('
                INSERT INTO refresh_tokens (user_id, refresh_token)
                VALUES(:user_id, :refresh_token) ON DUPLICATE KEY UPDATE
                user_id=:user_id, refresh_token=:refresh_token
            ')
            ->bindValues($params)
            ->execute();
        return;
    }

    static private function getRefreshToken($userId) {
        $refreshToken = null;
        $params = [':user_id' => $userId];
        $r = Yii::$app->db->createCommand('SELECT * FROM refresh_tokens WHERE user_id=:user_id')
            ->bindValues($params)
            ->queryOne();

        if (isset($r['refresh_token'])) {
            $refreshToken = $r['refresh_token'];
        }

        return $refreshToken;
    }


}