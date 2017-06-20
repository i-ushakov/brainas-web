<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 7/5/2016
 * Time: 8:06 AM
 */

namespace common\components;

use common\models\PictureOfTask;
use Yii;
use common\models\Task;
class GoogleDriveHelper {
    private static $instance;
    private $client;
    private $driveService;
    private $message;

    public static function getInstance($client) {
        if (null === static::$instance) {
            static::$instance = new static($client);
        }

        return static::$instance;
    }


    public function __construct(\Google_Client $client) {
        $this->driveService = new \Google_Service_Drive($client);
    }

    static public function buildImageRef($imageGoogleDriveId) {
        $imageRef = "https://drive.google.com/uc?export=view&id=" . $imageGoogleDriveId;
        return $imageRef;
    }

    static public function test($imageGoogleDriveId) {
        var_dump("imageGoogleDriveId");
        var_dump($imageGoogleDriveId);
        $client = GoogleIdentityHelper::getGoogleClient();
        $user = \Yii::$app->user->identity;
        $client->setAccessToken($user->access_token);

        $driveService = new \Google_Service_Drive($client);
        $pageToken = null;
        do {
            $response = $driveService->files->listFiles(array(
                'q' => "name='task_img_1467800323282.png'",
                'spaces' => 'drive',
                'pageToken' => $pageToken,
                'fields' => 'nextPageToken, files(id, name, webViewLink)',
            ));
            foreach ($response->files as $file) {
            printf("Found file: %s (%s) (%s)\n", $file->name, $file->id, $file->webViewLink);
            }
        } while ($pageToken != null);
    }

    public function getFileIdByName($fileName) {
        if (!isset($fileName)) {
            return null;
        }
        $response = $this->driveService->files->listFiles(array(
            'q' => "name='$fileName'",
            'spaces' => 'drive',
            'fields' => 'nextPageToken, files(id, name)',
        ));
        if (count($response->files) > 0) {
            $file = $response->files[0];
            return $file->id;
        } else {
            return null;
        }
    }

    public function getResourceIdForTask($task) {
        $resourceId = null;
        if (isset($task->picture->fileId) && $task->picture->fileId != "") {
            $resourceId = $task->picture->fileId;
        } else {
            $resourceId = $this->getFileIdByName($task->picture->name);
            $picture = PictureOfTask::findOne(['task_id' => $task->id]);
            $picture->file_id = $resourceId;
            $picture->save();
        }
        return $resourceId;
    }

    public function removeFile($fileId) {
        if (!isset($fileId) || $fileId== "") {
            $this->message = "No have fileId";
            return false;
        }
        $this->driveService->files->delete($fileId);
        return true;
    }

    /**
     * @param $folderId
     * @return array
     */
    public function getListOfFiles($folderId, $datetime = null) {
        $params = array(
            'q' => "'$folderId'" . " in parents",
            'spaces' => 'drive',
            'fields' => 'nextPageToken, files(id, name)',
        );
        if ($datetime != null) {
            $params['q'] .= " and modifiedTime < '$datetime'";
        }
        try {
            $response = $this->driveService->files->listFiles($params);
        } catch (\Google_Service_Exception $e) {
            Yii::error("Google_Service_Exception when try to get list of files");
            return null;
        }

        $filesInFolder = array();
        foreach ($response->files as $file) {
            $filesInFolder[] = $file;
        }
        return $filesInFolder;
    }

    public function deleteUnusedPictures($user) {
        if (!isset($user) || !isset($user->pictureFolder) || !isset($user->pictureFolder->resource_id)) {
            return;
        }
        $activePictures = array();
        $tasks = Task::find()->where(['user' => $user->id])->with('picture')->all();
        foreach ($tasks as $task) {
            if (isset($task->picture)) {
                $taskPictureName = $task->picture->name;
                $activePictures[] = $taskPictureName;
            }
        }
        $picturesInFolder = $this->getListOfFiles($user->pictureFolder->resource_id, date('Y-m-d\TH:i:s', strtotime('-1 hour')));
        if ($picturesInFolder != null) {
            foreach ($picturesInFolder as $pictureInFolder) {
                if (!in_array($pictureInFolder->name, $activePictures) && strpos("task_picture_", $pictureInFolder->name) == 0) {
                    $this->removeFile($pictureInFolder->id);
                }
            }
        }
    }
}