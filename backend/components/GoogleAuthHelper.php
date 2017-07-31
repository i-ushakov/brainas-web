<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 6/7/2016
 * Time: 12:38 PM
 */

namespace backend\components;

use Yii;
use backend\components\Factory\GoogleClientFactory;
use common\models\User;


/**
 * Class GoogleAuthHelper
 *
 * Have a bundle of helpers static methods to work with Google Identity Toolkit
 *
 * @package backend\components
 */
class GoogleAuthHelper {

    /**
     * Create Google Client object and pass to it the access token
     *
     * @param $accessToken
     * @return \Google_Client
     */
    static public function getClientWithToken($accessToken) {
        $client = GoogleClientFactory::create();
        $client->setAccessToken($accessToken);
        if ($client->isAccessTokenExpired() && isset($accessToken['refresh_token'])) {
            $accessToken = $client->refreshToken($accessToken['refresh_token']);
            $client->setAccessToken($accessToken);
        }
        return $client;
    }

    /**
     * Exchanging the access code with token
     * @param $code
     * @return array|string
     */
    static public  function getClientWithTokenByCode($code) {
        $client = GoogleClientFactory::create();
        if ($code != null) {
            $client->authenticate($code);
            return $client;
        } else {
            throw new HttpException(471 ,'Code wasn\'t sent');
        }
    }

    /**
     * Exchanging the access token on the internal information (like user's id, email)
     * @param $token
     * @return array
     */
    static public function verifyUserAccess($token) {
        $authInfo = array();
        $client = GoogleClientFactory::create();
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

    /**
     * Saving refresh token if exist in array,
     * or retrieve from database if not
     *
     * @param $token
     * @param $userId
     *
     * @return mixed
     */
    static public function actualizeRefreshToken($token, $userId) {
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

    /**
     * Get user id by email
     *
     * @param $userEmail
     *
     * @return int
     */
    static public function getUserIdByEmail($userEmail) {
        $user = User::findOne(['username' => $userEmail]);
        if (!empty($user)) {
            return $user->id;
        } else {
            $user = new User();
            $user->username = $userEmail;
            $user->email = $userEmail;
            $user->auth_key = "";
            $user->password_hash = "";
            $user->save();
            return $user->id;
        }
    }

    /**
     * Save refresh token in DB
     *
     * @param $refreshToken
     * @param $userId
     */
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

    /**
     * Get refresh token from DB
     *
     * @param int $userId
     * @return string
     */
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