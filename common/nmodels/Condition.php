<?php

/**
 * Created by PhpStorm.
 * User: kit
 * Date: 11/22/2016
 * Time: 3:24 PM
 */

namespace common\nmodels;

use yii\db\ActiveRecord;


class Condition extends ActiveRecord {


    public static function tableName() {

        return 'conditions';
    }
}