<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 10/20/2015
 * Time: 9:28 PM
 */

namespace common\models;

use yii\db\ActiveRecord;

/**
 * Class Task
 * One of the crucial model of app.
 * It has information about what adn when user has to do.
 *
 * @package common\models
 */
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

    /**
     * They determine when task will be active
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConditions() {
        return $this->hasMany(Condition::className(), ['task_id' => 'id']);
    }

    /**
     * The picture that bonded with task and presented it on a tile's face
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPicture() {
        return $this->hasOne(PictureOfTask::className(), ['task_id' => 'id']);
    }

    /**
     * Information about type and time of last change of task
     * @return \yii\db\ActiveQuery
     */
    public function getChangeOfTask() {
        return $this->hasOne(ChangeOfTask::className(), ['task_id' => 'id']);
    }

    /**
     * Some manipulation with data before save
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->last_modify = date('Y-m-d H:i:s');
        if (parent::beforeSave($insert)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * We need to remove all conditions bonded with task before remove this task
     *
     * @return bool
     */
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

    /**
     * After delete of task we have to remove bonded ChangeOfTask model
     * (in a nutshell, a record from database with info about last changes)
     */
    public function afterDelete() {
        parent::afterDelete();
        ChangeOfTask::removeFromChangeLog($this->id);
    }
}