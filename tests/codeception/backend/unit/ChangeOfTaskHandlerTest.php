<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 12/22/2016
 * Time: 11:00 AM
 */

use Codeception\Util\Stub;

class ChangeOfTaskHandlerTest extends \Codeception\TestCase\Test {
    /**
     * @var UnitTester
     */
    protected $tester;

    private $changeOfTaskNewXMLString = "
            <changeOfTask localId=\"16\" globalId=\"0\">
                <task id=\"1461\">
			        <message>15</message>
			        <description/><status>ACTIVE</status>
			        <conditions>
				        <condition id=\"719\" type=\"TIME\">
					        <params>{\"offset\":180,\"datetime\":\"01-11-2016 13:34:08\"}</params>
				        </condition>
			        </conditions>
		        </task>
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


    public function testHandleNewTask() {
        $taskConverter = Stub::make(
            '\common\nmodels\TaskXMLConverter',
            array(
                'fromXML' => Codeception\Util\Stub::exactly(1, function() {})
            ),$this
        );

        $changeParser = Stub::make(
            '\backend\helpers\ChangeOfTaskParser',
            array(
                'isANewTask' => Codeception\Util\Stub::exactly(1, function() {return true;})
            ),$this
        );

        $changeHandler = Stub::construct('\backend\helpers\ChangeOfTaskHandler',
            array($changeParser, $taskConverter),
            array(
                'addTask' => Codeception\Util\Stub::exactly(1, function () { return 9999; })
            ), $this
        );

        $this->assertEquals(
            9999,
            $changeHandler->handle(new \SimpleXMLElement($this->changeOfTaskNewXMLString)), "Return value of handled task must be 9999"
        );
    }

    public function testHandleUpdatedTask() {
        $changeParser = Stub::make(
            '\backend\helpers\ChangeOfTaskParser',
            array(
                'isANewTask' => Codeception\Util\Stub::exactly(1, function() {return false;}),
                'getGlobalId' => Codeception\Util\Stub::atLeastOnce(function() {return 1425;}),
                'getStatus' => Codeception\Util\Stub::exactly(1, function() {return 'UPDATED';})
            ),$this
        );
        $taskConverter = new \common\nmodels\TaskXMLConverter(new \common\nmodels\ConditionXMLConverter());
        $changeHandler = Stub::construct('\backend\helpers\ChangeOfTaskHandler',
            array($changeParser, $taskConverter),
            array(
                'isChangeOfTaskActual' => Codeception\Util\Stub::exactly(1, function () { return true; }),
                'updateTask' => Codeception\Util\Stub::exactly(1, function () { return true; })
            ), $this
        );

        $this->tester->haveInDatabase('tasks', array(
            'id' => '1425', 'message' => 'Test', 'user' => 1, 'status' => 'TODO', 'created' => '2016-10-04 16:13:09'));
        $this->assertEquals(
            1425,
            $changeHandler->handle(new \SimpleXMLElement($this->changeOfTaskUpdatedXMLString)), "Return value of handled task must be 1425"
        );
    }

    public function testHandleUpdatedNonexistentTask() {
        $changeParser = Stub::make(
            '\backend\helpers\ChangeOfTaskParser',
            array(
                'isANewTask' => Codeception\Util\Stub::exactly(1, function() {return false;}),
                'getGlobalId' => Codeception\Util\Stub::atLeastOnce(function() {return 1425;}),
                'getStatus' => Codeception\Util\Stub::never()
            ),$this
        );
        $taskConverter = new \common\nmodels\TaskXMLConverter(new \common\nmodels\ConditionXMLConverter());
        $changeHandler = Stub::construct('\backend\helpers\ChangeOfTaskHandler',
            array($changeParser, $taskConverter),
            array(
                'isChangeOfTaskActual' => Codeception\Util\Stub::never(),
                'updateTask' => Codeception\Util\Stub::never()
            ), $this
        );

        $this->assertEquals(
            1425,
            $changeHandler->handle(new \SimpleXMLElement($this->changeOfTaskUpdatedXMLString)), "Return value of handled task must be 1425"
        );
    }

    public function testHandleDeletedTask() {
        $changeParser = Stub::make(
            '\backend\helpers\ChangeOfTaskParser',
            array(
                'isANewTask' => Codeception\Util\Stub::exactly(1, function() {return false;}),
                'getGlobalId' => Codeception\Util\Stub::atLeastOnce(function() {return 1425;}),
                'getStatus' => Codeception\Util\Stub::exactly(1, function() {return 'DELETED';})
            ),$this
        );
        $taskConverter = new \common\nmodels\TaskXMLConverter(new \common\nmodels\ConditionXMLConverter());
        $changeHandler = Stub::construct('\backend\helpers\ChangeOfTaskHandler',
            array($changeParser, $taskConverter),
            array(
                'isChangeOfTaskActual' => Codeception\Util\Stub::exactly(1, function () { return true; }),
                'deleteTask' => Codeception\Util\Stub::exactly(1, function () { return true; })
            ), $this
        );

        $this->tester->haveInDatabase('tasks', array(
            'id' => '1425', 'message' => 'Test', 'user' => 1, 'status' => 'TODO', 'created' => '2016-10-04 16:13:09'));

        $this->assertEquals(
            1425,
            $changeHandler->handle(new \SimpleXMLElement($this->changeOfTaskUpdatedXMLString)), "Return value of handled task must be 1425"
        );
    }

    //TODO addTask, getTimeOfTaskChange, isChangeOfTaskActual, updateTask
}