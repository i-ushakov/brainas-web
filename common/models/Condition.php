<?php

/**
 * Created by PhpStorm.
 * User: kit
 * Date: 11/22/2016
 * Time: 3:24 PM
 */

namespace common\models;

use yii\db\ActiveRecord;
use common\models\EventType as EventType;


class Condition extends ActiveRecord {

    public static function tableName() {
        return 'conditions';
    }

    public function rules()
    {
        return [
            [['task_id','type', 'params'], 'required'],
            ['type', 'validateEventType']
        ];
    }

    public function getEventType() {
        return $this->hasOne(EventType::className(), ['id' => 'type']);
    }

    public function validateEventType($attribute, $params) {
        $typeId = $this->$attribute;
        if (EventType::findOne(['id' => $typeId]) == null) {
            $this->addError($attribute, 'Invalid eventType');
        }
    }
}