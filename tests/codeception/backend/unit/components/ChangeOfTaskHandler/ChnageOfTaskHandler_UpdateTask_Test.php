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
            ChangeOfTaskHandler::class . '[cleanDeletedConditions, updateConditions]',
            [$changeOfTaskParser, $taskXMLConverter, $userId]
        );
        $changeOfTaskHandler->shouldReceive('cleanDeletedConditions')->once();
        $changeOfTaskHandler->shouldReceive('updateConditions')->once();

        $taskFromDevice = new Task([
            'id' => 88,
            'user' => 1,
            'message' => 'Task 88 (UPDATED)',
            'status' => 'ACTIVE',
        ]);

        /* var $conditionFromDevice1 Condition */
        $conditionFromDevice1 = new Condition([]);

        /* var $conditionFromDevice2 Condition */
        $conditionFromDevice2 = new Condition([]);

        $conditionsFromDevice = [$conditionFromDevice1, $conditionFromDevice2];

        $taskWithConditions = [
            'task' => $taskFromDevice,
            'conditions' => [$conditionsFromDevice],
            'picture' => null
        ];

        // Preparing DB
        $existsTask = new Task([
            'id' => 88,
            'user' => 1,
            'message' => 'Task 88',
            'last_modify' => '2017-04-02 10:54:44'
        ]);
        $existsTask->save();

        // testing ...
        $taskId = $changeOfTaskHandler->updateTask($taskWithConditions);

        // assertions:
        $this->assertEquals(88, $taskId);
        $updatedTask = Task::findOne(['id' => $taskId]);
        $this->tester->assertEquals("Task 88 (UPDATED)", $updatedTask->message);
        $this->tester->assertEquals("ACTIVE", $updatedTask->status);
        $this->tester->assertTrue(strtotime($updatedTask->last_modify) > strtotime('2017-04-02 10:54:44'));
    }

    public function testCheckSecureIssueUserIdSubstitution()
    {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);
        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $userId = 2;

        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[cleanDeletedConditions]',
            [$changeOfTaskParser, $taskXMLConverter, $userId]
        );
        $changeOfTaskHandler->shouldReceive('cleanDeletedConditions')->never();

        $taskFromDevice = new Task([
            'id' => 88,
            'user' => 2, // Wrong user if (maybe russian hackers)
            'message' => 'Task 88 (UPDATED)',
            'status' => 'ACTIVE',
        ]);

        $condition = m::mock(Condition::class);

        $taskWithConditions = [
            'task' => $taskFromDevice,
            'conditions' => [$condition],
            'picture' => null
        ];

        $existsTask = new Task([
            'id' => 88,
            'user' => 1,
            'message' => 'Task 88',
            'status' => 'TODO',
        ]);
        $existsTask->save();

        $taskId = $changeOfTaskHandler->updateTask($taskWithConditions);

        $this->assertEquals(null, $taskId);
        $updatedTask = Task::findOne(['id' => 88, 'user' => 1]);
        $this->tester->assertEquals("Task 88", $updatedTask->message);
        $this->tester->assertEquals("TODO", $updatedTask->status);
    }
}