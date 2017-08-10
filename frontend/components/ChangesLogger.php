<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 8/10/2017
 * Time: 8:45 AM
 */

namespace frontend\components;


use common\utils\DatetimeUtils;
use common\models\ChangeOfTask;


/**
 * Class ChangesLogger
 * This class responsible for save logs of task changes
 * for using this info for synchronization (to know what tasks were be updated)
 *
 * @package frontend\components
 */
class ChangesLogger
{
    /**
     * Logging changes of task in database
     * using ChangeOfTask model
     *
     * @param $action
     * @param $task
     */
    public function logChanges($action, $task) {
        $changeOfTask = ChangeOfTask::find()
            ->where(['user_id' => $task->user, 'task_id' => $task->id])
            ->orderBy('id')
            ->one();
        if (empty($changeOfTask)) {
            $changeOfTask = new ChangeOfTask();
            $changeOfTask->task_id = $task->id;
            $changeOfTask->user_id = $task->user;
        }

        $changeOfTask->datetime = DatetimeUtils::getCurrentUTCTime();

        $changeOfTask->server_update_time = date('Y-m-d H:i:s');
        $changeOfTask->action = $action;
        $changeOfTask->save();
    }
}