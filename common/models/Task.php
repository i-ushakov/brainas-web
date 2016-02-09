<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 10/20/2015
 * Time: 9:28 PM
 */

namespace common\models;

use yii\db\ActiveRecord;
use common\infrastructure\ChangedTask;

class Task extends ActiveRecord {

    public static function tableName()
    {
        return 'tasks';
    }

    public function rules()
    {
        return [
            [['message', 'user'], 'required'],
            [['message'], 'string','max'=>100],
            [['description'], 'string','max'=>5000]

            // the email attribute should be a valid email address
            //['email', 'email'],
        ];
    }

    public function getConditions() {
        return $this->hasMany(Condition::className(), ['task_id' => 'id']);
    }

    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        if($insert == true) {
            $action = "Created";
        } else {
            $action = "Changed";
        }
        $this->loggingChangesForSync($action);
    }

    public function afterDelete() {
        parent::afterDelete();
        $this->loggingChangesForSync("Deleted");
    }

    public function loggingChangesForSync($action, $datetime = null) {
        $changedTask = ChangedTask::find()
            ->where(['user_id' => $this->user, 'task_id' => $this->id])
            ->orderBy('id')
            ->one();
        if (empty($changedTask)) {
            $changedTask = new ChangedTask();
            $changedTask->task_id = $this->id;
            $changedTask->user_id = $this->user;
        }

        if ($datetime == null) {
            $datetime = new \DateTime();
            $datetime->setTimezone(new \DateTimeZone("Europe/London"));
        }
        $changedTask->action = $action;
        $changedTask->datetime = $datetime->format('Y-m-d H:i:s');
        $changedTask->save();
    }
}