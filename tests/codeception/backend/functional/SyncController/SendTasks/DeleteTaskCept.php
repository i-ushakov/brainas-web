<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/2/2017
 * Time: 5:07 PM
 */

/* @var $scenario Codeception\Scenario */

$I = new \FunctionalTester($scenario);

$I->haveInDatabase('tasks', array(
    'id' => 101,
    'user' => 1,
    'message' => 'Task 101',
    'description' => 'No desc',
    'status' => 'TODO',
    'created' => '2017-02-04 00:00:00',
    'last_modify' => '2017-02-04 00:00:00'));

$I->haveInDatabase('sync_changed_tasks', array(
    'id' => 11,
    'user_id' => 1,
    'task_id' => 101,
    'datetime' => '2017-02-04 00:00:00',
    'server_update_time' => '2017-02-04 00:00:00'));



$I->sendPOST('sync/send-tasks',
    ['accessToken' => Yii::$app->params['testAccessToken']],
    ['tasks_changes_xml' => codecept_data_dir('SyncControllerFeed/tasks_changes_delete_tasks.xml')]
);

$I->seeResponseCodeIs(200);

/*
    <?xml version="1.0" encoding="UTF-8"?>' .
    '<synchronizedTasks>' .
        '<synchronizedTask>' .
            '<localId>1</localId>' .
            '<globalId>101</globalId>' .
        '</synchronizedTask>' .
    '</synchronizedTasks>';
 */

$I->wantTo('check that xml response is correct');
try {
    $response = $I->grabResponse();
    $responseXML = new \SimpleXMLElement($response);
} catch (Exception $exception) {
    $I->fail("Response is not valid XML");
}

$I->assertEquals($responseXML->getName(), 'synchronizedTasks', 'Wrong root element name');
$I->assertEquals(count($responseXML->synchronizedTask), 1, 'Must have one synchronizedTask element');

$synchronizedTask = $responseXML->synchronizedTask[0];
$I->assertEquals(intval($synchronizedTask->localId), 1, 'Local Id have to be 1');
$I->assertEquals(intval($synchronizedTask->globalId), 101, 'Global Id have to be 101');

$I->wantTo('check that Task 101 was DELETED from database');

$I->dontSeeInDatabase('tasks', [
    'id' => 101
]);
