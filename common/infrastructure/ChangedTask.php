<?php

/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 12/26/2015
 * Time: 1:13 PM
 */
namespace common\infrastructure;

use yii\db\ActiveRecord;

class ChangedTask extends ActiveRecord {

    public static function tableName()
    {
        return 'sync_changed_tasks';
    }
}