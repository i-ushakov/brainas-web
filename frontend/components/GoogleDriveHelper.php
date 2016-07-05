<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 7/5/2016
 * Time: 8:06 AM
 */

namespace frontend\components;

use Yii;
class GoogleDriveHelper {
    static public function getImageFolder() {
        $imageFolderPath = "";
        $client = GoogleIdentityHelper::getGoogleClient();
        $user = \Yii::$app->user->identity;
        var_dump("==access_token1==");
        var_dump($user->access_token);
        GoogleIdentityHelper::refreshUserAccessToken();
        $client->setAccessToken($user->access_token);
        var_dump("==access_token2==");
        var_dump($user->access_token);
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

}