<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/29/2017
 * Time: 2:58 PM
 */

use backend\components\ChangeOfTaskParser;
use backend\components\ChangeOfTaskHandler;
use common\nmodels\TaskXMLConverter;
use common\nmodels\Task;
use common\nmodels\Condition;

use Mockery as m;

class ChangeOfTaskHandler_UpdateTask_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testUpdateTask()
    {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);
        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $userId = 1;

        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[cleanDeletedConditions]',
            [$changeOfTaskParser, $taskXMLConverter, $userId]
        );
        $changeOfTaskHandler->shouldReceive('cleanDeletedConditions')->once();

        $task = new Task();
        $task->id = 88;
        $task->user = 1;
        $task->message = "Task 88 (UPDATED)";
        $task->status = "ACTIVE";

        $condition = m::mock(Condition::class);

        $taskWithConditions = [
            'task' => $task,
            'conditions' => [$condition],
            'picture' => null
        ];

        $existsTask = new Task([
            'id' => 88,
            'user' => 1,
            'message' => 'Task 88',
            'last_modify' => '2017-04-02 10:54:44'
        ]);
        $existsTask->save();

        $taskId = $changeOfTaskHandler->updateTask($taskWithConditions);

        $this->assertEquals(88, $taskId);

        $updatedTask = Task::findOne(['id' => $taskId]);
        $this->tester->assertEquals("Task 88 (UPDATED)", $updatedTask->message);
        $this->tester->assertEquals("ACTIVE", $updatedTask->status);
        $this->tester->assertTrue(strtotime($updatedTask->last_modify) > strtotime('2017-04-02 10:54:44'));
    }

    public function testCheckSecureIssueUserIdSubstitution()
    {

    }
}