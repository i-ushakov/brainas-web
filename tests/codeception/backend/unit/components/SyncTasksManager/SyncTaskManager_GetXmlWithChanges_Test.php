<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/29/2017
 * Time: 2:58 PM
 */

use \backend\components\TasksSyncManager;
use \backend\components\ChangeOfTaskHandler;
use \backend\components\XMLResponseBuilder;

use Mockery as m;

class SyncTaskManager_GetXmlWithChanges_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testGetXmlWithChanges() {
        /* @var $changeOfTaskHandler Mockery */
        $changeOfTaskHandler = m::mock(ChangeOfTaskHandler::class);

        /* @var $responseBuilder Mockery */
        $responseBuilder =  m::mock(XMLResponseBuilder::class);
        $serverChanges = ['created' => [], 'updated' => []];
        $serverChangesWithDeleted = array_merge($serverChanges, ['deleted' => [10 => 1, 11 => 2]]);
        $currentTime = '2017-02-05 00:00:00';
        $responseBuilder->shouldReceive('prepareXmlWithTasksChanges')
            ->once()->with($serverChangesWithDeleted, $currentTime);

        /* @var $tasksSyncManager Mockery */
        $tasksSyncManager = m::mock(
            TasksSyncManager::class . '[getChangesOfTasks, getCurrentTime, getDeletedTasks]',
            [$changeOfTaskHandler, $responseBuilder]);
        $tasksSyncManager->shouldReceive('getChangesOfTasks')->once()->andReturn($serverChanges);
        $tasksSyncManager->shouldReceive('getCurrentTime')->once()->andReturn($currentTime);
        $tasksSyncManager->shouldReceive('getDeletedTasks')->once()->andReturn([10 => 1, 11 => 2]);

        // testing...
        $existsTasksFromDevice = new SimpleXMLElement("<xml/>");
        $lastSyncTime = '2017-02-04 00:00:00';
        $tasksSyncManager->getXmlWithChanges($existsTasksFromDevice, $lastSyncTime);
    }
}