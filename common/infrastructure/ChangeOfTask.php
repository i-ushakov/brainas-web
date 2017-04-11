<?php

/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 12/26/2015
 * Time: 1:13 PM
 */
namespace common\infrastructure;

use yii\db\ActiveRecord;
use common\nmodels\Task;

class ChangeOfTask extends ActiveRecord {

    const STATUS_CREATED = "CREATED";
    const STATUS_UPDATED = "UPDATED";
    const STATUS_DELETED = "DELETED";

    public static function tableName()
    {
        return 'sync_changed_tasks';
    }

    public function getTask() {
        return $this->hasOne(Task::className(), ['id' => 'task_id']);
    }

    static public function loggingChangesForSync($action, $changeDatetime = null, $task) {
        $changeOfTask = ChangeOfTask::find()
            ->where(['user_id' => $task->user, 'task_id' => $task->id])
            ->orderBy('id')
            ->one();
        if (empty($changeOfTask)) {
            $changeOfTask = new ChangeOfTask();
            $changeOfTask->task_id = $task->id;
            $changeOfTask->user_id = $task->user;
        }

        if ($changeDatetime == null) {
            $currentDatetime = new \DateTime();
            $currentDatetime->setTimezone(new \DateTimeZone("UTC"));
            $changeOfTask->datetime = $currentDatetime->format('Y-m-d H:i:s');
        } else {
            $changeOfTask->datetime = $changeDatetime;
        }
        $changeOfTask->server_update_time = date('Y-m-d H:i:s');
        $changeOfTask->action = $action;
        $changeOfTask->save();
    }

    static public function removeFromChangeLog($taskId) {
        $changeOfTask = ChangeOfTask::find()->where(['task_id' => $taskId])->one();
        if (!is_null($changeOfTask)) {
            $changeOfTask->delete();
        }
    }
}