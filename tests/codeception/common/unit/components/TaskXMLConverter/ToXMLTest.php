<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 12/1/2016
 * Time: 8:47 AM
 */

use Codeception\Util\Stub;
use Mockery as m;
use \common\components\TaskXMLConverter;
use \common\components\ConditionXMLConverter;
use \common\models\Task;

class ToXMLTest extends \Codeception\TestCase\Test {
    /**
     * @var UnitTester
     */
    protected $tester;

    public function testToXML()
    {
        /* var $conditionXMLConverter ConditionXMLConverter */
        $conditionXMLConverter = m::mock(ConditionXMLConverter::class . "[toXML]");
        $conditionXMLConverter->shouldReceive('toXML')
            ->once()->andReturn("<condition>condition_stuff</condition>");

        /* var $taskXMLConverter TaskXMLConverter */
        $taskXMLConverter = new TaskXMLConverter($conditionXMLConverter);

        $task = new Task([
            'id' => 11,
            'user' => 1,
            'message' => 'Task 11',
            'description' => 'No desc',
            'status' => 'TODO',
            'created' => '2017-04-13 20:00:16',
            'last_modify' => '2017-04-13 20:00:16']);
        $datetime = "2017-04-13 20:00:00";

        // testing ...
        $resultXml = $taskXMLConverter->toXML($task, $datetime);

        $expectedXml = '' .
            '<task globalId="11" timeOfChange="2017-04-13 20:00:00">' .
                '<message>Task 11</message>' .
                '<description>No desc</description>' .
                '<conditions>' .
                    '<condition>condition_stuff</condition>' .
                '</conditions>' .
                '<status>TODO</status>' .
            '</task>';

        $this->tester->assertEquals($expectedXml, $resultXml, "Wrong xml with task");
    }
}