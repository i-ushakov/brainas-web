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
    const CLIENT_NOT_HAVE_TOKEN_MSG = "Client(Google_Client) not have a access token";
    const CLIENT_TOKEN_WAS_EXPIRED_MSG = "Client(Google_Client) have expired token";

    private static $instance;
    private $service;
    private $message;

    public static function getInstance($client) {
        if (null === static::$instance) {
            static::$instance = new static($client);
        }

        return static::$instance;
    }


    private function __construct(\Google_Service_Drive $service) {
        $this->service = $service;
        $client = $this->service->getClient();
        $token = $client->getAccessToken();

        if (!isset($token)) {
            throw new BAException(self::CLIENT_NOT_HAVE_TOKEN_MSG, BAException::INVALID_PARAM_EXCODE, null);
        }

        if ($client->isAccessTokenExpired()) {
            throw new BAException(self::CLIENT_TOKEN_WAS_EXPIRED_MSG, BAException::INVALID_PARAM_EXCODE, null);
        }
    }

    static public function buildImageRef($imageGoogleDriveId) {
        $imageRef = "https://drive.google.com/uc?export=view&id=" . $imageGoogleDriveId;
        return $imageRef;
    }

    public function getFileIdByName($fileName) {
        if (!isset($fileName)) {
            return null;
        }
        $response = $this->service->files->listFiles(array(
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
        $this->service->files->delete($fileId);
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
            $response = $this->service->files->listFiles($params);
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