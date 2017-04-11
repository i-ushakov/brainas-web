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
use common\nmodels\Task;


use Mockery as m;
use Codeception\Util\Stub;

class SyncTaskManager_GetChangesOfTasks_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }



    public function test() {
        $changeOfTaskHandler = \Mockery::mock(ChangeOfTaskHandler::class);

        $tasksSyncManager = \Mockery::mock(
            TasksSyncManager::class . '[retrieveChangesOfTasksFromDB, isChangedTaskExistInDb]',
            [$changeOfTaskHandler]
        );

        $lastSyncTime = '2017-02-04 00:00:00';

        /* @var $change1 ChangeOfTask */
        $task1 = m::mock(Task::class);
        $change1 = m::mock(ChangeOfTask::class . "[getTask]");
        $change1->task_id = 101;
        $change1->action = ChangeOfTask::STATUS_CREATED;
        $change1->datetime = '2017-01-04 00:00:00';
        $change1->shouldReceive('getTask')->andReturn($task1);

        /* @var $change2 ChangeOfTask */
        $task2 = m::mock(Task::class);
        $change2 = m::mock(ChangeOfTask::class . "[getTask]");
        $change2->task_id = 102;
        $change2->action = ChangeOfTask::STATUS_UPDATED;
        $change2->datetime = '2017-01-05 00:00:00';
        $change2->shouldReceive('getTask')->andReturn($task2);

        $tasksSyncManager->shouldReceive('retrieveChangesOfTasksFromDB')->once()
            ->with($lastSyncTime)->andReturn([$change1, $change2]);

        $tasksSyncManager->shouldReceive('isChangedTaskExistInDb')->times(2)
            ->andReturn(true, true);

        $chnagedTasks = $tasksSyncManager->getChangesOfTasks($lastSyncTime);

        // created
        $this->tester->assertTrue(isset($chnagedTasks['created']),
            "['created'] subset have to be defined");
        $this->tester->assertTrue(isset($chnagedTasks['created'][101]),
            "['created'][101] subset have to be defined");
        $this->tester->assertTrue(isset($chnagedTasks['created'][101]['action']),
            "['created'][101]['action'] subset have to be defined");
        $this->tester->assertEquals(
            ChangeOfTask::STATUS_CREATED,
            $chnagedTasks['created'][101]['action'],
            "chnagedTasks['created'][101]['action'] have to be ChangeOfTask::STATUS_CREATED");
        $this->tester->assertEquals(
            '2017-01-04 00:00:00',
            $chnagedTasks['created'][101]['datetime'],
            "chnagedTasks['created'][101]['datetime'] have to be '2017-01-04 00:00:00'");
        $this->tester->assertEquals(
            $task1,
            $chnagedTasks['created'][101]['object'],
            "chnagedTasks['created'][101]['object'] have to be task1");

        // updated
        $this->tester->assertTrue(isset($chnagedTasks['updated']),
            "['updated'] subset have to be defined");
        $this->tester->assertTrue(isset($chnagedTasks['updated'][102]),
            "['created'][101] subset have to be defined");
        $this->tester->assertTrue(isset($chnagedTasks['updated'][102]['action']),
            "['updated'][102]['action'] subset have to be defined");
        $this->tester->assertEquals(
            ChangeOfTask::STATUS_UPDATED,
            $chnagedTasks['updated'][102]['action'],
            "chnagedTasks['updated'][102]['action'] have to be ChangeOfTask::STATUS_CREATED");
        $this->tester->assertEquals(
            '2017-01-05 00:00:00',
            $chnagedTasks['updated'][102]['datetime'],
            "chnagedTasks['updated'][102]['datetime'] have to be '2017-01-05 00:00:00'");
        $this->tester->assertEquals(
            $task2,
            $chnagedTasks['updated'][102]['object'],
            "chnagedTasks['updated'][102]['object'] have to be task2");
    }
}