<?php

/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 12/26/2015
 * Time: 1:13 PM
 */
namespace common\models;

use yii\db\ActiveRecord;

/**
 * Class ChangeOfTask
 * Represents entity of a record in Database with information about change of task
 * @package common\infrastructure
 */
class ChangeOfTask extends ActiveRecord {

    const STATUS_CREATED = "CREATED";
    const STATUS_UPDATED = "UPDATED";
    const STATUS_DELETED = "DELETED";

    public static function tableName()
    {
        return 'sync_changed_tasks';
    }

    /**
     * Retrieve the task bound which the Change Task is
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTask() {
        return $this->hasOne(Task::className(), ['id' => 'task_id']);
    }
}