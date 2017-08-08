<?php
namespace frontend\components;
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 7/4/2016
 * Time: 12:45 PM
 */

use common\components\BAException;
use common\models\User;
use frontend\components\Factory\GoogleClientFactory;

use \Yii;

/**
 * Class GoogleIdentityHelper
 * Helper class to work with Google Identity Toolkit
 *
 * @package frontend\components
 */
class GoogleIdentityHelper
{
    const PROBLEM_WITH_REFRESH_TOKEN_MSG = "Access token expired and we cannot to refresh it (may be refresh_token is broken)";
    const NO_REFRESH_TOKEN_MSG = "Access token expired havn't refresh_token";
    const AUTH_CODE_MUSTNT_BE_EMPTY = "Auth code mustn't to be empty";

    protected $client;

    public function __construct(\Google_Client $client)
    {
        $this->client = $client;
    }

    /**
     * Getting Google Client and set access token to it
     *
     * @param User $user
     * @return \Google_Client
     * @throws BAException
     */
    public function getGoogleClientWithToken(User $user)
    {

        $this->client->setAccessToken($user->access_token);
        if ($this->client->isAccessTokenExpired()) {
            if (isset($user->refreshToken->refresh_token)) {
                $refreshedAccessToken = $this->client->refreshToken($user->refreshToken->refresh_token);
                try {
                    $this->client->setAccessToken($refreshedAccessToken);
                } catch (\InvalidArgumentException $e) {
                    throw new BAException(self::PROBLEM_WITH_REFRESH_TOKEN_MSG, BAException::INVALID_PARAM_EXCODE, $e);
                }
                $user->access_token = json_encode($this->client->getAccessToken());
                $user->refresh_token = $refreshedAccessToken['refresh_token'];
                $user->save();
            } else {
                throw new BAException(self::NO_REFRESH_TOKEN_MSG, BAException::NOT_ENOUGH_DATA, null);
            }
        }
        return $this->client;
    }

    /**
     * The method get Google Client and retrieve user email
     *
     * @param $clientWithToken \Google_Client
     * @return null
     */
    public function retrieveUserEmail($clientWithToken)
    {
        $data = $clientWithToken->verifyIdToken();
        $userEmail = $data['email'];
        if (isset($userEmail)) {
            return $userEmail;
        } else {
            return null;
        }
    }

    /**
     * After user went through Google Identity and got authorization we have to log it in into Yii.
     * If the user with this email not in the database yet we have to create the new one.
     *
     * @param $userEmail
     * @param $accessToken
     * @return User|null|\yii\web\IdentityInterface|static
     */
    public function loginUserInYii($userEmail, $accessToken)
    {
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
            $this->saveAccessToken($user, $accessToken);
        }
        return $user;
    }

    /**
     * Just logout user from Yii
     * @param $user
     */
    public function logoutUserInYii($user)
    {
        \Yii::$app->session->remove('googleAccessToken');
        $user->access_token = null;
        $user->save();
    }

    /**
     * The app store access tokens of users on server side
     * This sensitive information because access token contains refresh token that user gets only once
     *
     * @param $user
     * @param $accessToken
     */
    public function saveAccessToken($user, $accessToken)
    {
        \Yii::$app->session->set('googleAccessToken', json_encode($accessToken));
        $user->access_token = json_encode($accessToken);
        if (isset($accessToken['refresh_token'])) {
            $user->refresh_token = $accessToken['refresh_token'];
        }
        $user->save();
    }

    /**
     * If access token is expired we refresh it using refresh token
     */
    public function refreshUserAccessToken()
    {
        if (Yii::$app->user->isGuest) {
            return;
        }
        $user = Yii::$app->user->identity;
        $accessToken = $user->access_token;
        $this->client->setAccessToken($accessToken);
        if ($this->client->isAccessTokenExpired() && isset($user->refresh_token)) {
            $this->client->refreshToken($user->refresh_token);
            $accessToken = $this->client->getAccessToken();
            $user->access_token = json_encode($accessToken);
            $user->save();
        }
    }

    public function signIn($authCode)
    {
        if (empty($authCode)) {
            throw new BAException(self::AUTH_CODE_MUSTNT_BE_EMPTY, BAException::EMPTY_PARAM_EXCODE, null);
        }
        $accessToken = $this->client->authenticate($authCode);
        $userEmail = $this->retrieveUserEmail($this->client);
        if ($userEmail != null) {
            $user = $this->loginUserInYii($userEmail, $accessToken);
            if ($user != null) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }
}