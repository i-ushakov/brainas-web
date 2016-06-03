<?php

/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 5/2/2016
 * Time: 11:26 AM
 */
namespace backend\components;

use Yii;
use \common\models\Task;
use \common\models\Condition;
use \common\models\Event;
use \common\models\EventType;


class TaskHelper {
    /*
     * We are getting tasks that we have on device but they are  absent on server side
     */
    static public function getTasksRemovedOnServer($existingTasksOnDevice, $userId) {
        $removedTasks = array();
        $existingTasksOnServer = array();
        $tasks = Task::findAll(['user' => $userId]);
        foreach ($tasks as $task) {
            $existingTasksOnServer[] = intval($task->id);
        }
        foreach ($existingTasksOnDevice as $serverId => $localId) {
            if (!in_array(intval($serverId), $existingTasksOnServer)) {
                $removedTasks[$serverId] = intval($localId);
            }
        }
        return $removedTasks;
    }
}