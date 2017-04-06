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
use common\infrastructure\ChangeOfTask;
use common\components\BAException;

use Mockery as m;

class ChangeOfTaskHandler_LoggingChanges_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testLoggingCreationOfTask()
    {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);

        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $changeOfTaskParser->shouldReceive('getClientTimeOfChanges')->once()
            ->andReturn('2017-03-27 09:58:47');
        $changeOfTaskParser->shouldReceive('getGlobalId')->never();

        $userId = 1;
        $taskId = 100;

        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[savePistureOfTask]',
            [$changeOfTaskParser, $taskXMLConverter, $userId]
        );

        $chnageOfTaskXML = new SimpleXMLElement("<chnageOfTask/>");
        $type = "CREATED";

        $changeOfTaskHandler->loggingChanges($chnageOfTaskXML, $type, $taskId);

        $this->tester->seeRecord(ChangeOfTask::class, [
            'user_id' => 1,
            'task_id' => 100,
            'datetime' => '2017-03-27 09:58:47',
            'action' => 'CREATED'
        ]);
    }

    public function testLoggingChangeOfTask()
    {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);

        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $changeOfTaskParser->shouldReceive('getClientTimeOfChanges')->once()
            ->andReturn('2017-03-27 10:00:00');
        $changeOfTaskParser->shouldReceive('getGlobalId')->once()->andReturn(100);

        $userId = 1;

        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[savePistureOfTask]',
            [$changeOfTaskParser, $taskXMLConverter, $userId]
        );

        $chnageOfTaskXML = new SimpleXMLElement("<chnageOfTask/>");
        $type = "UPDATED";

        // have in database
        $changeOfTask = new ChangeOfTask(
            [
                'id' => 1,
                'user_id' => 1,
                'task_id' => 100,
                'action' => 'CREATED',
                'datetime' => '2017-03-27 08:48:47',
                'server_update_time' => '2017-03-27 08:59:47'
            ]);
        $changeOfTask->save();

        $changeOfTaskHandler->loggingChanges($chnageOfTaskXML, $type);

        $this->tester->dontSeeRecord(ChangeOfTask::class, [
            'user_id' => 1,
            'task_id' => 100,
            'action' => 'CREATED'
        ]);

        $this->tester->seeRecord(ChangeOfTask::class, [
            'user_id' => 1,
            'task_id' => 100,
            'datetime' => '2017-03-27 10:00:00',
            'action' => 'UPDATED'
        ]);

        $changeOfTask->delete();
    }

    public function testThrowExceptionThatTaskIdMustToBeKnow()
    {
        $converter = m::mock(TaskXMLConverter::class);

        $parser = m::mock(ChangeOfTaskParser::class);
        $parser->shouldReceive('getClientTimeOfChanges')->once()
            ->andReturn('2017-03-27 09:58:47');
        $parser->shouldReceive('getGlobalId')->once()->andReturn(0);

        $userId = 1;

        $handler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[]',
            [$parser, $converter, $userId]
        );

        $chnageXML = new SimpleXMLElement("<chnageOfTask/>");
        $type = "CREATED";

        $this->tester->expectException(
            new BAException(
                ChangeOfTaskHandler::TASK_ID_MUST_TO_BE_KNOWN_MSG,
                BAException::PARAM_NOT_SET_EXCODE
            ),
            function() use ($handler, $chnageXML, $type){
                $handler->loggingChanges($chnageXML, $type);
            }
        );
    }
}