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
use backend\components\SettingsManager;
use common\models\User;
use common\components\logging\BALogger;

use \Google_Client;

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
        $token = $this->getAccessTokenFromPost();
        $authInfo = GoogleAuthHelper::verifyUserAccess($token);
        if (isset($authInfo['userEmail'])) {
            $user = User::find()->where(['username' => $authInfo['userEmail']])->one();
            $settings = $this->retrieveSettingsFomPost();
            $settingsManager = new SettingsManager();
            return $settingsManager->handleSettings($user, $settings);
        }
    }

    /*
     * Synchronization tasks from device with server
     */
    public function actionSendTasks()
    {
        BALogger::i(
            "Received SEND request from a device",
            [BALogger::TAG_SYNC, BALogger::TAG_SENDACTION]
        );

        $token = $this->getAccessTokenFromPost();

        $authInfo = GoogleAuthHelper::verifyUserAccess($token);
        $userId = $authInfo['userId'];

        $changedTasksXml = $this->getChangedTaskFromPost();

        Yii::$container->set(Google_Client::class . "_WithToken", GoogleAuthHelper::getClientWithToken($token));

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

        Yii::$container->set(Google_Client::class . "_WithToken", GoogleAuthHelper::getClientWithToken($token));

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
        BALogger::i(
            "Access token was retrieved from POST-request",
            [BALogger::TAG_SYNC, BALogger::TAG_SENDACTION, BALogger::TAG_TOKEN],
            $accessToken
        );
        return json_decode($accessToken, true);
    }

    /**
     * Create and return XML object based on xml-document from POST request
     * with data about created and updated tasks
     *
     * @return \SimpleXMLElement[]
     */
    protected function getChangedTaskFromPost() {
        $syncDataFromDevice = simplexml_load_file($_FILES['tasks_changes_xml']['tmp_name']);
        BALogger::i(
            "XML with updated tasks was received from device",
            [BALogger::TAG_SYNC, BALogger::TAG_SENDACTION, BALogger::TAG_DEVICE_DATA],
            $syncDataFromDevice->asXML()
        );

        // TODO: When I'll remove <existingTasks>, the <changedTasks> will become root element
        $changedTasksXml = $syncDataFromDevice->changedTasks;
        return $changedTasksXml;
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

    protected function retrieveSettingsFomPost() {
        $post = Yii::$app->request->post();
        if(isset($post['settings'])) {
            $settings = $post['settings'];
            return json_decode($settings, true);
        }
        return null;
    }
}