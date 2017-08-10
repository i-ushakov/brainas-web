<?php

/**
 * Created by PhpStorm.
 * User: kit
 * Date: 8/9/2017
 * Time: 12:33 PM
 */
use \frontend\components\TasksManager;

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
        // TODO
    }
}