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

use Mockery as m;

class ChangeOfTaskHandler_IsActualChange_Test extends \Codeception\TestCase\Test
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
        $converter = m::mock(TaskXMLConverter::class);

        $parser = m::mock(ChangeOfTaskParser::class);
        $parser->shouldReceive('getGlobalId')->once()->andReturn(101);
        $parser->shouldReceive('getClientTimeOfChanges')->once()
            ->with(SimpleXMLElement::class)->andReturn('2017-03-27 00:00:00');

        $userId = 1;

        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[getServerTimeOfChanges]',
            [$parser, $converter, $userId]
        );
        $changeOfTaskHandler->shouldReceive('getServerTimeOfChanges')->once()
            ->with(101)->andReturn('2017-03-26 08:00:00');

        $chnageOfTaskXML = new SimpleXMLElement("<chnageOfTask/>");

        $this->tester->assertTrue($changeOfTaskHandler->isActualChange($chnageOfTaskXML));
    }
}