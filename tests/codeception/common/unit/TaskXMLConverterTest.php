<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 12/1/2016
 * Time: 8:47 AM
 */

use Codeception\Util\Stub;
use Mockery as m;
use \common\components\TaskXMLConverter;
use \common\components\ConditionXMLConverter;
use \common\nmodels\Task;
use \common\components\BAException;

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

    public function testFromXML_InavlidRootElException()
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

    public function testFromXML_TaskWithoutPicture()
    {
        $taskXMLStr = "<task localId=\"1\" globalId=\"0\">
                <message>Task 1 ADDED(ACTIVE)</message>
                <description>Task 1 Desc</description>
                <status>ACTIVE</status>
            </task>";
        $taskXML = new SimpleXMLElement($taskXMLStr);

        $cConverterMock = m::mock(ConditionXMLConverter::class);
        $taskXMLConverter = new TaskXMLConverter($cConverterMock);


        $r = $taskXMLConverter->fromXML($taskXML);
        $task = $r['task'];
        //$conditions = $r['conditions'];
        //$picture = $r['picture'];
        $this->tester->assertInstanceOf(Task::class, $task);
        $this->tester->assertEquals('Task 1 ADDED(ACTIVE)', $task->message);
        $this->tester->assertEquals('Task 1 Desc', $task->description);
        $this->tester->assertEquals('ACTIVE', $task->status);
    }

    public function testFromXML_TaskWithPicture()
    {
        //TODO
    }

    public function testFromXMLWithIncorrectXmlObj() {
        $taskXMLElement = new \SimpleXMLElement("<chandgedTask>" . $this->taskXMLString . "</chandgedTask>");
        $conditionConverter = new ConditionXMLConverter();
        $taskConverter = new TaskXMLConverter($conditionConverter);
        $this->tester->expectException('\common\components\BAException', function() use ($taskConverter, $taskXMLElement) {
                $taskConverter->fromXML($taskXMLElement);
            }
        );
    }

    public function testConvertCondition() {
        $taskXMLElement = new \SimpleXMLElement($this->taskXMLString);
        $conditionConverter = Stub::make(
            '\common\components\ConditionXMLConverter',
            array(
                'fromXML' => Codeception\Util\Stub::exactly(1, function() {return new common\nmodels\Condition();})
            ),$this
        );
        $taskConverter = new TaskXMLConverter($conditionConverter);
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
            '\common\components\ConditionXMLConverter',
            array(
                'fromXML' => Codeception\Util\Stub::exactly(2, function() {return new common\nmodels\Condition();})
            ),$this
        );
        $taskConverter = new TaskXMLConverter($conditionConverter);
        $taskConverter->fromXML($taskXMLElement);
    }
}