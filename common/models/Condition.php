<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 10/20/2015
 * Time: 9:28 PM
 */

namespace common\models;

use yii\db\ActiveRecord;

class Condition extends ActiveRecord {

    public static function tableName() {
        return 'conditions';
    }

    public function getEvents() {
        return $this->hasMany(Event::className(), ['condition_id' => 'id']);
    }
}