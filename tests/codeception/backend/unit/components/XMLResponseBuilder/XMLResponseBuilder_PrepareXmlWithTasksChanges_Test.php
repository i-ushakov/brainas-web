<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/5/2017
 * Time: 3:48 PM
 */

use backend\components\XMLResponseBuilder;
use common\nmodels\Task;
use common\infrastructure\ChangeOfTask;

use AspectMock\Test as test;
use Mockery as m;

class XMLResponseBuilder_BuildXmlWithTasksChanges_Test extends \Codeception\TestCase\Test
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

    public function testPrepareXmlWithCreatedAndUpdated()
    {
        /* var $xmlResponseBuilder XMLResponseBuilder */
        $xmlResponseBuilder = m::mock(XMLResponseBuilder::class . "[buildUpdatedPart, buildDeletedPart]");
        $xmlResponseBuilder->shouldReceive('buildUpdatedPart')
            ->once()->andReturn("<updated>some stuff</updated>");
        $xmlResponseBuilder->shouldReceive('buildDeletedPart')
            ->once()->andReturn("<deleted>some stuff</deleted>");

        $changedTasks = [
            'created' => [
                11 => [
                    'action' => ChangeOfTask::STATUS_CREATED,
                    'datetime' => '2017-04-13 20:00:16',
                    'object' => new Task([
                        'id' => 11,
                        'user' => 1,
                        'message' => 'Task 11',
                        'description' => 'No desc',
                        'status' => 'TODO',
                        'created' => '2017-04-13 20:00:16',
                        'last_modify' => '2017-04-13 20:00:16'])
                ]
            ],
            'updated' => [
                12 => [
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
                ]
            ],
            'deleted' => [11 => 1, 12 => 2]
        ];

        // testing ...
        $currentTime = "2017-06-01 00:00:00";
        $result = $xmlResponseBuilder->buildXmlWithTasksChanges($changedTasks, $currentTime);

        // assetions :
        $xmlWithTasksChanges = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<changes>' .
                '<tasks>' .
                    '<created>' .
                        '<task globalId="11" timeOfChange="2017-04-13 20:00:16">' .
                            '<message>Task 11</message>' .
                            '<description>No desc</description>' .
                            '<conditions></conditions>' .
                            '<status>TODO</status>' .
                        '</task>' .
                    '</created>' .
                    '<updated>some stuff</updated>'  .
                    '<deleted>some stuff</deleted>' .
                '</tasks>' .
                '<serverTime>2017-06-01 00:00:00</serverTime>' .
            '</changes>';
        $this->tester->assertEquals($xmlWithTasksChanges, $result, "Wrong xml with synchronized objects");
    }
}
