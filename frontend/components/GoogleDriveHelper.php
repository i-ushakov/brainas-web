<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 7/5/2016
 * Time: 8:06 AM
 */

namespace frontend\components;

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


    public function __construct($client) {
        $this->driveService = new \Google_Service_Drive($client);
    }

    private function __clone() {}

    private function __wakeup() {}

    static public function getImageFolder() {
        $imageFolderPath = "";
        $client = GoogleIdentityHelper::getGoogleClient();
        $user = \Yii::$app->user->identity;
        GoogleIdentityHelper::refreshUserAccessToken();
        $client->setAccessToken($user->access_token);
        $driveService = new \Google_Service_Drive($client);
        $pageToken = null;
        do {
            $response = $driveService->files->listFiles(array(
                'q' => "name='ba_settings.json'",
                'spaces' => 'appDataFolder',
                'pageToken' => $pageToken,
                'fields' => 'nextPageToken, files(id, name)',
            ));
            //foreach ($response->files as $file) {
                //printf("Found file: %s (%s)\n", $file->name, $file->id);
            //}
        } while ($pageToken != null);

        $fileId = $response->files[0]->id;
        $file = $response->files[0];

        $file = $driveService->files->get($fileId);
        //var_dump("==getDownloadUrl==");
        //var_dump($file->getDownloadUrl());
        /*$results =  $googleDriveService->files->listFiles(array(
            'spaces' => 'appDataFolder',
            'fields' => 'nextPageToken, files(id, name, webContentLink)',
            'pageSize' => 10
        ));

        if (count($results->getFiles()) == 0) {
            //print "No files found.\n";
        } else {
            //print "Files:\n";
            foreach ($results->getFiles() as $file) {
                printf("%s (%s)\n", $file->getName(), $file->getId());
                print_r("111".$file->getWebContentLink(). "11111");
            }
        }*/
        return $imageFolderPath;
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
        $response = $this->driveService->files->listFiles($params);

        $filesInFolder = array();
        foreach ($response->files as $file) {
            $filesInFolder[] = $file;
        }
        return $filesInFolder;
    }

    public function deleteUnusedPictures($user) {
        $activePictures = array();
        $tasks = Task::find()->where(['user' => $user->id])->with('picture')->all();
        foreach ($tasks as $task) {
            if (isset($task->picture)) {
                $taskPictureName = $task->picture->name;
                $activePictures[] = $taskPictureName;
            }
        }
        $picturesInFolder = $this->getListOfFiles($user->pictureFolder->resource_id, date('Y-m-d\TH:i:s', strtotime('-1 hour')));
        foreach($picturesInFolder as $pictureInFolder) {
            if (!in_array($pictureInFolder->name, $activePictures) && strpos("task_picture_", $pictureInFolder->name) == 0) {
                $this->removeFile($pictureInFolder->id);
            }
        }
    }
}