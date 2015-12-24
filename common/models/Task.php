<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 10/20/2015
 * Time: 9:28 PM
 */

namespace common\models;

use yii\db\ActiveRecord;

class Task extends ActiveRecord {

    public static function tableName()
    {
        return 'tasks';
    }

    public function rules()
    {
        return [
            // the name, email, subject and body attributes are required
            [['message'], 'required'],
            [['message'], 'string','max'=>100],
            [['description'], 'string','max'=>5000]

            // the email attribute should be a valid email address
            //['email', 'email'],
        ];
    }

    public function getConditions() {
        return $this->hasMany(Condition::className(), ['task_id' => 'id']);
    }
}