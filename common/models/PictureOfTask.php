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

class PictureOfTask extends ActiveRecord {

    public static function tableName() {
        return 'tasks_pictures';
    }
}