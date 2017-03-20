<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/2/2017
 * Time: 5:07 PM
 */

/* @var $scenario Codeception\Scenario */

$I = new \FunctionalTester($scenario);

$I->sendPOST('sync/send-tasks',
    ['token' => 'value'],
    ['tasks_changes_xml' => codecept_data_dir('SyncControllerFeed/tasks_changes_add_tasks.xml')]
);
$I->seeResponseCodeIs(200);

/*
    <?xml version="1.0" encoding="UTF-8"?>' .
    '<synchronizedTasks>' .
        '<synchronizedTask>' .
            '<localId>1</localId>' .
            '<globalId>?</globalId>' .
        '</synchronizedTask>' .
    '</synchronizedTasks>';
 */

$I->wantTo('check that xml response is correct');
try {
    $response = $I->grabResponse();
    ob_start();
    var_dump($response);
    $result = ob_get_clean();
    file_put_contents("t11.txt", $result);
    $responseXML = new \SimpleXMLElement($response);
} catch (Exception $exception) {
    $I->fail("Response is not valid XML");
}

$I->assertEquals($responseXML->getName(), 'synchronizedTasks', 'Wrong root element name');
$I->assertEquals(count($responseXML->synchronizedTask), 1, 'Must have one synchronizedTask element');

$synchronizedTask = $responseXML->synchronizedTask[0];
$I->assertEquals(intval($synchronizedTask->localId), 1, 'Local id have to be 1');

$I->wantTo('check that task 1 was UPDATED in database');
$I->seeInDatabase('tasks', array(
    'message' => 'Task 1 ADDED(ACTIVE)',
    'descriptio' => 'Task 1 Desc',
    'status' => 'ACTIVE'));