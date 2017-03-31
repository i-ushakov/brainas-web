<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 12/22/2016
 * Time: 11:00 AM
 */

use backend\components\ChangeOfTaskParser;

class ChangeOfTaskParserTest extends \Codeception\TestCase\Test {
    /**
     * @var UnitTester
     */
    protected $tester;

    private $changeOfTaskNewXMLString = "
            <changeOfTask localId=\"16\" globalId=\"0\">
                <task id=\"0\">dummy element</task>
                <change><status>CREATED</status><changeDatetime>2016-12-01 06:05:13</changeDatetime></change>
		    </changeOfTask>";

    private $changeOfTaskUpdatedXMLString = "
            <changeOfTask localId=\"16\" globalId=\"1425\">
                <task id=\"1425\">dummy element</task>
                <change><status>UPDATED</status><changeDatetime>2016-12-01 06:05:13</changeDatetime></change>
		    </changeOfTask>";

    private $changeOfTaskDeletedXMLString = "<changeOfTask localId=\"16\" globalId=\"1425\">
                <task id=\"1425\">dummy element</task>
                <change><status>DELETED</status><changeDatetime>2016-12-01 06:05:13</changeDatetime></change>
		    </changeOfTask>";
    protected function _before() {

    }


    public function testIsANewTask() {
        $parser = new ChangeOfTaskParser();
        $this->assertEquals(
            true,
            $parser->isANewTask(new \SimpleXMLElement($this->changeOfTaskNewXMLString)), "Task must be defined as a new"
        );
        $this->assertEquals(
            false,
            $parser->isANewTask(new \SimpleXMLElement($this->changeOfTaskUpdatedXMLString)), "Task must be defined as a NOT  new"
        );
        $this->assertEquals(
            false,
            $parser->isANewTask(new \SimpleXMLElement($this->changeOfTaskDeletedXMLString)), "Task must be defined as a NOT  new"
        );
    }

    public function testWasDeletedTask() {
        $parser = new ChangeOfTaskParser();
        $this->assertEquals(
            false,
            $parser->wasDeletedTask(new \SimpleXMLElement($this->changeOfTaskNewXMLString)), "Actualy the task was deleted"
        );

        $this->assertEquals(
            false,
            $parser->wasDeletedTask(new \SimpleXMLElement($this->changeOfTaskUpdatedXMLString)), "Actualy the task was deleted"
        );

        $this->assertEquals(
            true,
            $parser->wasDeletedTask(new \SimpleXMLElement($this->changeOfTaskDeletedXMLString)), "Actualy the task was deleted"
        );
    }



    public function testGetTimeOfChange()
    {
        $xml = new SimpleXMLElement('' .
            '<changeOfTask localId="1" globalId="10">' .
                '<change><status>CREATED</status><changeDatetime>2016-12-01 06:05:13</changeDatetime></change>' .
            '</changeOfTask>');
        $parser = new ChangeOfTaskParser();
        $timeOfChange = $parser->getTimeOfChange($xml);
        $this->assertEquals("2016-12-01 06:05:13", $timeOfChange);
    }
}