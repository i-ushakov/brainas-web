<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 10/20/2015
 * Time: 9:28 PM
 */

namespace common\models;

use common\components\BAException;
use yii\db\ActiveRecord;

class EventType extends ActiveRecord {
    const WRONG_NAME_OF_TYPE__MSG = "Wrong name of event type";

    public static function tableName() {
        return 'event_types';
    }

    public static function  getTypeIdByName($typeName) {
        $eventType = self::find()
            ->where(['name' => $typeName])
            ->one();
        if ($eventType == null) {
            throw new BAException(self::WRONG_NAME_OF_TYPE__MSG, BAException::WRONG_NAME_OF_EVENT_TYPE_ERRORCODE);
        }

        return  $eventType->id;;
    }
}