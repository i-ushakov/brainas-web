<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/2/2017
 * Time: 5:08 PM
 */

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use backend\components\TasksSyncManager;
use backend\components\GoogleAuthHelper;
use common\models\GoogleDriveFolder;
use common\models\User;

class SyncController extends Controller
{
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        \Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /*
     * Refresh user's access token
     */
    public function actionRefreshToken()
    {
        $token = $this->getAccessTokenFromPost();

        if (is_null($token)) {
            $xmlResp = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
                "<errors><error>Token wasn't given</error></errors>";
            return $xmlResp;
        }

        $authInfo = GoogleAuthHelper::verifyUserAccess($token);
        $token = $authInfo['token'];
        $userEmail =  $authInfo['userEmail'];

        $xmlResp = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
            "<credentials>" .
                "<accessToken> " . json_encode($token) . "</accessToken>" .
                "<userEmail>$userEmail</userEmail>" .
            "</credentials>";

        return $xmlResp;

    }

    /*
     * Synchronization settings between dives and server
     */
    public function actionGetSettings() {
        $accessToken = $this->getAccessTokenFromPost();
        if (isset($accessToken)) {
            $user = $this->getUserByToken($accessToken);
            $settings = $this->retrieveSettingsFomPost();
            return $this->handleSettings($user, $settings);
        }
    }

    /*
     * Synchronization tasks from device with server
     */
    public function actionSendTasks()
    {
        $token = $this->getAccessTokenFromPost();

        $authInfo = GoogleAuthHelper::verifyUserAccess($token);
        $userId = $authInfo['userId'];

        $syncDataFromDevice = simplexml_load_file($_FILES['tasks_changes_xml']['tmp_name']);
        $changedTasksXml = $syncDataFromDevice->changedTasks;

        /* @var $tasksSyncManager TasksSyncManager */
        $tasksSyncManager = Yii::$container->get(TasksSyncManager::class);
        $tasksSyncManager->setUserId($userId);

        $syncObjectsXml = $tasksSyncManager->handleTasksFromDevice($changedTasksXml);
        return $syncObjectsXml;
    }

    /*
     * Synchronization tasks from server with device
     */
    public function actionGetTasks()
    {
        $token = $this->getAccessTokenFromPost();

        $authInfo = GoogleAuthHelper::verifyUserAccess($token);
        $userId = $authInfo['userId'];

        $existsTasksFromDeviceXml = simplexml_load_file($_FILES['exists_tasks_xml']['tmp_name']);
        $existingTasksFromDevice = json_decode($existsTasksFromDeviceXml->existingTasks);
        $timeOfLastSync = $this->getTimeOfLastSync();

        /* @var $tasksSyncManager TasksSyncManager */
        $tasksSyncManager = Yii::$container->get(TasksSyncManager::class);
        $tasksSyncManager->setUserId($userId);

        $resultXml = $tasksSyncManager->getXmlWithChanges($existingTasksFromDevice, $timeOfLastSync);

        return $resultXml;
    }

    protected function getAccessTokenFromPost()
    {
        $post = Yii::$app->request->post();
        if(isset($post['accessToken'])) {
            $accessToken = $post['accessToken'];
        } else {
            return null;
        }
        return json_decode($accessToken, true);
    }

    protected function getTimeOfLastSync()
    {
        $post = Yii::$app->request->post();
        if(isset($post['lastSyncTime'])) {
            $timeOfLastSync = $post['lastSyncTime'];
        } else {
            return null;
        }
        return $timeOfLastSync;
    }

    protected function getUserByToken($accessToken) {
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
        $user = User::find()->where(['username' => $userEmail])->one();
        return $user;
    }

    protected function retrieveSettingsFomPost() {
        $post = Yii::$app->request->post();
        if(isset($post['settings'])) {
            $settings = $post['settings'];
            return json_decode($settings, true);
        }
        return null;
    }

    private function handleSettings($user, $settings) {
        if ($user != null) {
            $projectFolder = GoogleDriveFolder::findOne(['id' => $user->projectFolder->id]);
            $pictureFolder = GoogleDriveFolder::findOne(['id' => $user->pictureFolder->id]);

            if (isset($settings) && !empty($settings)) {
                if (
                    !isset($projectFolder->resource_id) &&
                    isset($settings[GoogleDriveFolder::PROJECT_FOLDER_RESOURCE_ID]) &&
                    $settings[GoogleDriveFolder::PROJECT_FOLDER_RESOURCE_ID] != ""
                ) {
                    $projectFolder->resource_id = $settings[GoogleDriveFolder::PROJECT_FOLDER_RESOURCE_ID];
                }
                $projectFolder->save();

                if (
                    !isset($pictureFolder->resource_id) &&
                    isset($settings[GoogleDriveFolder::PICTURE_FOLDER_RESOURCE_ID]) &&
                    $settings[GoogleDriveFolder::PICTURE_FOLDER_RESOURCE_ID] != ""
                ) {
                    $pictureFolder->resource_id = $settings[GoogleDriveFolder::PICTURE_FOLDER_RESOURCE_ID];
                }
                $pictureFolder->save();
            }

            if (isset($projectFolder->resource_id)) {
                $settings[GoogleDriveFolder::PROJECT_FOLDER_RESOURCE_ID] = $projectFolder->resource_id;
            }
            if (isset($pictureFolder->resource_id)) {
                $settings[GoogleDriveFolder::PICTURE_FOLDER_RESOURCE_ID] = $pictureFolder->resource_id;
            }
            return json_encode($settings);
        } else {
            return null;
        }
    }
}