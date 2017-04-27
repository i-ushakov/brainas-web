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

use Mockery as m;

class ChangeOfTaskHandler_HandleNewTask_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }
    public function testHandleNewTask()
    {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);
        $taskXMLConverter->shouldReceive('fromXML')
            ->times(1)
            ->andReturn([
                'task' =>  null,
                'conditions' => [],
                'picture' => null
            ]);
        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $userId = 1;

        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[addTask, loggingChanges]',
            [$changeOfTaskParser, $taskXMLConverter, $userId]
        );
        $changeOfTaskHandler->shouldReceive('addTask')
            ->times(1)
            ->andReturn(100);
        $changeOfTaskHandler->shouldReceive('loggingChanges')
            ->times(1);

        $changeOfTaskHandler->handleNewTask(new SimpleXMLElement("<chnageOfTaskXML/>"));
    }
}