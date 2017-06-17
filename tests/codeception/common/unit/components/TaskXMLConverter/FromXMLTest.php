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
use \common\models\Task;
use \common\models\Condition;

class FromXMLTest extends \Codeception\TestCase\Test {
    /**
     * @var UnitTester
     */
    protected $tester;

    public function testTaskWithoutPicture()
    {
        $taskXMLStr = "<task localId=\"1\" globalId=\"0\">
                <message>Task 1 ADDED(ACTIVE)</message>
                <description>Task 1 Desc</description>
                <status>ACTIVE</status>
            </task>";
        $taskXML = new SimpleXMLElement($taskXMLStr);

        $conditionConverter = m::mock(ConditionXMLConverter::class);
        $taskXMLConverter = new TaskXMLConverter($conditionConverter);

        // testing ...
        $r = $taskXMLConverter->fromXML($taskXML);
        $task = $r['task'];
        //$conditions = $r['conditions'];
        //$picture = $r['picture'];

        // assertions :
        $this->tester->assertInstanceOf(Task::class, $task);
        $this->tester->assertEquals('Task 1 ADDED(ACTIVE)', $task->message);
        $this->tester->assertEquals('Task 1 Desc', $task->description);
        $this->tester->assertEquals('ACTIVE', $task->status);
    }

    public function testTaskWithPicture()
    {
        //TODO
    }

    public function testIncorrectXmlObj() {
        $taskXMLElement = new \SimpleXMLElement("<chandgedTask><task></task></chandgedTask>");
        $conditionConverter = new ConditionXMLConverter();
        $taskConverter = new TaskXMLConverter($conditionConverter);
        $this->tester->expectException('\common\components\BAException', function() use ($taskConverter, $taskXMLElement) {
                $taskConverter->fromXML($taskXMLElement);
            }
        );
    }

    public function testWith1Condition() {
        $taskXMLString = "<task id=\"1461\">
			    <message>task 15</message>
			    <description>Description of time task</description>
			    <status>ACTIVE</status>
			    <picture>
			        <fileName>task_picture_1478350860389.png</fileName>
			        <resourceId>0B-nWSp4lPq2nZllKMldSbzRsLUE</resourceId>
		        </picture>
                <conditions>
                    <condition localId='14' globalId='714' type=\"TIME\"></condition>
                </conditions>
		    </task>";

        $taskXMLElement = new \SimpleXMLElement($taskXMLString);

        /* var $conditionConverter ConditionXMLConverter */
        $conditionConverter = m::mock(ConditionXMLConverter::class);
        $conditionConverter->shouldReceive('fromXML')
            ->once()->andReturn(new Condition());

        /* var $taskConverter TaskXMLConverter */
        $taskConverter = new TaskXMLConverter($conditionConverter);
        
        // testing ...
        $taskConverter->fromXML($taskXMLElement);
    }

    public function testWith2Conditions() {
        $taskXMLString = "<task id=\"1461\">
			    <message>task 15</message>
			    <description>Description of time task</description>
			    <status>ACTIVE</status>
			    <picture>
			        <fileName>task_picture_1478350860389.png</fileName>
			        <resourceId>0B-nWSp4lPq2nZllKMldSbzRsLUE</resourceId>
		        </picture>
                <conditions>
                    <condition localId='14' globalId='714' type='TIME'></condition>
                    <condition localId='15' globalId='715' type='LOCATION'></condition>
                </conditions>
		    </task>";

        $taskXMLElement = new \SimpleXMLElement($taskXMLString);

        /* var $conditionConverter ConditionXMLConverter */
        $conditionConverter = m::mock(ConditionXMLConverter::class);
        $conditionConverter->shouldReceive('fromXML')
            ->times(2)->andReturn(new Condition(), new Condition());

        /* var $taskConverter TaskXMLConverter */
        $taskConverter = new TaskXMLConverter($conditionConverter);

        // testing ...
        $taskConverter->fromXML($taskXMLElement);
    }
}