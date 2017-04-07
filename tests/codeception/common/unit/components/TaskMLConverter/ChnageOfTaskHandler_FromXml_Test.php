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
use common\components\ConditionXMLConverter;
use common\nmodels\Task;
use common\nmodels\Condition;

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