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

            $client = GoogleIdentityHelper::getGoogleClient();
            $client->setAccessToken($user->access_token );

            if ($client->isAccessTokenExpired() && isset($user->refresh_token)) {
                $client->refreshToken($user->refresh_token);
                $user->access_token = json_encode($client->getAccessToken());
                $user->save();
            } else {
                $result['status'] = "FAILED";
                $result['code'] = "no_refresh_token";
                $result['message'] = "Access tokent txpired and we havn't refresh_token";
            }
            if (isset($this->mimeTypes_Extensions[$mimeType])) {
                $imageName = "task_picture_" . round(microtime(true) * 1000) . "." . $this->mimeTypes_Extensions[$mimeType];
                $driveService = new \Google_Service_Drive($client);
                $fileMetadata = new \Google_Service_Drive_DriveFile(array(
                    'name' => $imageName,
                    'mimeType' => $mimeType));
                $file = $driveService->files->create($fileMetadata, array(
                    'data' => $imgData,
                    'mimeType' => 'image/jpeg',
                    'uploadType' => 'multipart',
                    'fields' => 'id'));

                if ($file != null) {
                    $result['status'] = "SUCCESS";
                    $result['message'] = "Image successfuly upload inot google docs";
                    $result['fileid'] = $file->id;
                }
            } else {
                $result['status'] = "FAILED";
                $result['code'] = "bad_image_format";
                $result['message'] = $mimeType . " an not acceptable format of image";
            }
        }

        \Yii::$app->response->format = 'json';
        return json_encode($result);

    }
}