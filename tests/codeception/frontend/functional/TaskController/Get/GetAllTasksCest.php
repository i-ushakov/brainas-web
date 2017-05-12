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

    public function tryToTest(\FunctionalTester $I)
    {
        //https://brainas.com/task/get?statusesFilter%5B%5D=ACTIVE&statusesFilter%5B%5D=WAITING&statusesFilter%5B%5D=TODO&statusesFilter%5B%5D=DISABLED&statusesFilter%5B%5D=DONE&statusesFilter%5B%5D=CANCELED&typeOfSort=TIME_ADDED_NEWEST
    }

}