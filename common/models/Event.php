<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 10/20/2015
 * Time: 9:28 PM
 */

namespace common\models;


use yii\db\ActiveRecord;

class Event extends ActiveRecord {

    public static function tableName() {
        return 'events';
    }

    public function getEventType() {
        return $this->hasOne(EventType::className(), ['id' => 'type']);
    }
}