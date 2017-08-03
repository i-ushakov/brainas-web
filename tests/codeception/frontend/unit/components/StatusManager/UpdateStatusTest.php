<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 8/1/2017
 * Time: 5:08 PM
 */
use \frontend\components\StatusManager;
use \common\models\Task;
use \Mockery as m;
use \common\models\Condition;


class UpdateStatusTest extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testUpdateActiveTask()
    {
        $task = m::mock(Task::class . "[save]");
        $task->status = StatusManager::STATUS_ACTIVE;

        $statusManager = new StatusManager();
        $task = $statusManager->updateStatus($task);
        $this->tester->assertEquals(StatusManager::STATUS_ACTIVE, $task->status);
    }

    public function testUpdateWaitingTaskWithoutConditions()
    {
        $task = m::mock(Task::class . "[save, getConditions]");
        $task->status = StatusManager::STATUS_WAITING;
        $task->shouldReceive('getConditions')->once()->andReturn([]);

        $statusManager = new StatusManager();
        $task = $statusManager->updateStatus($task);
        $this->tester->assertEquals(StatusManager::STATUS_DISABLED, $task->status);
    }

    public function testUpdateWaitingTaskWithConditions()
    {
        $task = m::mock(Task::class . "[save, getConditions]");
        $task->status = StatusManager::STATUS_WAITING;
        $task->shouldReceive('getConditions')->once()->andReturn([new Condition()]);

        $statusManager = new StatusManager();
        $task = $statusManager->updateStatus($task);
        $this->tester->assertEquals(StatusManager::STATUS_WAITING, $task->status);
    }

    public function testUpdateTodoTaskWithConditions()
    {
        $task = m::mock(Task::class . "[save, getConditions]");
        $task->status = StatusManager::STATUS_TODO;
        $task->shouldReceive('getConditions')->once()->andReturn([new Condition()]);

        $statusManager = new StatusManager();
        $task = $statusManager->updateStatus($task);
        $this->tester->assertEquals(StatusManager::STATUS_WAITING, $task->status);
    }

    public function testUpdateTodoTaskWithoutConditions()
    {
        $task = m::mock(Task::class . "[save, getConditions]");
        $task->status = StatusManager::STATUS_TODO;
        $task->shouldReceive('getConditions')->once()->andReturn([]);

        $statusManager = new StatusManager();
        $task = $statusManager->updateStatus($task);
        $this->tester->assertEquals(StatusManager::STATUS_TODO, $task->status);
    }
}