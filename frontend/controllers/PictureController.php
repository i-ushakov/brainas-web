<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 7/10/2016
 * Time: 3:31 PM
 */

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use frontend\components\GoogleIdentityHelper;
use frontend\components\GoogleDriveHelper;


class PictureController extends Controller {
    public $mimeTypes_Extensions = [
        "image/jpeg" => 'jpg',
        "image/gif" => 'gif',
        "image/png" => 'png',
        "image/bmp" => 'bmp'
    ];

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionUpload() {
        $result = array();
        $imgData = $_POST['imageData'];

        list($type, $imgData) = explode(';', $imgData);
        $mimeType = str_replace("data:" , "", $type);
        list(, $imgData)      = explode(',', $imgData);
        $imgData = base64_decode($imgData);

        if ((!Yii::$app->user->isGuest)) {
            $user = \Yii::$app->user->identity;

            $client = GoogleIdentityHelper::getGoogleClientWithToken($user);

            if ($client != null) {
                $pictureFolderId = $user->pictureFolder->resource_id;
                if (!isset($pictureFolderId)) {
                    $pictureFolderId = $this->createGoogleDriveFolders($client);
                }

                if (isset($this->mimeTypes_Extensions[$mimeType])) {
                    $imageName = "task_picture_" . round(microtime(true) * 1000) . "." . $this->mimeTypes_Extensions[$mimeType];
                    $driveService = new \Google_Service_Drive($client);
                    $fileMetadata = new \Google_Service_Drive_DriveFile(array(
                        'name' => $imageName,
                        'mimeType' => $mimeType,
                        'parents' => array($pictureFolderId)));
                    $file = $driveService->files->create($fileMetadata, array(
                        'data' => $imgData,
                        'mimeType' => 'image/jpeg',
                        'uploadType' => 'multipart',
                        'fields' => 'id'));

                    if ($file != null) {
                        $result['status'] = "SUCCESS";
                        $result['message'] = "Image successfuly upload inot google docs";
                        $result['picture_name'] = $imageName;
                        $result['picture_file_id'] = $file->id;
                    }
                } else {
                    $result['status'] = "FAILED";
                    $result['code'] = "bad_image_format";
                    $result['message'] = $mimeType . " an not acceptable format of image";
                }
            } else {
                $result['status'] = "FAILED";
                $result['code'] = "problem_with_token";
                $result['message'] = "problem_with_token";
            }
        }

        \Yii::$app->response->format = 'json';
        return json_encode($result);

    }

    public function actionRemove() {
        $result = array();
        if ((!Yii::$app->user->isGuest)) {
            $user = \Yii::$app->user->identity;

            $post = Yii::$app->request->post();
            $pictureForRemove = json_decode($post['picture'], true);
            if (isset($pictureForRemove['task_id'])) {

            }
            if (isset($pictureForRemove['file_id'])) {
                $client = GoogleIdentityHelper::getGoogleClientWithToken($user);
                $googleDriveHelper = new GoogleDriveHelper($client);
                $googleDriveHelper->removeFile($pictureForRemove['file_id']);
            }
        } else {
            $result['status'] = "FAILED";
            $result['type'] = "must_be_signed_in";
        }

        \Yii::$app->response->format = 'json';
        return json_encode($result);
    }

    private function createGoogleDriveFolders($client) {
        $driveService = new \Google_Service_Drive($client);
        // TODO create folders
    }
}