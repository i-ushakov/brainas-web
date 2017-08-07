<?php

/**
 * Created by PhpStorm.
 * User: kit
 * Date: 11/22/2016
 * Time: 3:24 PM
 */

namespace common\models;

use yii\db\ActiveRecord;

/**
 * Class Condition
 * Represent task's condition, that determines when task will be activated
 * 
 * @package common\models
 */
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

    /**
     * Every conditional has a type of event, which is activate this condition
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEventType() {
        return $this->hasOne(EventType::className(), ['id' => 'type']);
    }

    /**
     * Checking that event id is valid
     *
     * @param $attribute
     * @param $params
     */
    public function validateEventType($attribute, $params) {
        $typeId = $this->$attribute;
        if (EventType::findOne(['id' => $typeId]) == null) {
            $this->addError($attribute, 'Invalid eventType');
        }
    }
}