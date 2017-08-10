<?php

/**
 * Created by PhpStorm.
 * User: kit
 * Date: 8/9/2017
 * Time: 12:33 PM
 */
use frontend\components\TasksManager;
use frontend\components\StatusManager;
use frontend\components\GoogleIdentityHelper;
use frontend\components\ChangesLogger;

use Mockery as m;

class SetTaskDataTest extends \Codeception\TestCase\Test
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
        // preparing data and test objects
        $message = 'message';
        $updatedMessage = 'update message';
        $description = 'description';
        $updatedDescription = 'update description';
        $status = StatusManager::STATUS_DISABLED;
        $updatedStatus = StatusManager::STATUS_TODO;
        $taskDataForSave = [
            'message' => $updatedMessage,
            'description' => $updatedDescription,
            'status' => $updatedStatus
        ];

        $task = new \common\models\Task(
            [
                'message' => $message,
                'description' => $description,
                'status' => $status
            ]
        );
        $tasksManager = new TasksManager(
            m::mock(GoogleIdentityHelper::class),
            m::mock(StatusManager::class),
            m::mock(ChangesLogger::class));

        $task = $tasksManager->setTaskData($task, $taskDataForSave);

        $this->tester->assertEquals($updatedMessage, $task->message);
        $this->tester->assertEquals($updatedDescription, $task->description);
        $this->tester->assertEquals($updatedStatus, $task->status);
    }
}