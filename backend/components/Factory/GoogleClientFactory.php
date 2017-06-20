<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 6/20/2017
 * Time: 1:54 PM
 */

namespace backend\components\Factory;

use Yii;

/**
 * Class GoogleClientFactory - just for creating Google_Client object with bunch of params
 * @package backend\components\Factory
 */
class GoogleClientFactory
{
    static protected $APP_NAME = "Brain Assistant app";

    public static function create()
    {
        $client = new \Google_Client();
        $client->setApplicationName(self::$APP_NAME);
        $client->setAuthConfigFile(Yii::$app->params['ClientSecretPath']);
        $client->setRedirectUri("http://brainas.net/site/login");
        $client->setScopes(implode(' ', array(
            \Google_Service_Drive::DRIVE_APPDATA,
            \Google_Service_Drive::DRIVE_METADATA,
            \Google_Service_Drive::DRIVE_FILE,
            \Google_Service_Drive::DRIVE,
            \Google_Service_Oauth2::PLUS_LOGIN,
        )));

        $client->setDeveloperKey(Yii::$app->params['ServiceAccountKey']);
        $client->setAccessType('offline');
        return $client;
    }
}