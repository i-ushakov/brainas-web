<?php

/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 12/26/2015
 * Time: 1:13 PM
 */
namespace common\infrastructure;

use yii\db\ActiveRecord;
use common\models\Task;

class ChangeOfTask extends ActiveRecord {

    public static function tableName()
    {
        return 'sync_changed_tasks';
    }

    public function getTask() {
        return $this->hasOne(Task::className(), ['id' => 'task_id']);
    }

    static public function loggingChangesForSync($action, $changeDatetime = null, $task) {
        $changedTask = ChangeOfTask::find()
            ->where(['user_id' => $task->user, 'task_id' => $task->id])
            ->orderBy('id')
            ->one();
        if (empty($changedTask)) {
            $changedTask = new ChangeOfTask();
            $changedTask->task_id = $task->id;
            $changedTask->user_id = $task->user;
        }

        if ($changeDatetime == null) {
            $currentDatetime = new \DateTime();
            $currentDatetime->setTimezone(new \DateTimeZone("UTC"));
            $changedTask->datetime = $currentDatetime->format('Y-m-d H:i:s');
        } else {
            $changedTask->datetime = $changeDatetime;
        }
        $changedTask->server_update_time = date('Y-m-d H:i:s');
        $changedTask->action = $action;
        $changedTask->save();
    }

    static public function removeFromChangeLog($taskId) {
        ChangeOfTask::find()->where(['task_id' => $taskId])->one()->delete();
    }
}