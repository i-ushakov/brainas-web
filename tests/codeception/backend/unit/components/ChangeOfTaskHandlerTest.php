<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 12/22/2016
 * Time: 11:00 AM
 */

use Codeception\Util\Stub;
use backend\components\ChangeOfTaskParser;
use backend\components\ChangeOfTaskHandler;

class ChangeOfTaskHandlerTest extends \Codeception\TestCase\Test {
    /**
     * @var UnitTester
     */
    protected $tester;

    private $changeOfTaskNewXMLString = "
            <changeOfTask localId=\"16\" globalId=\"0\">
                <task localId=\"16\" globalId=\"0\">
			        <someTaskStuff/>
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
            ChangeOfTaskParser::class,
            array(
                'isANewTask' => Codeception\Util\Stub::exactly(1, function() {return true;}),
                //'getTimeOfChange' => Codeception\Util\Stub::exactly(1, function() {return "2016-09-15 18:08:09";})
            ),$this
        );

        $changeHandler = Stub::construct(ChangeOfTaskHandler::class,
            array($changeParser, $taskConverter, 1),
            array(
                'addTask' => Codeception\Util\Stub::exactly(1, function () { return 9999; }),
                'loggingChanges' => Codeception\Util\Stub::exactly(1, function () { return true; })
            ), $this
        );

        $this->assertEquals(
            9999,
            $changeHandler->handle(new \SimpleXMLElement($this->changeOfTaskNewXMLString)), "Return value of handled task must be 9999"
        );
    }

    public function testHandleUpdatedTask() {
        $changeParser = Stub::make(
            ChangeOfTaskParser::class,
            array(
                'isANewTask' => Codeception\Util\Stub::exactly(1, function() {return false;}),
                'getGlobalId' => Codeception\Util\Stub::atLeastOnce(function() {return 1425;}),
                'getStatus' => Codeception\Util\Stub::exactly(1, function() {return 'UPDATED';})
            ),$this
        );
        $taskConverter = new \common\nmodels\TaskXMLConverter(new \common\nmodels\ConditionXMLConverter());
        $changeHandler = Stub::construct(ChangeOfTaskHandler::class,
            array($changeParser, $taskConverter, 1),
            array(
                'isChangeOfTaskActual' => Codeception\Util\Stub::exactly(1, function () { return true; }),
                'updateTask' => Codeception\Util\Stub::exactly(1, function () { return 1425; }),
                'loggingChanges' => Codeception\Util\Stub::exactly(1, function () {})
            ), $this
        );

        $this->tester->haveInDatabase('tasks', array(
            'id' => '1425', 'message' => 'Test', 'user' => 1, 'status' => 'TODO', 'created' => '2016-10-04 16:13:09', 'last_modify' => '2016-10-04 16:13:09'));
        $this->assertEquals(
            1425,
            $changeHandler->handle(new \SimpleXMLElement($this->changeOfTaskUpdatedXMLString)), "Return value of handled task must be 1425"
        );
    }

    public function testHandleUpdatedNonexistentTask() {
        $changeParser = Stub::make(
            ChangeOfTaskParser::class,
            array(
                'isANewTask' => Codeception\Util\Stub::exactly(1, function() {return false;}),
                'getGlobalId' => Codeception\Util\Stub::atLeastOnce(function() {return 1425;}),
                'getStatus' => Codeception\Util\Stub::never()
            ),$this
        );
        $taskConverter = new \common\nmodels\TaskXMLConverter(new \common\nmodels\ConditionXMLConverter());
        $changeHandler = Stub::construct(ChangeOfTaskHandler::class,
            array($changeParser, $taskConverter, 1),
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
            ChangeOfTaskParser::class,
            array(
                'isANewTask' => Codeception\Util\Stub::exactly(1, function() {return false;}),
                'getGlobalId' => Codeception\Util\Stub::atLeastOnce(function() {return 1425;}),
                'getStatus' => Codeception\Util\Stub::exactly(1, function() {return 'DELETED';})
            ),$this
        );
        $taskConverter = new \common\nmodels\TaskXMLConverter(new \common\nmodels\ConditionXMLConverter());
        $changeHandler = Stub::construct(ChangeOfTaskHandler::class,
            array($changeParser, $taskConverter, 1),
            array(
                'isChangeOfTaskActual' => Codeception\Util\Stub::exactly(1, function () { return true; }),
                'deleteTask' => Codeception\Util\Stub::exactly(1, function () { return true; })
            ), $this
        );

        $this->tester->haveInDatabase('tasks', array(
            'id' => '1425', 'message' => 'Test', 'user' => 1, 'status' => 'TODO', 'created' => '2016-10-04 16:13:09', 'last_modify' => '2016-10-04 16:13:09'));

        $this->assertEquals(
            1425,
            $changeHandler->handle(new \SimpleXMLElement($this->changeOfTaskUpdatedXMLString)), "Return value of handled task must be 1425"
        );
    }

    public function testAddTask() {
        $changeParser = Stub::make(
            ChangeOfTaskParser::class,
            array(
                //'getTimeOfChange' => Codeception\Util\Stub::exactly(1, function() {return '111';})
            ),$this
        );
        $taskConverter = new \common\nmodels\TaskXMLConverter(new \common\nmodels\ConditionXMLConverter());
        $changeHandler = Stub::construct(ChangeOfTaskHandler::class,
            array($changeParser, $taskConverter, 1),
            array(
                'savePistureOfTask' => Codeception\Util\Stub::exactly(1, function () { return true; })
            ), $this
        );
        $task = Stub::construct(
            '\common\nmodels\Task',
            array(),
            array(
                'id' => '777',
                'message' => 'test',
                'user' => 1
            ),$this
        );
        $condition = Stub::construct(
            '\common\nmodels\Condition',
            array(),
            array(
                'id' => '1777',
                'type' => 1,
                'params' => '{json:params}'
            ),$this
        );
        $picture = Stub::construct(
            '\common\models\PictureOfTask',
            array(),
            array(
                'name' => 'task_img_1234567890.jpg',
                'drive_id' => 'DriveId:CAESABi0DSD-iK_fmFIoAA==',
                'file_id' => '0B-nWSp4lPq2nb3NjbnIyaTZYSWc'
            ),$this
        );

        $conditions = array($condition);
        $taskWithConditions = array('task' => $task, 'conditions' => $conditions, 'picture' => $picture);
        $changeHandler->addTask($taskWithConditions);
        $this->tester->seeRecord('common\nmodels\Task', array( 'id' => '777', 'user' => 1));
        $this->tester->seeRecord('common\nmodels\Condition', array('id' => '1777', 'task_id' => 777));
        //$this->tester->seeRecord('common\models\PictureOfTask', array('task_id' => 777, 'name' => 'task_img_1234567890.jpg'));
    }


    public function testUpdateTask() {
        $changeParser = Stub::make(
            ChangeOfTaskParser::class,
            array(
                //'getTimeOfChange' => Codeception\Util\Stub::exactly(1, function() {return '111';})
            ),$this
        );
        $taskConverter = new \common\nmodels\TaskXMLConverter(new \common\nmodels\ConditionXMLConverter());
        $changeHandler = Stub::construct(ChangeOfTaskHandler::class,
            array($changeParser, $taskConverter, 1),
            array(
                'savePistureOfTask' => Codeception\Util\Stub::exactly(1, function () { return true; }),
                'cleanDeletedConditions' => Codeception\Util\Stub::exactly(1, function () { return true; }),
            ), $this
        );
        $task = Stub::construct(
            '\common\nmodels\Task',
            array(),
            array('id' => 779, 'message' => 'test_updated', 'user' => 1, 'status' => 'ACTIVE', 'last_modify' => '2017-01-02 20:07:44'
            ),$this
        );
        $condition1 = Stub::construct(
            '\common\nmodels\Condition',
            array(),
            array('id' => 1779, 'type' => 1, 'params' => '{json:params2}', 'task_id' => 779),
            $this
        );
        $condition2 = Stub::construct(
            '\common\nmodels\Condition',
            array(),
            array('type' => 1, 'params' => '{json:params3}', 'task_id' => 779),
            $this
        );
        $picture = Stub::construct('\common\models\PictureOfTask', array(), array(),$this);

        $taskInDatabase = new \common\nmodels\Task();
        $taskInDatabase->id = 779;
        $taskInDatabase->user = 1;
        $taskInDatabase->message = 'test';
        $taskInDatabase->status = 'ACTIVE';
        $taskInDatabase->created = '2017-01-02 20:07:44';
        $taskInDatabase->last_modify = '2017-01-02 20:07:44';
        $taskInDatabase->save();

        $conditionInDatabase = new \common\nmodels\Condition();
        $conditionInDatabase->id = 1779;
        $conditionInDatabase->task_id = 1;
        $conditionInDatabase->type = 1;
        $conditionInDatabase->params = '{json:params1}';
        $conditionInDatabase->save();

        $conditions = array($condition1, $condition2);
        $taskWithConditions = array('task' => $task, 'conditions' => $conditions, 'picture' => $picture);
        $changeHandler->updateTask($taskWithConditions);
        $this->tester->seeRecord('common\nmodels\Task', array( 'id' => '779', 'user' => 1, 'message' => 'test_updated'));
        $this->tester->seeRecord('common\nmodels\Condition', array('id' => '1779', 'task_id' => 779, 'params' => '{json:params2}'));
        $this->tester->seeRecord('common\nmodels\Condition', array('task_id' => 779, 'params' => '{json:params3}'));
    }

    /*
     * Checking security for case when client substituted the task id
     */
    public function testUpdateTaskWithForeignTaskId() {
        // TODO
    }

    public function getTimeOfTaskChange() {
        // TODO
    }

    public function isChangeOfTaskActual() {
        // TODO
    }


    public function loggingChanges() {
        // TODO
    }

    public function testSavePistureOfTask() {
        // TODO
        $picture = Stub::construct(
            '\common\models\PictureOfTask',
            array(),
            array(
                'task_id' => 779,
                'name' => 'task_img_1234567890.jpg',
                'drive_id' => 'DriveId:CAESABi0DSD-iK_fmFIoAA==',
                'file_id' => '0B-nWSp4lPq2nb3NjbnIyaTZYSWc'
            ),$this
        );
        //$this->tester->seeRecord('common\models\PictureOfTask', array('task_id' => 777, 'name' => 'task_img_1234567890.jpg'));
    }

    public function testCleanDeletedCondition() {
        // TODO
    }

}