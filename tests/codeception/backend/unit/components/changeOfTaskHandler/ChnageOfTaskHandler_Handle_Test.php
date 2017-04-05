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
use common\components\BAException;

use Mockery as m;
use Codeception\Util\Stub;

class ChangeOfTaskHandler_Handle_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testThrowUserIdNotSetEx()
    {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);
        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $changeHandler = new ChangeOfTaskHandler($changeOfTaskParser, $taskXMLConverter);
        $this->tester->expectException(
            new BAException(
                ChangeOfTaskHandler::USER_ID_MUST_TO_BE_SET_MSG,
                BAException::PARAM_NOT_SET_EXCODE
            ),
            function() use ($changeHandler){
                $changeHandler->handle(new \SimpleXMLElement("<changeOfTask></changeOfTask>"));
            }
        );
    }

    public function testAddNewTask() {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);
        $changeParser = m::mock(ChangeOfTaskParser::class . '[isANewTask]');
        $changeParser->shouldReceive('isANewTask')
            ->once()->andReturn(true);
        $userId = 1;

        $changeHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[handleNewTask]',
            [$changeParser, $taskXMLConverter, $userId]
        );

        $changeHandler->shouldReceive('handleNewTask')
            ->times(1);

        $changeHandler->handle(new \SimpleXMLElement("<changeOfTask localId=\"16\" globalId=\"0\"></changeOfTask>"));
    }

    public function testUpdateExistTask()
    {
        $parser = m::mock(ChangeOfTaskParser::class);
        $parser->shouldReceive('isANewTask')->once()->andReturn(false);
        $converter = m::mock(TaskXMLConverter::class);
        $userId = 1;

        $changeHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[handleExistTask]',
            [$parser, $converter, $userId]
        );
        $changeHandler->shouldReceive('handleExistTask')->once();

        $changeHandler->handle(new \SimpleXMLElement("<changeOfTask localId=\"11\" globalId=\"101\"></changeOfTask>"));
    }

    public function testUpdateNonexistentTask()
    {
        $changeParser = Stub::make(
            ChangeOfTaskParser::class,
            array(
                'isANewTask' => Codeception\Util\Stub::exactly(1, function() {return false;}),
                'getGlobalId' => Codeception\Util\Stub::atLeastOnce(function() {return 1425;}),
                'getStatus' => Codeception\Util\Stub::never()
            ),$this
        );
        $taskConverter = new \common\nmodels\TaskXMLConverter(new \common\nmodels\ConditionXMLConverter());
        $userId = 1;
        $changeHandler = Stub::construct(ChangeOfTaskHandler::class,
            array($changeParser, $taskConverter, $userId),
            array(
                'isChangeOfTaskActual' => Codeception\Util\Stub::never(),
                'updateTask' => Codeception\Util\Stub::never()
            ), $this
        );

        $this->tester->dontSeeInDatabase('tasks', ['id' => '1425']);
        $this->assertEquals(
            1425,
            $changeHandler->handle(new \SimpleXMLElement("<changeOfTask />")), "Return value of handled task must be 1425"
        );
    }

    public function testDeleteTask()
    {
        $changeParser = Stub::make(
            ChangeOfTaskParser::class,
            array(
                'isANewTask' => Codeception\Util\Stub::exactly(1, function() {return false;}),
                'getGlobalId' => Codeception\Util\Stub::atLeastOnce(function() {return 1425;}),
                'getStatus' => Codeception\Util\Stub::exactly(1, function() {return 'DELETED';})
            ),$this
        );
        $taskConverter = new \common\nmodels\TaskXMLConverter(new \common\nmodels\ConditionXMLConverter());
        $userId = 1;
        $changeHandler = Stub::construct(ChangeOfTaskHandler::class,
            array($changeParser, $taskConverter, $userId),
            array(
                'isChangeOfTaskActual' => Codeception\Util\Stub::exactly(1, function () { return true; }),
                'deleteTask' => Codeception\Util\Stub::exactly(1, function () { return true; })
            ), $this
        );

        $this->tester->haveInDatabase('tasks', array(
            'id' => '1425', 'message' => 'Test', 'user' => 1, 'status' => 'TODO', 'created' => '2016-10-04 16:13:09', 'last_modify' => '2016-10-04 16:13:09'));

        $this->assertEquals(
            1425,
            $changeHandler->handle(new \SimpleXMLElement("<changeOfTask localId=\"16\" globalId=\"1425\">
                <task localId=\"16\" globalId=\"1425\">
                    <someTaskStuff/>
                </task>
                <change><status>UPDATED</status><changeDatetime>2016-12-01 06:05:13</changeDatetime></change>
		    </changeOfTask>")), "Return value of handled task must be 1425"
        );
    }
}