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
     * Synchronization tasks from device with server
     */
    public function actionSendTasks()
    {
        $token = $this->getAccessTokenFromPost();

        $authInfo = GoogleAuthHelper::verifyUserAccess($token);
        $userId = $authInfo['userId'];

        $syncDataFromDevice = simplexml_load_file($_FILES['tasks_changes_xml']['tmp_name']);
        $changedTasksXml = $syncDataFromDevice->changedTasks;

        $tasksSyncManager = Yii::$container->get(TasksSyncManager::class);
        $tasksSyncManager->setUserId($userId);

        $synchronizedTasks = $tasksSyncManager->handleTasksFromDevice($changedTasksXml);
        $syncObjectsXml = $tasksSyncManager->prepareSyncObjectsXml($synchronizedTasks);
        return $syncObjectsXml;
    }

    /*
     * Synchronization tasks from server with device
     */
    public function actionGetTasks()
    {
        //TODO
    }

    protected function getAccessTokenFromPost() {
        $post = Yii::$app->request->post();
        if(isset($post['accessToken'])) {
            $accessToken = $post['accessToken'];
        } else {
            return null;
        }
        return json_decode($accessToken, true);
    }
}