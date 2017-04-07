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
use common\infrastructure\ChangeOfTask;

use Mockery as m;

class ChangeOfTaskHandler_HandleExistTask_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }
    public function testHandleExistTask()
    {
        $converter = m::mock(TaskXMLConverter::class);
        $converter->shouldReceive('fromXML')
            ->times(1)
            ->andReturn([
                'task' =>  null,
                'conditions' => [],
                'picture' => null
            ]);
        $parser = m::mock(ChangeOfTaskParser::class);
        $parser->shouldReceive('getGlobalId')->once()->andReturn(101);
        $parser->shouldReceive('getStatus')->once()->andReturn('UPDATED');

        $userId = 1;

        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[isActualChange, updateTask, loggingChanges]',
            [$parser, $converter, $userId]
        );
        $changeOfTaskHandler->shouldReceive('isActualChange')->once()->andReturn(true);
        $changeOfTaskHandler->shouldReceive('updateTask')->once()->andReturn(101);
        $changeOfTaskHandler->shouldReceive('loggingChanges')->once()
            ->with(SimpleXMLElement::class, ChangeOfTask::STATUS_UPDATED);

        $this->tester->haveInDatabase('tasks', array(
            'id' => '101',
            'message' => 'Task 101',
            'user' => 1,
            'status' => 'TODO',
            'created' => '2016-10-04 16:13:09',
            'last_modify' => '2016-10-04 16:13:09'));

        $taskId = $changeOfTaskHandler->handleExistTask(new SimpleXMLElement("<chnageOfTaskXML/>"));
        $this->tester->assertEquals(101,$taskId);
    }
}