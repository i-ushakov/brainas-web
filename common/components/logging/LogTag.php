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

/**
 * Class LogTag
 * Model represents logging tag entity, every tag aims to spot what process
 * is bound with saved the log
 * @package common\components\logging
 */
class LogTag extends ActiveRecord
{
    public static function tableName() {
        return 'log_tags';
    }
}