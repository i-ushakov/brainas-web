<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/29/2017
 * Time: 2:58 PM
 */

use backend\components\ChangeOfTaskParser;
use backend\components\ChangeOfTaskHandler;
use common\models\Task;
use common\components\TaskXMLConverter;

use Mockery as m;

class ChangeOfTaskHandler_DeleteTask_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testDeleteExistTask(){
        $converter = m::mock(TaskXMLConverter::class);
        $parser = m::mock(ChangeOfTaskParser::class);
        $userId = 1;

        $changeOfTaskHandler = new ChangeOfTaskHandler($parser, $converter, $userId);

        $taskInDatabase = new Task([
            'id' => 103,
            'message' => 'Task 103',
            'user' => 1,
            'status' => 'TODO',
            'created' => '2016-10-04 16:13:09',
            'last_modify' => '2016-10-04 16:13:09']);
        $taskInDatabase->save();

        $taskId = $changeOfTaskHandler->deleteTask(103);

        $this->tester->dontSeeInDatabase('tasks', array(
            'id' => 103));
        $this->tester->assertEquals(103, $taskId);
    }
}