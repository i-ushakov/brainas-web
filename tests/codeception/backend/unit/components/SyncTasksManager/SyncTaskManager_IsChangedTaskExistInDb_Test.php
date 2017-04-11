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

class SyncTaskManager_IsChangedTaskExistInDb_Test extends \Codeception\TestCase\Test
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
        $changeOfTaskHandler->shouldReceive('setUserId');

        /* @var $tasksSyncManager TasksSyncManager*/
        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandler);
        $tasksSyncManager->setUserId(1);

        $change = m::mock(ChangeOfTask::class . "[getTask]");
        $change->shouldReceive('getTask')->andReturn(new Task());

        $this->tester->assertTrue($tasksSyncManager->isChangedTaskExistInDb($change));
    }
}