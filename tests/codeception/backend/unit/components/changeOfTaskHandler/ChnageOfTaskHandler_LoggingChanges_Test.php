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
        $changeOfTaskParser->shouldReceive('getTimeOfChange')->once()->andReturn('2017-03-27 09:58:47');
        $changeOfTaskParser->shouldReceive('getGlobalId')->once()->andReturn(100);

        $userId = 1;

        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[savePistureOfTask]',
            [$changeOfTaskParser, $taskXMLConverter, $userId]
        );

        $chnageOfTaskXML = new SimpleXMLElement("<chnageOfTask/>");
        $type = "Created";

        $changeOfTaskHandler->loggingChanges($chnageOfTaskXML, $type);

        $this->tester->seeRecord(ChangeOfTask::class, [
            'user_id' => 1,
            'task_id' => 100,
            'datetime' => '2017-03-27 09:58:47',
            'action' => 'Created'
        ]);
    }

    public function testLoggingChangeOfTask()
    {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);

        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $changeOfTaskParser->shouldReceive('getTimeOfChange')->once()->andReturn('2017-03-27 10:00:00');
        $changeOfTaskParser->shouldReceive('getGlobalId')->once()->andReturn(100);

        $userId = 1;

        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[savePistureOfTask]',
            [$changeOfTaskParser, $taskXMLConverter, $userId]
        );

        $chnageOfTaskXML = new SimpleXMLElement("<chnageOfTask/>");
        $type = "Changed";

        // have in database
        $changeOfTask = new ChangeOfTask(
            [
                'id' => 1,
                'user_id' => 1,
                'task_id' => 100,
                'action' => 'Created',
                'datetime' => '2017-03-27 08:48:47',
                'server_update_time' => '2017-03-27 08:59:47'
            ]);
        $changeOfTask->save();

        $changeOfTaskHandler->loggingChanges($chnageOfTaskXML, $type);

        $this->tester->dontSeeRecord(ChangeOfTask::class, [
            'user_id' => 1,
            'task_id' => 100,
            'action' => 'Created'
        ]);

        $this->tester->seeRecord(ChangeOfTask::class, [
            'user_id' => 1,
            'task_id' => 100,
            'datetime' => '2017-03-27 10:00:00',
            'action' => 'Changed'
        ]);

        $changeOfTask->delete();
    }
}