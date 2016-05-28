<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 10/20/2015
 * Time: 9:28 PM
 */

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use common\infrastructure\ChangeOfTask;

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

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            foreach (Condition::find()->where('task_id = ' . $this->id)->all() as $condition) {
                $condition->delete();
            }
            return true;
        } else {
            return false;
        }
    }

    public function afterDelete() {
        parent::afterDelete();
        $this->loggingChangesForSync("Deleted");
    }

    public function loggingChangesForSync($action, $changeDatetime = null) {
        $changedTask = ChangeOfTask::find()
            ->where(['user_id' => $this->user, 'task_id' => $this->id])
            ->orderBy('id')
            ->one();
        if (empty($changedTask)) {
            $changedTask = new ChangeOfTask();
            $changedTask->task_id = $this->id;
            $changedTask->user_id = $this->user;
        }

        if ($changeDatetime == null) {
            $currentDatetime = new \DateTime();
            $currentDatetime->setTimezone(new \DateTimeZone("UTC"));
            $changedTask->datetime = $currentDatetime->format('Y-m-d H:i:s');
        } else {
            $changedTask->datetime = $changeDatetime;
        }
        $changedTask->action = $action;
        $changedTask->save();
    }

    public function addConditionFromXML(\SimpleXMLElement $conditionXML) {
        if (isset($conditionXML['globalId'])) {
            $conditionId = $conditionXML['globalId'];
            $condition = Condition::find($conditionId)
                ->where(['id' => $conditionId])
                ->one();
        } else {
            $condition = new Condition();
            $condition->task_id = $this->id;
            $condition->save();
        }
        foreach($conditionXML->events->event as $event) {
            $condition->addEventFromXML($event);
        }
    }
}