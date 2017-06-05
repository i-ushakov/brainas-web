<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 5/12/2017
 * Time: 3:46 PM
 */

use \frontend\components\TasksQueryBuilde;

use Mockery as m;

class Get_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // preparing DB
        $this->tester->haveInDatabase('tasks', array(
            'id' => 100,
            'user' => 1,
            'message' => 'Task 100',
            'description' => 'Desc 100',
            'status' => 'TODO',
            'created' => '2017-04-28 20:49:24',
            'last_modify' => '2017-05-04 13:37:05'));

        $this->tester->haveInDatabase('tasks', array(
            'id' => 98,
            'user' => 1,
            'message' => 'Task 98',
            'description' => 'Desc 98',
            'status' => 'DONE',
            'created' => '2017-02-02 00:00:00',
            'last_modify' => '2017-03-28 00:00:00'));

        $this->tester->haveInDatabase('tasks', array(
            'id' => 99,
            'user' => 1,
            'message' => 'Task 99',
            'description' => 'Desc 99',
            'status' => 'WAITING',
            'created' => '2017-03-28 00:00:00',
            'last_modify' => '2017-03-28 00:00:00'));

        $this->tester->haveInDatabase('tasks', array(
            'id' => 101,
            'user' => 1,
            'message' => 'Task 101',
            'description' => 'Desc 101',
            'status' => 'ACTIVE',
            'created' => '2017-05-29 00:00:00',
            'last_modify' => '2017-05-29 00:00:00'));

        $this->tester->haveInDatabase('tasks', array(
            'id' => 102,
            'user' => 1,
            'message' => 'Task 102',
            'description' => 'Desc 102',
            'status' => 'CANCELED',
            'created' => '2017-05-30 00:00:00',
            'last_modify' => '2017-05-30 00:00:00'));


        $this->tester->haveInDatabase('tasks', array(
            'id' => 103,
            'user' => 1,
            'message' => 'Task 103',
            'description' => 'Desc 103',
            'status' => 'DISABLED',
            'created' => '2017-06-01 00:00:00',
            'last_modify' => '2017-06-01 00:00:00'));
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testGetAllTasksInNewestOrder()
    {
        $userID = 1;
        $statusesFilter = ["TODO", "WAITING", "ACTIVE", "DISABLED", "DONE", "CANCELED"];
        $typeOfSort = TasksQueryBuilde::SORTTYPE_NEWEST;
        $tasksQueryBuilde = new TasksQueryBuilde($userID);

        // testing ...
        $tasks = $tasksQueryBuilde->get($statusesFilter, $typeOfSort);

        // assertions:
        $this->tester->assertEquals(6, count($tasks), "Wrong number of tasks");

        $task98 = $tasks[0];
        $this->tester->assertTrue($task98 instanceof \common\nmodels\Task);
        $this->tester->assertEquals(98, $task98->id, "Wrong id of tasks");

        $task99 = $tasks[1];
        $this->tester->assertEquals(99, $task99->id, "Wrong id of tasks");

        $task100 = $tasks[2];
        $this->tester->assertEquals(100, $task100->id, "Wrong id of tasks");

        $task101 = $tasks[3];
        $this->tester->assertEquals(101, $task101->id, "Wrong id of tasks");

        $task102 = $tasks[4];
        $this->tester->assertEquals(102, $task102->id, "Wrong id of tasks");

        $task103 = $tasks[5];
        $this->tester->assertEquals(103, $task103->id, "Wrong id of tasks");
    }

    public function testGetAllTasksInOldestOrder()
    {
        $userID = 1;
        $statusesFilter = ["TODO", "WAITING", "ACTIVE", "DISABLED", "DONE", "CANCELED"];
        $typeOfSort = TasksQueryBuilde::SORTTYPE_OLDEST;
        $tasksQueryBuilde = new TasksQueryBuilde($userID);

        // testing ...
        $tasks = $tasksQueryBuilde->get($statusesFilter, $typeOfSort);

        // assertions:
        $this->tester->assertEquals(6, count($tasks), "Wrong number of tasks");

        $task98 = $tasks[5];
        $this->tester->assertTrue($task98 instanceof \common\nmodels\Task);
        $this->tester->assertEquals(98, $task98->id, "Wrong id of tasks");

        $task99 = $tasks[4];
        $this->tester->assertEquals(99, $task99->id, "Wrong id of tasks");

        $task100 = $tasks[3];
        $this->tester->assertEquals(100, $task100->id, "Wrong id of tasks");

        $task101 = $tasks[2];
        $this->tester->assertEquals(101, $task101->id, "Wrong id of tasks");

        $task102 = $tasks[1];
        $this->tester->assertEquals(102, $task102->id, "Wrong id of tasks");

        $task103 = $tasks[0];
        $this->tester->assertEquals(103, $task103->id, "Wrong id of tasks");
    }

    public function testGetAllActiveTasksInOldestOrder()
    {
        $userID = 1;
        $statusesFilter = ["ACTIVE"];
        $typeOfSort = TasksQueryBuilde::SORTTYPE_OLDEST;
        $tasksQueryBuilde = new TasksQueryBuilde($userID);

        // testing ...
        $tasks = $tasksQueryBuilde->get($statusesFilter, $typeOfSort);

        // assertions:
        $this->tester->assertEquals(1, count($tasks), "Wrong number of tasks");
        $task101 = $tasks[0];
        $this->tester->assertEquals(101, $task101->id, "Wrong id of tasks");
    }
}