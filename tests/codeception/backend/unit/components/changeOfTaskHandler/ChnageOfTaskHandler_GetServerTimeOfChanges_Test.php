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

use Mockery as m;

class ChangeOfTaskHandler_GetServerTimeOfChanges_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testGetServerTime()
    {
        $converter = m::mock(TaskXMLConverter::class);
        $parser = m::mock(ChangeOfTaskParser::class);
        $userId = 1;

        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . "[]",
            [$parser, $converter, $userId]
        );

        $taskId = 101;

        $this->tester->haveInDatabase('sync_changed_tasks', array(
            'id' => 122,
            'task_id' => $taskId,
            'user_id' => 1,
            'datetime' => '2016-10-04 00:00:00',
            'server_update_time' => '2016-10-05 00:00:00'));

        $serverTime = $changeOfTaskHandler->getServerTimeOfChanges($taskId);
        $this->tester->assertEquals("2016-10-04 00:00:00", $serverTime);
    }
}