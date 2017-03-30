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

class ChangeOfTaskHandler_HandleTest extends \Codeception\TestCase\Test
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

    public function testNewTask() {
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
}