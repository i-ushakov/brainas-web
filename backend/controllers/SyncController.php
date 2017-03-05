<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/2/2017
 * Time: 5:08 PM
 */
use Yii;

use yii\web\Controller;

class SyncController extends Controller
{
    public function actionSyncTasks()
    {
        // TODO verifyUserAccess
        // $syncDataFromDevice = simplexml_load_file($_FILES['all_changes_xml']['tmp_name']);
        // TODO SyncTasksManager->sync($syncDataFromDevice);
    }
}