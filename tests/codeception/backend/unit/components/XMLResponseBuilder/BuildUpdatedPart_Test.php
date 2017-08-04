<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/5/2017
 * Time: 3:48 PM
 */

use backend\components\XMLResponseBuilder;
use common\models\ChangeOfTask;
use common\models\Task;
use common\components\TaskXMLConverter;
use common\components\ConditionXMLConverter;

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

    public function testBuildUpdatedPart()
    {
        /* var $taskXMLConverter TaskXMLConverter */
        $taskXMLConverter = m::mock(TaskXMLConverter::class . "[toXML]", [m::mock(ConditionXMLConverter::class)]);
        $taskXMLConverter->shouldReceive('toXML')
            ->once()->andReturn('<task globalId="12" timeOfChange="2017-04-13 22:00:0">task stuff</task>');

        /* var $xmlResponseBuilder XMLResponseBuilder */
        $xmlResponseBuilder = new XMLResponseBuilder($taskXMLConverter);

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
                '<task globalId="12" timeOfChange="2017-04-13 22:00:0">task stuff</task>' .
            '</updated>';
        $this->tester->assertEquals($updatedPartOfXml, $result, "Wrong part of xml with updated tasks");
    }
}
