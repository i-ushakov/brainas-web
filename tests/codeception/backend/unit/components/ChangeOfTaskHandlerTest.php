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
use common\nmodels\TaskXMLConverter;
use common\infrastructure\ChangeOfTask;

use Mockery as m;

class ChangeOfTaskHandlerTest extends \Codeception\TestCase\Test {
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testUpdateTask() {
        $changeParser = Stub::make(
            ChangeOfTaskParser::class,
            array(
                //'getTimeOfChange' => Codeception\Util\Stub::exactly(1, function() {return '111';})
            ),$this
        );
        $taskConverter = new \common\nmodels\TaskXMLConverter(new \common\nmodels\ConditionXMLConverter());
        $userId = 1;
        $changeHandler = Stub::construct(ChangeOfTaskHandler::class,
            array($changeParser, $taskConverter, $userId),
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


    public function testLoggingChanges_created()
    {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);

        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $changeOfTaskParser->shouldReceive('getTimeOfChange')->once()->andReturn('2017-03-27 09:58:47');
        $changeOfTaskParser->shouldReceive('getGlobalId')->once()->andReturn(100);

        $userId = 1;

        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[savePistureOfTask]',
            [$changeOfTaskParser, $taskXMLConverter, $userId]
        );

        $chnageOfTaskXML = new SimpleXMLElement("<chnageOfTask/>");
        $type = "Created";

        $changeOfTaskHandler->loggingChanges($chnageOfTaskXML, $type);

        $this->tester->seeRecord(ChangeOfTask::class, [
            'user_id' => 1,
            'task_id' => 100,
            'datetime' => '2017-03-27 09:58:47',
            'action' => 'Created'
        ]);
    }

    public function testLoggingChanges_changed()
    {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);

        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $changeOfTaskParser->shouldReceive('getTimeOfChange')->once()->andReturn('2017-03-27 10:00:00');
        $changeOfTaskParser->shouldReceive('getGlobalId')->once()->andReturn(100);

        $userId = 1;

        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[savePistureOfTask]',
            [$changeOfTaskParser, $taskXMLConverter, $userId]
        );

        $chnageOfTaskXML = new SimpleXMLElement("<chnageOfTask/>");
        $type = "Changed";

        // have in database
        $changeOfTask = new ChangeOfTask(
            [
                'id' => 1,
                'user_id' => 1,
                'task_id' => 100,
                'action' => 'Created',
                'datetime' => '2017-03-27 08:48:47',
                'server_update_time' => '2017-03-27 08:59:47'
            ]);
        $changeOfTask->save();

        $changeOfTaskHandler->loggingChanges($chnageOfTaskXML, $type);

        $this->tester->dontSeeRecord(ChangeOfTask::class, [
            'user_id' => 1,
            'task_id' => 100,
            'action' => 'Created'
        ]);

        $this->tester->seeRecord(ChangeOfTask::class, [
            'user_id' => 1,
            'task_id' => 100,
            'datetime' => '2017-03-27 10:00:00',
            'action' => 'Changed'
        ]);

        $changeOfTask->delete();
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