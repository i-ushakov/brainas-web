<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 5/9/2017
 * Time: 8:31 PM
 */

class GetAllTasksCest
{
    public function _before(\FunctionalTester $I)
    {
        $DBCleaner = new DBCleaner(Yii::$app->db);
        $DBCleaner->clean();
    }

    // I think we don't need to test this stuff, it will be overhead (YAGNI)
    public function getAllTest(\FunctionalTester $I)
    {
        //$I->sendGET('/task/get', ['statusesFilter' => 'ALL', 'typeOfSort' => 'TIME_ADDED_NEWEST']);
        //$response = $I->grabResponse();
        //file_put_contents("test.txt1", $response);

        //https://brainas.com/task/get?statusesFilter%5B%5D=ACTIVE&statusesFilter%5B%5D=WAITING&statusesFilter%5B%5D=TODO&statusesFilter%5B%5D=DISABLED&statusesFilter%5B%5D=DONE&statusesFilter%5B%5D=CANCELED&typeOfSort=TIME_ADDED_NEWEST
    }

}