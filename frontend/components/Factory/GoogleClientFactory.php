<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 8/7/2017
 * Time: 12:34 PM
 */

namespace frontend\components\Factory;

use Yii;

/**
 * Class GoogleClientFactory
 * Just for creating Google_Client object with bunch of params
 *
 * @package frontend\components\Factory
 */
class GoogleClientFactory
{
    static protected $APP_NAME = "Brain Assistant app";

    public static function create()
    {
        $client = new \Google_Client();
        $client->setApplicationName(self::$APP_NAME);
        $client->setScopes(implode(' ', array(
            \Google_Service_Drive::DRIVE_APPDATA,
            \Google_Service_Drive::DRIVE_METADATA,
            \Google_Service_Drive::DRIVE_FILE,
            \Google_Service_Drive::DRIVE
        )));
        $client->setAuthConfigFile(Yii::$app->params['ClientSecretPath']);
        $client->setDeveloperKey(Yii::$app->params['ServiceAccountKey']);
        $client->setRedirectUri("postmessage");
        $client->setAccessType('offline');
        return $client;
    }
}