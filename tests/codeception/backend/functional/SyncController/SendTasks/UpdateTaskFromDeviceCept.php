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
    'id' => 11,
    'message' => 'Task 1',
    'status' => 'WAITING',
    'last_modify' => '2017-02-03 11:50:10'
));
$I->haveInDatabase('conditions', array(
    'id' => 111,
    'task_id' => 11,
    'type' => 2,
    'params' => '{"datetime":"18-03-2017 12:46:00","offset":180}'
));

$I->sendPOST('/sync/send-tasks', ['token' => 'value'], ['tasks_changes_xml' => codecept_data_dir('SyncControllerFeed/tasks_changes.xml')]);
$I->seeResponseCodeIs(200);


// I want to check that xml response is correct
/*
    <?xml version="1.0" encoding="UTF-8"?>' .
    '<synchronizedObjects>' .
        '<synchronizedTasks>' .
            '<synchronizedTask>' .
                '<localId>1</localId>' .
                '<globalId>11</globalId>' .
            '</synchronizedTask>' .
        '</synchronizedTasks>' .
    '</synchronizedObjects>';
 */
$I->wantTo('check that xml response is correct');
$response = $I->grabResponse();
try {
    $responseXML = new \SimpleXMLElement($response);
} catch (Exception $exception) {
    $I->fail("response is not valid XML");
}
$I->assertEquals($responseXML->getName(), 'synchronizedObjects', 'Wrong root element name');

$I->assertEquals(count($responseXML->synchronizedTasks), 1, 'Must have one synchronizedTasks element');

$synchronizedTasks = $responseXML->synchronizedTasks;
$I->assertEquals(count($synchronizedTasks->synchronizedTask), 1, 'Must have one synchronizedTask element');

$synchronizedTask = $synchronizedTasks->synchronizedTask[0];
$I->assertEquals(intval($synchronizedTask->localId), 1, 'Local id have to be 1');
$I->assertEquals(intval($synchronizedTask->globalId), 11, 'Global id have to be 11');

$I->assertEquals(count($responseXML->synchronizedConditions), 1, 'Must have one synchronizedConditions element');
$I->assertEquals(count($responseXML->synchronizedConditions->synchronizedCondition), 2, 'Must have two synchronizedCondition element');

$synchronizedConditions = $responseXML->synchronizedConditions->synchronizedCondition;
$I->assertEquals(intval($synchronizedConditions[0]->localId), 11, 'Local id have to be 11');
$I->assertEquals(intval($synchronizedConditions[0]->globalId), 111, 'Global id have to be 111');
$I->assertEquals(intval($synchronizedConditions[1]->localId), 12, 'Local id have to be 12');
$I->assertEquals(count($synchronizedConditions[1]->globalId), 1, 'Must have one globalId element');


// I want to check that task 1 was UPDATED in database
$I->wantTo('check that task 1 was UPDATED in database');
$I->seeInDatabase('tasks', array(
    'id' => 11,
    'message' => 'Task 1 UPDATED (ACTIVE)',
    'status' => 'ACTIVE'));
$I->seeInDatabase('conditions', array(
    'id' => 111,
    'task_id' => 11,
    'type' => 2,
    'params' => '{"datetime":"18-03-2017 13:46:00","offset":180}'));
$I->seeInDatabase('conditions', array(
    'task_id' => 11,
    'type' => 1,
    'params' => '{"lat":55.5991236,"lng":38.1258632,"radius":200,"address":"ulitsa Frunze, 12, Zhukovskiy"}'));