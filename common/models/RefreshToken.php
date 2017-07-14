<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 07/05/2017
 * Time: 11:06 PM
 */

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class RefreshToken extends ActiveRecord {

    public static function tableName() {
        return 'refresh_tokens';
    }
}