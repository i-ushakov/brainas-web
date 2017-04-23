<?php

/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/2/2017
 * Time: 5:07 PM
 */

class GetUpdatesFromLastSyncCest
{
    public function _before(\FunctionalTester $I)
    {
        $DBCleaner = new DBCleaner(Yii::$app->db);
        $DBCleaner->clean();
    }

    /* @var $scenario Codeception\Scenario */

    public function tryToTest(\FunctionalTester $I)
    {

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

        $I->sendPOST('sync/get-tasks',
            ['accessToken' => Yii::$app->params['testAccessToken'], 'last_sync_time' => '10-04-2017 10:00'],
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

        $I->assertEquals($responseXML->getName(), 'tasks', 'Wrong root element name');
        $I->assertEquals(count($responseXML->created), 1, 'Must have one created element');

        $created = $responseXML->created;
        $I->assertEquals(count($created->task), 1, 'Wrong number of created tasks elements');

        $task1 = $created->task[0];
        $I->assertEquals(104, (int)$task1['globalId'], 'Wrong globalId');
        $I->assertEquals("2017-02-04 00:00:00", $task1['timeOfChange'], 'Wrong timeOfChange');

        $I->assertEquals("Task 104", $task1->message, 'Wrong message');
        $I->assertEquals("No desc", $task1->description, 'Wrong description');
        $I->assertEquals("TODO", $task1->status, 'Wrong status');
        $I->assertEquals("TODO", $task1->status, 'Wrong status');

        $conditions1 = $task1->conditions;
        $I->assertEquals(count($conditions1), 1, 'Must have 1 condiitons element');
        $I->assertEquals(count($conditions1->condition), 0, 'Must have 0 condiiton elements');
    }

}