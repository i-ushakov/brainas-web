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

    public function getPicture() {
        return $this->hasOne(PictureOfTask::className(), ['task_id' => 'id']);
    }

    public function getChangeOfTask() {
        return $this->hasOne(ChangeOfTask::className(), ['task_id' => 'id']);
    }

    public function beforeSave($insert)
    {
        $this->last_modify = date('Y-m-d H:i:s');
        if (parent::beforeSave($insert)) {
            $this->updateStatus();
            return true;
        } else {
            return false;
        }
    }

    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        if($insert == true) {
            $action = "Created";
        } else {
            $action = "Changed";
        }
        ChangeOfTask::loggingChangesForSync($action, null, $this);
    }


    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            foreach (Condition::find()->where('task_id = ' . $this->id)->all() as $condition) {
                $condition->delete();
            }
            foreach(PictureOfTask::find()->where('task_id = ' . $this->id)->all() as $pictureOfTask) {
                $pictureOfTask->delete();
            }
            return true;
        } else {
            return false;
        }
    }

    public function afterDelete() {
        parent::afterDelete();
        ChangeOfTask::removeFromChangeLog($this->id);
    }

    public function updateStatus() {
        Yii::warning("before ==".$this->status);
        $conditions = $this->conditions;
        if ($this->status == 'TODO' && !empty($conditions)) {
            $this->status = "WAITING";
        }
        if ($this->status == 'DISABLED' && !empty($conditions)) {
            $this->status = "WAITING";
        }
        if(($this->status == 'WAITING' || $this->status == 'ACTIVE') && empty($conditions)) {
            $this->status = "DISABLED";
        }
        Yii::warning("after ==".$this->status);
    }
}