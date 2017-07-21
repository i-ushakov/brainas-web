<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 7/16/2017
 * Time: 11:08 AM
 */

namespace common\components\logging;


use Yii;
use yii\db\ActiveRecord;

class LogTag extends ActiveRecord
{
    public static function tableName() {
        return 'log_tags';
    }
}