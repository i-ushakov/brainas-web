<?php

/**
 * Created by PhpStorm.
 * User: kit
 * Date: 8/9/2017
 * Time: 12:33 PM
 */
use common\models\User;
use frontend\components\TasksManager;
use frontend\components\StatusManager;
use frontend\components\GoogleIdentityHelper;
use frontend\components\ChangesLogger;

use Mockery as m;

class CreateNewTaskTest extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function test()
    {
        /* @var array */
        $taskDataForSave = [
            'id'=> null,
            'conditions' => [[]],
            'picture_name' => 'picture_name',
            'picture_file_id' => 'picture_file_id'
        ];

        /* @var $user */
        $user = m::mock(User::class . "[]");
        $testUserId = 1;
        $user->id = $testUserId;

        /* @var $tasksManagerSpy TasksManager */
        $tasksManagerSpy = m::mock(TasksManager::class . "[setTaskData, saveTask]",
            [
                m::mock(GoogleIdentityHelper::class),
                m::mock(StatusManager::class),
                m::mock(ChangesLogger::class)
            ]);
        $tasksManagerSpy->shouldReceive('setTaskData')->once();
        $tasksManagerSpy->shouldReceive('saveTask')->once();
        $tasksManagerSpy->setUser($user);

        $task = $tasksManagerSpy->createTask($taskDataForSave);

        $this->tester->assertEquals(TasksManager::NEW_MESSAGE_TITLE, $task->message);
        $this->tester->assertEquals($testUserId, $task->user);
    }

}