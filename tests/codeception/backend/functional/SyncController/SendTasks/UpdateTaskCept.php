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

$I->haveInDatabase('conditions', array(
    'id' => 10,
    'task_id' => 101,
    'type' => 2,
    'params' => '{"offset":180,"datetime":"15-01-2016 00:00:00"}'));

$I->haveInDatabase('conditions', array(
    'id' => 11,
    'task_id' => 101,
    'type' => 2,
    'params' => '{"offset":180,"datetime":"01-11-2017 13:34:08"}'));

$I->haveInDatabase('sync_changed_tasks', array(
    'id' => 11,
    'user_id' => 1,
    'task_id' => 101,
    'datetime' => '2017-02-04 00:00:00',
    'server_update_time' => '2017-02-04 00:00:00'));



$I->sendPOST('sync/send-tasks',
    ['accessToken' => Yii::$app->params['testAccessToken']],
    ['tasks_changes_xml' => codecept_data_dir('SyncControllerFeed/tasks_changes_update_tasks.xml')]
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
$I->assertEquals(intval($synchronizedTask->localId), 1, 'Local id have to be 1');

$I->wantTo('check that Task 101 was UPDATED in database');

$I->seeInDatabase('tasks', [
    'id' => 101,
    'message' => 'Task 101 UPDATED(ACTIVE)',
    'status' => 'ACTIVE',
    'description' => 'Task 101 Desc'
]);

$I->seeInDatabase('conditions', [
    'id' => 11,
    'task_id' => 101,
    'type' => 2,
    'params' => '{"offset":180,"datetime":"02-12-2017 00:00:00"}'
]);

$I->seeInDatabase('conditions', [
    'task_id' => 101,
    'type' => 1,
    'params' => '{"address":"ул. Фрунзе, 12, Zhukovskiy","radius":100,"lng":38.125353455544,"lat":55.59917167827}'
]);

$I->dontSeeInDatabase('conditions', [
    'id' => 10,
    'task_id' => 101,
]);
