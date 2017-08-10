<?php

/**
 * Created by PhpStorm.
 * User: kit
 * Date: 8/9/2017
 * Time: 12:33 PM
 */
use common\models\Task;
use common\models\User;
use \frontend\components\TasksManager;
use \frontend\components\GoogleIdentityHelper;
use \frontend\components\StatusManager;


use Mockery as m;

class handleTaskTest extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testCreateNewTask()
    {
        // Preparing test data and object
        $testTaskId = 1;

        /* @var array */
        $testTaskDataForSave = [
            'id'=> null,
            'conditions' => [[]],
            'picture_name' => 'picture_name',
            'picture_file_id' => 'picture_file_id'
        ];

        /* @var  Mock_Task */
        $testTaskMock = m::mock(Task::class . "[]");
        $testTaskMock->id = $testTaskId;

        /* @var $statusManagerMock Mock_StatusManager */
        $statusManagerMock = m::mock(StatusManager::class . "[updateStatus]");
        $statusManagerMock->shouldReceive('updateStatus')->once();

        /* @var $tasksManagerSpy TasksManager */
        $tasksManagerSpy = m::mock(TasksManager::class . "[createTask, savePicture, cleanDeletedConditions, saveConditions, saveTask]",
            [
                m::mock(GoogleIdentityHelper::class),
                $statusManagerMock
            ]);
        $tasksManagerSpy->shouldReceive('createTask')->once()->andReturn($testTaskMock);
        $tasksManagerSpy->shouldReceive('savePicture')->once();
        $tasksManagerSpy->shouldReceive('cleanDeletedConditions')->once();
        $tasksManagerSpy->shouldReceive('saveConditions')->once();
        $tasksManagerSpy->shouldReceive('saveTask')->once();


        $result = $tasksManagerSpy->handleTask($testTaskDataForSave);

        $this->tester->assertEquals(
            $testTaskMock, $result
        );
    }

    public function testUpdateExistsTask()
    {
        // Preparing test data and object
        $testTaskId = 100;
        $testUserId = 1;

        // Preparing DB
        $testTask = new Task([
            'id' => $testTaskId,
            'user' => $testUserId,
            'message' => 'Task 100',
            'description' => 'Desc 100',
            'status' => 'TODO',
            'created' => '2017-04-28 20:49:24',
            'last_modify' => '2017-05-04 13:37:05']);
        $testTask->save();

        /* @var $testTaskDataForSave */
        $testTaskDataForSave = [
            'id'=> $testTaskId,
            'conditions' => [[]],
            'picture_name' => 'picture_name',
            'picture_file_id' => 'picture_file_id'
        ];

        /* @var $testUser */
        $testUser = m::mock(User::class . "[]");
        $testUser->id = $testUserId;

        /* @var $statusManagerMock Mock_StatusManager */
        $statusManagerMock = m::mock(StatusManager::class . "[updateStatus]");
        $statusManagerMock->shouldReceive('updateStatus')->once();

        /* @var $tasksManagerSpy Mock_TasksManager */
        $tasksManagerSpy = m::mock(TasksManager::class . "[createTask, updateTask, savePicture, cleanDeletedConditions, saveConditions, saveTask]",
            [
                m::mock(GoogleIdentityHelper::class),
                $statusManagerMock

            ]);
        $tasksManagerSpy->setUser($testUser);
        $tasksManagerSpy->shouldReceive('createTask')->never();
        $tasksManagerSpy->shouldReceive('updateTask')->once()->with($testTaskDataForSave)->andReturn($testTask);
        $tasksManagerSpy->shouldReceive('savePicture')->once();
        $tasksManagerSpy->shouldReceive('cleanDeletedConditions')->once();
        $tasksManagerSpy->shouldReceive('saveConditions')->once();
        $tasksManagerSpy->shouldReceive('saveTask')->once();

        $result = $tasksManagerSpy->handleTask($testTaskDataForSave);

        $this->tester->assertTrue(!empty($result));
        $this->tester->assertEquals($testTaskId, $result->id);
    }
}