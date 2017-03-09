<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/2/2017
 * Time: 5:08 PM
 */

namespace backend\controllers;

use yii\web\Controller;

class SyncController extends Controller
{
    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        \Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /*
     * Synchronization tasks from device with server
     */
    public function actionSendTasks()
    {
        // TODO verifyUserAccess
        // $syncDataFromDevice = simplexml_load_file($_FILES['all_changes_xml']['tmp_name']);
        // TODO $result = SyncTasksManager->getTasksFromDevice($syncDataFromDevice);
        // TODO return $result;
    }

    /*
     * Synchronization tasks from server with device
     */
    public function actionGetTasks()
    {
        //TODO
    }
}