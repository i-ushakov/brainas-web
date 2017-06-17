<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/29/2017
 * Time: 2:58 PM
 */

use backend\components\ChangeOfTaskParser;
use backend\components\ChangeOfTaskHandler;
use common\components\TaskXMLConverter;
use common\models\Task;
use common\models\Condition;

use Mockery as m;

class ChangeOfTaskHandler_AddTask_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testAddTask()
    {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);
        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $userId = 1;

        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[savePistureOfTask]',
            [$changeOfTaskParser, $taskXMLConverter, $userId]
        );
        $changeOfTaskHandler->shouldReceive('savePistureOfTask')->never();

        $task = m::mock(Task::class . '[save]');
        $task->id = 77;
        $task->shouldReceive('save')->times(1);

        $condition = m::mock(Condition::class . '[save]');
        $condition->shouldReceive('save')->times(1);

        $taskWithConditions = [
            'task' => $task,
            'conditions' => [$condition],
            'picture' => null
        ];

        $changeOfTaskHandler->addTask($taskWithConditions);

        $this->assertEquals($userId, $task->user, "User id must be 1");
        $this->assertEquals($task->id, $condition->task_id, "Task id must be 1");
    }
}