<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 12/1/2016
 * Time: 8:47 AM
 */

use Codeception\Util\Stub;

class TaskXMLConverterTest extends \Codeception\TestCase\Test {
    /**
     * @var UnitTester
     */
    protected $tester;

    private $taskXMLString = "<task id=\"1461\">
			    <message>task 15</message>
			    <description>Description of time task</description>
			    <status>ACTIVE</status>
                <conditions>
                    <condition id=\"719\" type=\"TIME\">
                        <params>{\"offset\":180,\"datetime\":\"01-11-2016 13:34:08\"}</params>
                    </condition>
                </conditions>
		    </task>";

    public function testConvertTask() {
        $taskXMLElement = new \SimpleXMLElement($this->taskXMLString);
        $conditionConverter = new \common\nmodels\ConditionXMLConverter();
        $taskConverter = new \common\nmodels\TaskXMLConverter($conditionConverter);
        $task = $taskConverter->fromXML($taskXMLElement)['task'];
        $this->assertNotNull($task, "Task must not be a null");
        $this->assertEquals(1461, $task->id, "Wrong id");
        $this->assertEquals("task 15", $task->message, "Wrong message");
        $this->assertEquals("Description of time task", $task->description, "Wrong description");
        $this->assertEquals("ACTIVE", $task->status, "Wrong status");
    }

    public function testConvertCondition() {
        $taskXMLElement = new \SimpleXMLElement($this->taskXMLString);
        $conditionConverter = Stub::make(
            '\common\nmodels\ConditionXMLConverter',
            array(
                'fromXML' => Codeception\Util\Stub::exactly(1, function() {return new common\nmodels\Condition();})
            ),$this
        );
        $taskConverter = new \common\nmodels\TaskXMLConverter($conditionConverter);
        $taskConverter->fromXML($taskXMLElement);
    }

    public function testConvertTaskWith2Condition() {
        $taskXMLElement = new \SimpleXMLElement($this->taskXMLString);
        // add one more conditon of LOCATION type
        $secondCondition = $taskXMLElement->conditions->addChild(
            'condition',
            "<params>{\"offset\":180,\"datetime\":\"01-11-2016 13:34:08\"}</params>"
        );
        $secondCondition->addAttribute('id', 1462);
        $secondCondition->addAttribute('type', 'LOCATION');

        $conditionConverter = Stub::make(
            '\common\nmodels\ConditionXMLConverter',
            array(
                'fromXML' => Codeception\Util\Stub::exactly(2, function() {return new common\nmodels\Condition();})
            ),$this
        );
        $taskConverter = new \common\nmodels\TaskXMLConverter($conditionConverter);
        $taskConverter->fromXML($taskXMLElement);
    }
}