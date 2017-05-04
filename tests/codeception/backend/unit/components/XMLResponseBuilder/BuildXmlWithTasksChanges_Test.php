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
use common\components\TaskXMLConverter;
use common\components\ConditionXMLConverter;

use AspectMock\Test as test;
use Mockery as m;

class BuildXmlWithTasksChanges_Test extends \Codeception\TestCase\Test
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

    public function testBuildXmlWithTasksChanges()
    {
        /* var $taskXMLConverter TaskXMLConverter */
        $taskXMLConverter = m::mock(TaskXMLConverter::class . "[toXML]", [m::mock(ConditionXMLConverter::class)]);
        $taskXMLConverter->shouldReceive('toXML');

        /* var $xmlResponseBuilder XMLResponseBuilder */
        $xmlResponseBuilder = m::mock(XMLResponseBuilder::class . "[buildCreatedPart, buildUpdatedPart, buildDeletedPart]", [$taskXMLConverter]);
        $xmlResponseBuilder->shouldReceive('buildCreatedPart')
            ->once()->andReturn("<created>created_stuff</created>");
        $xmlResponseBuilder->shouldReceive('buildUpdatedPart')
            ->once()->andReturn("<updated>updated_stuff</updated>");
        $xmlResponseBuilder->shouldReceive('buildDeletedPart')
            ->once()->andReturn("<deleted>deleted_stuff</deleted>");

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
                    '<created>created_stuff</created>' .
                    '<updated>updated_stuff</updated>'  .
                    '<deleted>deleted_stuff</deleted>' .
                '</tasks>' .
                '<serverTime>2017-06-01 00:00:00</serverTime>' .
            '</changes>';
        $this->tester->assertEquals($xmlWithTasksChanges, $result, "Wrong xml with synchronized objects");
    }
}
