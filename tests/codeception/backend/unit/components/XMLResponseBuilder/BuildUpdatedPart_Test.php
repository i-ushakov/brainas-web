<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/5/2017
 * Time: 3:48 PM
 */

use backend\components\XMLResponseBuilder;
use common\infrastructure\ChangeOfTask;
use common\nmodels\Task;

use AspectMock\Test as test;
use Mockery as m;

class BuildUpdatedPart_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        test::clean(); // remove all registered test doubles
        m::close();
    }

    public function testbuildDeletedPart()
    {
        /* var $xmlResponseBuilder XMLResponseBuilder */
        $xmlResponseBuilder = new XMLResponseBuilder();

        $updatedTasks = [12 => [
            'action' => ChangeOfTask::STATUS_UPDATED,
            'datetime' => '2017-04-13 22:00:00',
            'object' => new Task([
                'id' => 12,
                'user' => 1,
                'message' => 'Task 12',
                'description' => 'No desc',
                'status' => 'ACTIVE',
                'created' => '2017-04-13 20:00:16',
                'last_modify' => '2017-04-13 22:00:00'])
        ]];

        // testing ...
        $result = $xmlResponseBuilder->buildUpdatedPart($updatedTasks);

        // assetions :
        $updatedPartOfXml = '' .
            '<updated>' .
                '<task globalId="12" timeOfChange="2017-04-13 22:00:00">' .
                    '<message>Task 12</message>' .
                    '<description>No desc</description>' .
                    '<conditions></conditions>' .
                    '<status>ACTIVE</status>' .
                '</task>' .
            '</updated>';
        $this->tester->assertEquals($updatedPartOfXml, $result, "Wrong part of xml with updated tasks");
    }
}
