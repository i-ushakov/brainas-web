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


use common\models\User;
use common\models\GoogleDriveFolder;


use yii\web\Controller;
use yii\web\HttpException;

class ConnectionController extends Controller {

    const STATUS_INVALID_TOKEN = "INVALID_TOKEN";

    public static $jsonGoogleClientConfig = "/var/www/brainas.net/backend/config/client_secret_925705811320-cenbqg1fe5jb804116oefl78sbishnga.apps.googleusercontent.com.json";

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionAuthenticateUser() {
        $code = $this->getCodeFromPost();
        $accessToken = GoogleAuthHelper::getAccessTokenByCode($code);
        echo json_encode($accessToken);
    }

    public function actionSyncSettings() {
        $accessToken = $this->getAccessTokenFromPost();
        if (isset($accessToken)) {
            $user = $this->getUserByToken($accessToken);
            $settings = $this->retrieveSettingsFomPost();
            return $this->handleSettings($user, $settings);
        }
    }

    public function actionGetTasks() {
        // get user id and access token
        $token = $this->getAccessTokenFromPost();
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
        $xmlResponse = $taskSyncHelper->doSynchronization();

        echo $xmlResponse;
    }

    private function handleSettings($user, $settings) {
        if ($user != null) {
            Yii::warning("===111===");
            Yii::warning($user);
            Yii::warning($user->projectFolder->id);
            Yii::warning($user->pictureFolder->id);
            $projectFolder = GoogleDriveFolder::findOne(['id' => $user->projectFolder->id]);
            $pictureFolder = GoogleDriveFolder::findOne(['id' => $user->pictureFolder->id]);
            Yii::warning("==projectFolder==");
            Yii::warning($projectFolder);
            Yii::warning("==pictureFolder==");
            Yii::warning($pictureFolder);

            if (isset($settings) && !empty($settings)) {
                if (isset($settings[GoogleDriveFolder::PROJECT_FOLDER_DRIVE_ID]) &&
                    $settings[GoogleDriveFolder::PROJECT_FOLDER_DRIVE_ID] != ""
                ) {
                    $projectFolder->drive_id = $settings[GoogleDriveFolder::PROJECT_FOLDER_DRIVE_ID];
                }
                if (isset($settings[GoogleDriveFolder::PROJECT_FOLDER_RESOURCE_ID]) &&
                    $settings[GoogleDriveFolder::PROJECT_FOLDER_RESOURCE_ID] != ""
                ) {
                    $projectFolder->resource_id = $settings[GoogleDriveFolder::PROJECT_FOLDER_RESOURCE_ID];
                }
                $projectFolder->save();

                if (isset($settings[GoogleDriveFolder::PICTURE_FOLDER_DRIVE_ID]) &&
                    $settings[GoogleDriveFolder::PICTURE_FOLDER_DRIVE_ID] != ""
                ) {
                    $pictureFolder->drive_id = $settings[GoogleDriveFolder::PICTURE_FOLDER_DRIVE_ID];
                }
                if (isset($settings[GoogleDriveFolder::PICTURE_FOLDER_RESOURCE_ID]) &&
                    $settings[GoogleDriveFolder::PICTURE_FOLDER_RESOURCE_ID] != ""
                ) {
                    $pictureFolder->resource_id = $settings[GoogleDriveFolder::PICTURE_FOLDER_RESOURCE_ID];
                }
                $pictureFolder->save();
            }

            if (isset($projectFolder->drive_id)) {
                $settings[GoogleDriveFolder::PROJECT_FOLDER_DRIVE_ID] = $projectFolder->drive_id;
            }
            if (isset($projectFolder->resource_id)) {
                $settings[GoogleDriveFolder::PROJECT_FOLDER_RESOURCE_ID] = $projectFolder->resource_id;
            }
            if (isset($pictureFolder->drive_id)) {
                $settings[GoogleDriveFolder::PICTURE_FOLDER_DRIVE_ID] = $pictureFolder->drive_id;
            }
            if (isset($pictureFolder->resource_id)) {
                $settings[GoogleDriveFolder::PICTURE_FOLDER_RESOURCE_ID] = $pictureFolder->resource_id;
            }
            return json_encode($settings);
        } else {
            return null;
        }
    }


    private function loadDeviceChangesFromXML() {
        $syncDataFromDevice = simplexml_load_file($_FILES['all_changes_xml']['tmp_name']);
        return $syncDataFromDevice;
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

    private function retrieveSettingsFomPost() {
        $post = Yii::$app->request->post();
        //$post['accessToken'] = "";
        if(isset($post['settings'])) {
            $settings = $post['settings'];
            return json_decode($settings, true);
        }
        return null;
    }

    private function getAccessTokenFromPost() {
        $post = Yii::$app->request->post();
        if(isset($post['accessToken'])) {
            $accessToken = $post['accessToken'];
        } else {
            throw new HttpException(470 ,'Token wasn\'t sent');
        }
        return json_decode($accessToken, true);
    }

    private function getUserByToken($accessToken) {
        $client = GoogleAuthHelper::getGoogleClient();
        $client->setAccessToken($accessToken);
        if ($client->isAccessTokenExpired()) {
            if (isset($accessToken['refresh_token'])) {
                $accessToken = $client->refreshToken($accessToken['refresh_token']);
                $client->setAccessToken($accessToken);
            } else {
                throw new InvalidArgumentException();
            }
        }
        $data = $client->verifyIdToken();
        $userEmail = $data['email'];
        $user = User::find(['username' => $userEmail])->one();
        return $user;
    }

}