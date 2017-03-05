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

$I->sendPOST('/sync/send-tasks', ['token' => 'value'], [ 'tasks_changes_xml' => codecept_data_dir('SyncControllerFeed/tasks_changes.xml')]);

$I->wantTo('check that task 1 was UPDATED');
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
    'id' => 112,
    'task_id' => 11,
    'type' => 1,
    'params' => '{"lat":55.5991236,"lng":38.1258632,"radius":200,"address":"ulitsa Frunze, 12, Zhukovskiy"}'));