<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 11/16/2015
 * Time: 1:01 PM
 */


namespace backend\controllers;


use Google_Client;
use Yii;
use backend\components\TaskSyncHelper;
use backend\components\GoogleAuthHelper;


use yii\web\Controller;
use yii\web\HttpException;

class ConnectionController extends Controller {

    const STATUS_INVALID_TOKEN = "INVALID_TOKEN";

    public static $jsonGoogleClientConfig = "/var/www/brainas.net/backend/config/client_secret_925705811320-cenbqg1fe5jb804116oefl78sbishnga.apps.googleusercontent.com.json";

    private $userId;
    private $token;
    private $synchronizedObjects;

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionFetchAccessToken() {
        $code = $this->getCodeFromPost();
        $token = GoogleAuthHelper::getAccessTokenByCode($code);
        echo json_encode($token);
    }

    public function actionGetTasks() {
        // get user id and access token
        $token = $this->getTokenFromPost();
        try {
            $authInfo = GoogleAuthHelper::verifyUserAccess($token);
        } catch (\InvalidArgumentException $e) {
            Yii::warning("catch InvalidArgumentException");
            echo self::STATUS_INVALID_TOKEN;
            exit();
        }
        $token = $authInfo['token'];
        $userId =  $authInfo['userId'];
        // retrive xml-document with device changes from file
        $deviceChanges = $this->loadDeviceChangesFromXML();
        $taskSyncHelper = new TaskSyncHelper($deviceChanges, $userId, $token);
        $xmlResponse = $taskSyncHelper->doSynchronizationOfTasks();

        echo $xmlResponse;
    }


    private function loadDeviceChangesFromXML() {
        $deviceChangesInXML = simplexml_load_file($_FILES['all_changes_xml']['tmp_name']);
        return $deviceChangesInXML;
    }

    private function getCodeFromPost() {
        $post = Yii::$app->request->post();
        //$post['accessToken'] = "";
        if(isset($post['accessCode'])) {
            $accessCode = $post['accessCode'];
            return $accessCode;
        }
        return null;
    }

    private function getTokenFromPost() {
        $post = Yii::$app->request->post();
        //$post['accessToken'] = "";
        if(isset($post['accessToken'])) {
            $accessToken = $post['accessToken'];
        } else {
            throw new HttpException(470 ,'Token wasn\'t sent');
        }
        return json_decode($accessToken, true);
    }
}