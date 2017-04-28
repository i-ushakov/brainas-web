<?php

/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/2/2017
 * Time: 5:07 PM
 */

use  \common\infrastructure\ChangeOfTask;

class SyncSettingsCest
{
    public function _before(\FunctionalTester $I)
    {
        $DBCleaner = new DBCleaner(Yii::$app->db);
        $DBCleaner->clean();
    }

    public function tryToTest(\FunctionalTester $I)
    {

        $I->haveInDatabase('google_drive_folders', array(
            'id' => 1,
            'user_id' => 1,
            'folder_type' => 1,
            'resource_id' => '0B-nWSp42Pq2nQ01rY3NmMlVVV1k',
            'timestamp' => '2016-08-03 14:49:25'));

        $I->haveInDatabase('google_drive_folders', array(
            'id' => 2,
            'user_id' => 1,
            'folder_type' => 2,
            'resource_id' => '0B-nWSp42Pq2nRm1mRHNqeTFTYkk',
            'timestamp' => '2016-08-03 14:49:30'));

        $I->sendPOST('sync/get-settings',
            ['accessToken' => Yii::$app->params['testAccessToken'], 'settings' => '{}']
        );
        $I->seeResponseCodeIs(200);

        $I->wantTo('check that xml response is correct');
        try {
            $response = $I->grabResponse();
        } catch (Exception $exception) {
            $I->fail("Response is not valid XML");
        }

        $expectedResult = '{' .
            '"PROJECT_FOLDER_RESOURCE_ID":"0B-nWSp42Pq2nQ01rY3NmMlVVV1k",' .
            '"PICTURE_FOLDER_RESOURCE_ID":"0B-nWSp42Pq2nRm1mRHNqeTFTYkk"' .
        '}';
        $I->assertEquals($expectedResult, $response, 'Must have 2 <deletedTask> element');
    }

}