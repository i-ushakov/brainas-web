<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/29/2017
 * Time: 2:58 PM
 */

use backend\components\ChangeOfTaskHandler;
use backend\components\TasksSyncManager;
use common\infrastructure\ChangeOfTask;
use common\components\BAException;


use Mockery as m;
use Codeception\Util\Stub;

class SyncTaskManager_RetriveChangesOfTaskFromDB_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testRetriveTwoChanges()
    {
        $changeOfTaskHandler = \Mockery::mock(ChangeOfTaskHandler::class);
        $changeOfTaskHandler->shouldReceive('setUserId');

        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandler);
        $tasksSyncManager->setUserId(1);

        $currentDate = date('Y-m-d H:i:s', time());
        $minuteAgo = date('Y-m-d H:i:s', strtotime("$currentDate -1 minute"));
        $twoMinutesAgo = date('Y-m-d H:i:s', strtotime("$currentDate -2 minute"));
        $sixSecondsAgo = date('Y-m-d H:i:s', strtotime("$currentDate -6 second"));
        $oneSecondAgo = date('Y-m-d H:i:s', strtotime("$currentDate -1 second"));
        $lastSyncTime = $minuteAgo;

        $this->tester->haveInDatabase('sync_changed_tasks', array(
            'id' => 121,
            'task_id' => 101,
            'action' => ChangeOfTask::STATUS_CREATED,
            'user_id' => 1,
            'datetime' => $sixSecondsAgo,
            'server_update_time' => $sixSecondsAgo));

        $this->tester->haveInDatabase('sync_changed_tasks', array(
            'id' => 122,
            'task_id' => 102,
            'action' => ChangeOfTask::STATUS_UPDATED,
            'user_id' => 1,
            'datetime' => $twoMinutesAgo,
            'server_update_time' => $oneSecondAgo));

        $this->tester->haveInDatabase('sync_changed_tasks', array(
            'id' => 123,
            'task_id' => 103,
            'action' => ChangeOfTask::STATUS_UPDATED,
            'user_id' => 1,
            'datetime' => $twoMinutesAgo,
            'server_update_time' => $twoMinutesAgo));

        $changesOfTasks = $tasksSyncManager->retrieveChangesOfTasksFromDB($lastSyncTime);

        $this->tester->assertEquals(2, count($changesOfTasks), 'We must have two changesOfTasks');
    }

    public function testThrowNoUserIdException()
    {
        $changeOfTaskHandler = \Mockery::mock(ChangeOfTaskHandler::class);

        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandler);

        $this->tester->expectException(
            new BAException(
                TasksSyncManager::USER_ID_MUST_TO_BE_SET_MSG,
                BAException::PARAM_NOT_SET_EXCODE
            ),
            function() use ($tasksSyncManager){
                $lastSyncTime = '2016-10-04 16:13:09';
                $tasksSyncManager->retrieveChangesOfTasksFromDB($lastSyncTime);
            }
        );
    }
}