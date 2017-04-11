<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/29/2017
 * Time: 2:58 PM
 */

use \backend\components\TasksSyncManager;
use \backend\components\ChangeOfTaskHandler;

use Mockery as m;
use Codeception\Util\Stub;

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

    public function testGetUpdates() {
        $changeOfTaskHandler = \Mockery::mock(ChangeOfTaskHandler::class);

        $tasksSyncManager = \Mockery::mock(
            TasksSyncManager::class . '[getChangesOfTasks, prepareXmlWithTasksChanges]',
            [$changeOfTaskHandler]
        );
        $tasksSyncManager->shouldReceive('getChangesOfTasks')->once();
        $tasksSyncManager->shouldReceive('prepareXmlWithTasksChanges')->once();

        $existsTasksFromDevice= new SimpleXMLElement("<xml/>");
        $lastSyncTime = '2017-02-04 00:00:00';
        $tasksSyncManager->getXmlWithChanges($existsTasksFromDevice, $lastSyncTime);
    }
}