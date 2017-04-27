<?php

/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/2/2017
 * Time: 5:07 PM
 */

use  \common\infrastructure\ChangeOfTask;

class GetTasksChangedFromLastSyncCest
{
    public function _before(\FunctionalTester $I)
    {
        $DBCleaner = new DBCleaner(Yii::$app->db);
        $DBCleaner->clean();
    }

    public function tryToTest(\FunctionalTester $I)
    {

        // Task 50 was changed befor last sync
        $I->haveInDatabase('tasks', array(
            'id' => 50,
            'user' => 1,
            'message' => 'Task 50',
            'description' => 'No desc 50',
            'status' => 'TODO',
            'created' => '2016-02-04 00:00:00',
            'last_modify' => '2017-01-02 00:00:00'));

        $I->haveInDatabase('sync_changed_tasks', array(
            'id' => 5,
            'user_id' => 1,
            'task_id' => 50,
            'action' => 'CREATED',
            'datetime' => '2017-01-01 00:00:00',
            'server_update_time' => '2017-01-02 00:00:00'));

        $lastSyncTime = '2017-01-03 00:00:00';

        // Task 104 was Created on server
        $I->haveInDatabase('tasks', array(
            'id' => 104,
            'user' => 1,
            'message' => 'Task 104',
            'description' => 'No desc',
            'status' => 'TODO',
            'created' => '2017-02-04 00:00:00',
            'last_modify' => '2017-02-04 00:00:00'));

        $I->haveInDatabase('sync_changed_tasks', array(
            'id' => 14,
            'user_id' => 1,
            'task_id' => 104,
            'action' => 'CREATED',
            'datetime' => '2017-02-04 00:00:00',
            'server_update_time' => '2017-02-04 00:00:00'));

        // Task 99 was Created on server
        $I->haveInDatabase('tasks', array(
            'id' => 99,
            'user' => 1,
            'message' => 'Task 99',
            'description' => 'No desc 99',
            'status' => 'ACTIVE',
            'created' => '2017-01-01 00:00:00',
            'last_modify' => '2017-01-20 00:00:00'));

        $I->haveInDatabase('sync_changed_tasks', array(
            'id' => 99,
            'user_id' => 1,
            'task_id' => 99,
            'action' => ChangeOfTask::STATUS_UPDATED,
            'datetime' => '2017-01-19 00:00:00',
            'server_update_time' => '2017-01-20 00:00:00'));


        $I->sendPOST('sync/get-tasks',
            ['accessToken' => Yii::$app->params['testAccessToken'], 'lastSyncTime' => $lastSyncTime],
            ['exists_tasks_xml' => codecept_data_dir('SyncControllerFeed/exists_tasks.xml')]
        );
        $I->seeResponseCodeIs(200);

        /*
            <?xml version="1.0" encoding="UTF-8"?>' .
            <tasks>
                <created>
                    <task globalId="104" time-changes="2017-02-04 00:00:00">
                        <message>Task 104</message>
                        <description>No desc</description>
                        <conditions></conditions>
                        <status>ACTIVE</status>
                    </task>
                </created>
                <updated>
                    ....
                </updated>
                <deleted>
                    <deletedTask globalId=66 localId=6></deletedTask>
                    <deletedTask globalId=77 localId=7></deletedTask>
                </deleted>
            </tasks>
         */

        $I->wantTo('check that xml response is correct');
        try
        {
            $response = $I->grabResponse();
            $responseXML = new \SimpleXMLElement($response);
        }

        catch
        (Exception $exception) {
            $I->fail("Response is not valid XML");
        }

        $I->assertEquals($responseXML->getName(), 'changes', 'Wrong root element name');
        $I->assertEquals(count($responseXML->tasks), 1, 'Must have 1 <tasks> elemnt');

        $created = $responseXML->tasks->created;
        $I->assertEquals(count($created), 1, 'Must have one created element');
        $I->assertEquals(count($created->task), 1, 'Wrong number of created tasks elements');

        $task1 = $created->task[0];
        $I->assertEquals(104, (int)$task1['globalId'], 'Wrong globalId');
        $I->assertEquals("2017-02-04 00:00:00", $task1['timeOfChange'], 'Wrong timeOfChange');

        $I->assertEquals("Task 104", $task1->message, 'Wrong message');
        $I->assertEquals("No desc", $task1->description, 'Wrong description');
        $I->assertEquals("TODO", $task1->status, 'Wrong status');

        $conditions1 = $task1->conditions;
        $I->assertEquals(count($conditions1), 1, 'Must have 1 condiitons element');
        $I->assertEquals(count($conditions1->condition), 0, 'Must have 0 condiiton elements');

        $updated = $responseXML->tasks->updated;
        $I->assertEquals(count($updated), 1, 'Must have one updated element');
        $I->assertEquals(count($updated->task), 1, 'Wrong number of updated tasks elements');

        $deleted = $responseXML->tasks->deleted;
        $I->assertEquals(count($deleted), 1, 'Must have one <deleted> element');
        $I->assertEquals(count($deleted->deletedTask), 2, 'Must have 2 <deletedTask> element');
    }

}