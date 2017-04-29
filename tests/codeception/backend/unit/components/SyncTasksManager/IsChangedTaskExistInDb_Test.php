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

class IsChangedTaskExistInDb_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function isChangedTaskExistInDb() {
        $changeOfTaskHandler = \Mockery::mock(ChangeOfTaskHandler::class);
        $changeOfTaskHandler->shouldReceive('setUserId');

        /* @var $responseBuilder XMLResponseBuilder */
        $responseBuilder =  new XMLResponseBuilder();

        /* @var $tasksSyncManager TasksSyncManager*/
        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandler, $responseBuilder);
        $tasksSyncManager->setUserId(1);

        // testing ...
        $change = m::mock(ChangeOfTask::class . "[getTask]");
        $change->shouldReceive('getTask')->andReturn(new Task());

        // assertions:
        $this->tester->assertTrue($tasksSyncManager->isChangedTaskExistInDb($change));
    }
}