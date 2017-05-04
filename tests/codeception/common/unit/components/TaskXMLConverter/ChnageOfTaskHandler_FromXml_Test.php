<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/29/2017
 * Time: 2:58 PM
 */

use common\components\TaskXMLConverter;
use common\components\ConditionXMLConverter;
use common\nmodels\Condition;
use \common\components\BAException;

use Mockery as m;

class ChangeOfTaskHandler_FromXML_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testThrowInavlidRootElException()
    {
        $taskXMLStr = "<taskEl localId=\"1\" globalId=\"11\"></taskEl>";
        $taskXML = new SimpleXMLElement($taskXMLStr);

        $cConverterMock = m::mock(ConditionXMLConverter::class);
        $taskXMLConverter = new TaskXMLConverter($cConverterMock);

        $this->tester->expectException(
            new BAException(TaskXMLConverter::WRONG_ROOT_ELEMNT, BAException::WRONG_ROOT_XML_ELEMENT_NAME),
            function() use ($taskXMLConverter, $taskXML){
                $taskXMLConverter->fromXML($taskXML);
            }
        );
    }

    public function testConvertTask()
    {
        $taskXMLElement = new \SimpleXMLElement("<task localId=\"1\" globalId=\"101\">
                <message>Task 101 UPDATED(ACTIVE)</message>
                <description>Task 101 Desc</description>
                <status>ACTIVE</status>
                <conditions><condition /></conditions>
            </task>");

        $cConverter = m::mock(ConditionXMLConverter::class);
        $cConverter->shouldReceive('fromXML')->once()->andReturn(new Condition());

        $taskConverter = new TaskXMLConverter($cConverter);
        $res = $taskConverter->fromXML($taskXMLElement);
        $task = $res['task'];
        $this->assertNotNull($task, "Task must not be a null");
        $this->assertEquals(101, $task->id, "Wrong id");
        $this->assertEquals("Task 101 UPDATED(ACTIVE)", $task->message, "Wrong message");
        $this->assertEquals("Task 101 Desc", $task->description, "Wrong description");
        $this->assertEquals("ACTIVE", $task->status, "Wrong status");
    }
}