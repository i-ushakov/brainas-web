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
use common\components\TaskXMLConverter;

use AspectMock\Test as test;
use Mockery as m;

class BuildCreatedPart_Test extends \Codeception\TestCase\Test
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

    public function testBuildCreatedPart()
    {
        /* var $taskXMLConverter TaskXMLConverter */
        $taskXMLConverter = m::mock(TaskXMLConverter::class . "[toXML]", [m::mock(\common\components\ConditionXMLConverter::class)]);
        $taskXMLConverter->shouldReceive('toXML')
            ->once()->andReturn("<task globalId=\"11\" timeOfChange=\"2017-04-13 20:00:16\">task_stuff1</task>");

        /* var $xmlResponseBuilder XMLResponseBuilder */
        $xmlResponseBuilder = new XMLResponseBuilder($taskXMLConverter);

        $createdTasks = [
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
                'last_modify' => '2017-04-13 20:00:16'])]
        ];

        // testing ...
        $result = $xmlResponseBuilder->buildCreatedPart($createdTasks);

        // assetions :
        $createdPartOfXml = '' .
            '<created>' .
                '<task globalId="11" timeOfChange="2017-04-13 20:00:16">task_stuff1</task>' .
            '</created>';
        $this->tester->assertEquals($createdPartOfXml, $result, "Wrong part of xml with updated tasks");
    }
}
