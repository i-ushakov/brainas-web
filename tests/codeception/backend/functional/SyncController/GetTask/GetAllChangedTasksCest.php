<?php

/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/2/2017
 * Time: 5:07 PM
 */

use  \common\infrastructure\ChangeOfTask;

class GetAllChangedTasksCest
{
    public function _before(\FunctionalTester $I)
    {
        $DBCleaner = new DBCleaner(Yii::$app->db);
        $DBCleaner->clean();
    }

    public function tryToTest(\FunctionalTester $I)
    {
        // Task 99 was Created on server
        $I->haveInDatabase('tasks', array(
            'id' => 99,
            'user' => 2,
            'message' => 'Task 99',
            'description' => 'No desc 99',
            'status' => 'ACTIVE',
            'created' => '2017-01-01 00:00:00',
            'last_modify' => '2017-01-20 00:00:00'));

        $I->haveInDatabase('sync_changed_tasks', array(
            'id' => 99,
            'user_id' => 2,
            'task_id' => 99,
            'action' => ChangeOfTask::STATUS_UPDATED,
            'datetime' => '2017-01-19 00:00:00',
            'server_update_time' => '2017-01-20 00:00:00'));


        $I->sendPOST('sync/get-tasks',
            ['accessToken' => Yii::$app->params['testAccessToken']],
            ['exists_tasks_xml' => codecept_data_dir('SyncControllerFeed/exists_tasks.xml')]
        );
        $I->seeResponseCodeIs(200);

        /*
            <?xml version="1.0" encoding="UTF-8"?>' .
            <tasks>
                <updated>
                    <task globalId="99" time-changes="2017-01-19 00:00:00">
                        <message>Task 99</message>
                        <description>No desc</description>
                        <conditions></conditions>
                        <status>ACTIVE</status>
                    </task>
                </updated>
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
        $I->assertEquals(count($created), 1, 'Must have one <created> element');
        $I->assertEquals(count($created->task), 0, 'Wrong number of created tasks elements');

        $updated = $responseXML->tasks->updated;
        $I->assertEquals(count($updated), 1, 'Must have one updated element');
        $I->assertEquals(count($updated->task), 1, 'Wrong number of updated tasks elements');

        $task1 = $updated->task[0];

        $I->assertEquals(99, (int)$task1['globalId'], 'Wrong globalId');
        $I->assertEquals("2017-01-19 00:00:00", $task1['timeOfChange'], 'Wrong timeOfChange');

        $I->assertEquals("Task 99", $task1->message, 'Wrong message');
        $I->assertEquals("No desc 99", $task1->description, 'Wrong description');
        $I->assertEquals("ACTIVE", (string)$task1->status, 'Wrong status');

        $conditions1 = $task1->conditions;
        $I->assertEquals(count($conditions1), 1, 'Must have 1 condiitons element');
        $I->assertEquals(count($conditions1->condition), 0, 'Must have 0 condiiton elements');
    }

}