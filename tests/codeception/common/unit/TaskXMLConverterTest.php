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
			    <picture>
			        <fileName>task_picture_1478350860389.png</fileName>
			        <resourceId>0B-nWSp4lPq2nZllKMldSbzRsLUE</resourceId>
		        </picture>
                <conditions>
                    <condition id=\"719\" type=\"TIME\">
                        <params>{\"offset\":180,\"datetime\":\"01-11-2016 13:34:08\"}</params>
                    </condition>
                </conditions>
		    </task>";

    public function testFromXML() {
        $taskXMLElement = new \SimpleXMLElement($this->taskXMLString);
        $conditionConverter = Stub::construct('\common\nmodels\ConditionXMLConverter',
            array(),
            array(
                'fromXML' => Codeception\Util\Stub::exactly(1, function () {return new \common\nmodels\Condition();})
            ), $this
        );
        //$conditionConverter = new \common\nmodels\ConditionXMLConverter();
        $taskConverter = new \common\nmodels\TaskXMLConverter($conditionConverter);
        $res = $taskConverter->fromXML($taskXMLElement);
        $task = $res['task'];
        $this->assertNotNull($task, "Task must not be a null");
        $this->assertEquals(1461, $task->id, "Wrong id");
        $this->assertEquals("task 15", $task->message, "Wrong message");
        $this->assertEquals("Description of time task", $task->description, "Wrong description");
        $this->assertEquals("ACTIVE", $task->status, "Wrong status");
        $conditions = $res['conditions'];
        $this->assertEquals(1461, $conditions[0]->task_id, "Wrong task id in condition");
        $picture = $res['picture'];
        $this->assertNotNull($picture);
        $this->assertEquals("task_picture_1478350860389.png", $picture->name, "Wrong picture name");
        $this->assertEquals("0B-nWSp4lPq2nZllKMldSbzRsLUE", $picture->file_id, "Wrong file_id of picture");
    }

    public function testFromXMLWithIncorrectXmlObj() {
        $taskXMLElement = new \SimpleXMLElement("<chandgedTask>" . $this->taskXMLString . "</chandgedTask>");
        $conditionConverter = new \common\nmodels\ConditionXMLConverter();
        $taskConverter = new \common\nmodels\TaskXMLConverter($conditionConverter);
        $this->tester->expectException('\common\components\BAException', function() use ($taskConverter, $taskXMLElement) {
                $taskConverter->fromXML($taskXMLElement);
            }
        );
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