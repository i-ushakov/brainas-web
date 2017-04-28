<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 4/28/2017
 * Time: 11:07 AM
 */
namespace backend\components;

use common\models\GoogleDriveFolder;

class SettingsManager
{
    public function handleSettings($user, $settings) {
        if ($user != null) {
            $projectFolder = GoogleDriveFolder::findOne(['id' => $user->projectFolder->id]);
            $pictureFolder = GoogleDriveFolder::findOne(['id' => $user->pictureFolder->id]);

            if (isset($settings) && !empty($settings)) {
                if (
                    !isset($projectFolder->resource_id) &&
                    isset($settings[GoogleDriveFolder::PROJECT_FOLDER_RESOURCE_ID]) &&
                    $settings[GoogleDriveFolder::PROJECT_FOLDER_RESOURCE_ID] != ""
                ) {
                    $projectFolder->resource_id = $settings[GoogleDriveFolder::PROJECT_FOLDER_RESOURCE_ID];
                }
                $projectFolder->save();

                if (
                    !isset($pictureFolder->resource_id) &&
                    isset($settings[GoogleDriveFolder::PICTURE_FOLDER_RESOURCE_ID]) &&
                    $settings[GoogleDriveFolder::PICTURE_FOLDER_RESOURCE_ID] != ""
                ) {
                    $pictureFolder->resource_id = $settings[GoogleDriveFolder::PICTURE_FOLDER_RESOURCE_ID];
                }
                $pictureFolder->save();
            }

            if (isset($projectFolder->resource_id)) {
                $settings[GoogleDriveFolder::PROJECT_FOLDER_RESOURCE_ID] = $projectFolder->resource_id;
            }
            if (isset($pictureFolder->resource_id)) {
                $settings[GoogleDriveFolder::PICTURE_FOLDER_RESOURCE_ID] = $pictureFolder->resource_id;
            }
            return json_encode($settings);
        } else {
            return null;
        }
    }
}