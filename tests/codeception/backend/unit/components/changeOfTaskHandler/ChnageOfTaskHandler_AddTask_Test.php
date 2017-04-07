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
use common\nmodels\Task;
use common\nmodels\Condition;

use Mockery as m;

class ChangeOfTaskHandler_AddTask_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testAddTask()
    {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);
        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $userId = 1;

        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[savePistureOfTask]',
            [$changeOfTaskParser, $taskXMLConverter, $userId]
        );
        $changeOfTaskHandler->shouldReceive('savePistureOfTask')->never();

        $task = m::mock(Task::class . '[save]');
        $task->id = 77;
        $task->shouldReceive('save')->times(1);

        $condition = m::mock(Condition::class . '[save]');
        $condition->shouldReceive('save')->times(1);

        $taskWithConditions = [
            'task' => $task,
            'conditions' => [$condition],
            'picture' => null
        ];

        $changeOfTaskHandler->addTask($taskWithConditions);

        $this->assertEquals($userId, $task->user, "User id must be 1");
        $this->assertEquals($task->id, $condition->task_id, "Task id must be 1");
    }

    /*
   public function testAddTask() {
       $changeParser = Stub::make(
           ChangeOfTaskParser::class,
           array(
               //'getTimeOfChange' => Codeception\Util\Stub::exactly(1, function() {return '111';})
           ),$this
       );
       $taskConverter = new \common\nmodels\TaskXMLConverter(new \common\components\ConditionXMLConverter());
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
   */
}