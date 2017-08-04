<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 1/13/2016
 * Time: 4:58 PM
 */

namespace common\models;

use yii\db\ActiveRecord;

/**
 * Class SingInLog
 * AR for saving information about whan and who logged
 * @package common\models
 */
class SingInLog extends ActiveRecord {
    public static function tableName() {
        return 'singin_log';
    }
}