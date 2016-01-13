<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 1/13/2016
 * Time: 4:58 PM
 */

namespace common\infrastructure;

use yii\db\ActiveRecord;

class SingInLog extends ActiveRecord {
    public static function tableName() {
        return 'singin_log';
    }
}