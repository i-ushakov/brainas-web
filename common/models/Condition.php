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

    public function rules()
    {
        return [
            ['events', 'validateEvents']
        ];
    }

    public function getEvents() {
        return $this->hasMany(Event::className(), ['condition_id' => 'id']);
    }

    public function beforeDelete() {
        Event::deleteAll(['condition_id' => $this->id]);
        return parent::beforeDelete();
    }

    public function validateEvents($attribute, $params) {
        foreach ($this->$attribute as $event) {
            if (!$event->validate()) {
                $this->addError($attribute, 'Invalid events');
            }
        }
    }
    /*public function addNewEvent($event) {
        return true;
    }*/
}