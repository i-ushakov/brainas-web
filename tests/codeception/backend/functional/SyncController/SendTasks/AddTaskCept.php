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
    ['accessToken' => Yii::$app->params['testAccessToken']],
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
    'user' => 1,
    'message' => 'Task 1 ADDED(ACTIVE)',
    'description' => 'Task 1 Desc',
    'status' => 'ACTIVE'));

$I->seeInDatabase('conditions', array(
    'task_id' => 1,
    'type' => 1,
    'params' => '{"lat":55.5991901,"lng":38.1256387,"radius":200}')
);