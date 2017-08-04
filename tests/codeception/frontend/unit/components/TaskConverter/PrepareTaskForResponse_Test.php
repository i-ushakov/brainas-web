<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 5/9/2017
 * Time: 8:39 PM
 */

use \common\models\Task;
use \common\models\Condition;
use \common\models\EventType;
use \common\models\PictureOfTask;
use \common\models\ChangeOfTask;
use \frontend\components\TaskConverter;

use Mockery as m;

class PrepareTaskForResponse_Test extends \Codeception\TestCase\Test
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
        /* var $changeOfTask Mockery */
        $changeOfTask = new ChangeOfTask([
            'datetime' => '2017-05-04 13:37:05'
        ]);

        $condition1 = m::mock(Condition::class . "[getEventType]", [
            ['id' => 50, 'params' => '{"lat":55.599274724762,"lng":38.102688789368,"radius":100}']
        ]);
        $condition1->shouldReceive('getEventType')->andReturn(new EventType(['id' => 1, 'name' => 'LOCATION']));
        $condition2 = m::mock(Condition::class . "[getEventType]", [
            ['id' => 51, 'params' => '{"datetime":"04-10-2016 13:25:25","offset":-180}']
        ]);
        $condition2->shouldReceive('getEventType')->andReturn(new EventType(['id' => 2, 'name' => 'TIME']));

        /* var $conditions Array */
        $conditions = [$condition1, $condition2];

        /* var $picture PictureOfTask */
        $picture = new PictureOfTask([
            'name' => 'task_picture_1468265942124.gif',
            'file_id' => '0B-nWSp4lPq2nV2dBMEs1bk1kdl1'
        ]);

        /* var $task Mockery */
        $task = m::mock(
            Task::class . "[getChangeOfTask, getConditions, getPicture]", [
                [
                    'id' => 100,
                    'user' => 1,
                    'message' => 'Task 100',
                    'description' => 'Desc',
                    'status' => 'ACTIVE',
                    'created' => '2017-04-28 20:49:24'
                ]
            ]
        );
        $task->shouldReceive('getChangeOfTask')->andReturn($changeOfTask);
        $task->shouldReceive('getConditions')->andReturn($conditions);
        $task->shouldReceive('getPicture')->andReturn($picture);

        /* var $taskConverter TaskConverter */
        $taskConverter = new TaskConverter();

        // testing ...
        $r = $taskConverter->prepareTaskForResponse($task);

        // assertions:
        $this->tester->assertEquals(100, $r['id'], "Wrong task Id");
        $this->tester->assertEquals("Desc", $r['description'], "Wrong task description");
        $this->tester->assertEquals('Task 100', $r['message'], "Wrong task message");
        $this->tester->assertEquals('ACTIVE', $r['status'], "Wrong task status");
        $this->tester->assertEquals('2017-04-28 20:49:24', $r['created'], "Wrong task created");
        $this->tester->assertEquals('2017-05-04 13:37:05', $r['changed'], "Wrong task changed");
        $this->tester->assertEquals('task_picture_1468265942124.gif', $r['picture_name'], "Wrong task picture name");
        $this->tester->assertEquals('0B-nWSp4lPq2nV2dBMEs1bk1kdl1', $r['picture_file_id'], "Wrong task picture fileId");

        $this->tester->assertEquals(2, count($r['conditions']), "Wrong task changed");
        $condition1 = $r['conditions'][0];
        $condition2 = $r['conditions'][1];
        $this->tester->assertEquals(50, $condition1['conditionId'], "Wrong condition1 Id");
        $this->tester->assertEquals(51, $condition2['conditionId'], "Wrong condition2 Id");
        $this->tester->assertTrue(isset($condition1['LOCATION']));
        $this->tester->assertTrue(isset($condition2['TIME']));
        $this->tester->assertEquals(50, $condition1['LOCATION']['eventId'], "Wrong condition1 eventId");
        $this->tester->assertEquals(51, $condition2['TIME']['eventId'], "Wrong condition2 eventId");
        $this->tester->assertEquals('LOCATION', $condition1['LOCATION']['type'], "Wrong condition1 type");
        $this->tester->assertEquals('TIME', $condition2['TIME']['type'], "Wrong condition2 type");
        $expectedParams1 = json_decode('{"lat":55.599274724762,"lng":38.102688789368,"radius":100}');
        $this->tester->assertEquals($expectedParams1, $condition1['LOCATION']['params'], "Wrong condition1 params");
        $expectedParams2 = json_decode('{"datetime":"04-10-2016 13:25:25","offset":-180}');
        $this->tester->assertEquals($expectedParams2, $condition2['TIME']['params'], "Wrong condition2 params");
    }
}