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

class Condition extends ActiveRecord {

    public static function tableName() {
        return 'conditions';
    }

    public function getEvents() {
        return $this->hasMany(Event::className(), ['condition_id' => 'id']);
    }


    public function addEventFromXML(\SimpleXMLElement $eventXML) {
        if (isset($eventXML['globalId'])) {
            $eventId = $eventXML['globalId'];
            $event = Event::find($eventId)
                ->where(['id' => $eventId])
                ->one();
        } else {
            $event = new Event();
            $event->condition_id = $this->id;
        }
        $event->type = EventType::getTypeIdByName((string)$eventXML->type);
        $event->params = (string)$eventXML->params;
        $event->save();
    }

    public function beforeDelete() {
        Event::deleteAll(['condition_id' => $this->id]);
        return parent::beforeDelete();
    }

    /*public function addNewEvent($event) {
        return true;
    }*/
}