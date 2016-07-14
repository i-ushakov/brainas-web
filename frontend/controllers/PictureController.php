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
    const TMP_FOLDER = "/var/www/brainas.net/frontend/tmp/testImg";

    public $mimeTypes_Extensions = [
        "image/jpeg" => 'jpg',
        "image/gif" => 'gif',
        "image/png" => 'png',
        "image/bmp" => 'bmp'
    ];

    public $exif_imagetype_code_mimeTypes = [
        1 => "image/gif",
        2 => "image/jpeg",
        3 => "image/png",
        6 => 'image/bmp'
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
                        'mimeType' => $mimeType,
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

    public function actionDownload() {
        $result = array();
        if ((!Yii::$app->user->isGuest)) {
            $user = \Yii::$app->user->identity;

            if (isset($_POST['imageUrl']) && trim($_POST['imageUrl']) != "") {
                $imageUrl = trim($_POST['imageUrl']);
                if ($this->checkResponseCodeIsOk($imageUrl)) {
                    $imageContent = file_get_contents($imageUrl);
                    $client = GoogleIdentityHelper::getGoogleClientWithToken($user);
                    if ($client != null) {
                        $pictureFolderId = $user->pictureFolder->resource_id;
                        if (!isset($pictureFolderId)) {
                            $pictureFolderId = $this->createGoogleDriveFolders($client);
                        }

                        file_put_contents(self::TMP_FOLDER, $imageContent);
                        $imageType = exif_imagetype(self::TMP_FOLDER);
                        if (isset($this->exif_imagetype_code_mimeTypes[$imageType])) {
                            $mimeType = $this->exif_imagetype_code_mimeTypes[$imageType];
                        } else {
                            $mimeType = null;
                        }
                        if (isset($this->mimeTypes_Extensions[$mimeType])) {
                            $imageName = "task_picture_" . round(microtime(true) * 1000) . "." . $this->mimeTypes_Extensions[$mimeType];
                            $driveService = new \Google_Service_Drive($client);
                            $fileMetadata = new \Google_Service_Drive_DriveFile(array(
                                'name' => $imageName,
                                'mimeType' => $mimeType,
                                'parents' => array($pictureFolderId)));
                            $file = $driveService->files->create($fileMetadata, array(
                                'data' => $imageContent,
                                'mimeType' => $mimeType,
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
                            $result['message'] = "Bad image format";
                        }
                    } else {
                        $result['status'] = "FAILED";
                        $result['code'] = "problem_with_token";
                        $result['message'] = "problem_with_token";
                    }
                } else {
                    $result['status'] = "FAILED";
                    $result['code'] = "bad_url";
                    $result['message'] = "Bad reference";
                }
            } else {
                $result['status'] = "FAILED";
                $result['code'] = "no_url";
                $result['message'] = "no_url";
            }
        } else {
            $result['status'] = "FAILED";
            $result['code'] = "must_be_signed_in";
            $result['message'] = "User is not signin";
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

    private function checkResponseCodeIsOk($url) {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }
        $headers = get_headers($url);
        $response = substr($headers[0], 9, 3);
        if ($response != "200") {
            return false;
        } else {
            return true;
        }

    }
}