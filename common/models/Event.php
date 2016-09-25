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

class Event extends ActiveRecord {

    public static function tableName() {
        return 'events';
    }


    public function rules()
    {
        return [
            ['params', 'validateParams']
        ];
    }

    public function getEventType() {
        return $this->hasOne(EventType::className(), ['id' => 'type']);
    }

    public function validateParams($attribute, $p) {
        $params = json_decode($this->$attribute);
        if (empty($params)) {
            $this->addError($attribute, 'Corrupted params of events');
            CustomLogger::log("We have empty/null (event) params after json_decode() for event with id = " . $this->id, 'error', null, false);
        }
    }
}