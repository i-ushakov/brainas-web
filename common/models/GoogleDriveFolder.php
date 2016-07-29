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

class GoogleDriveFolder extends ActiveRecord {
    const PROJECT_FOLDER_RESOURCE_ID = 'PROJECT_FOLDER_RESOURCE_ID';
    const PICTURE_FOLDER_RESOURCE_ID = 'PICTURE_FOLDER_RESOURCE_ID';

    public static function tableName() {
        return 'google_drive_folders';
    }
}