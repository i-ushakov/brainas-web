<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 11/28/2016
 * Time: 5:38 PM
 */

namespace tests\codeception\common\unit;

use common\components\ConditionXMLConverter;
use common\models\Condition;
use common\models\Task;
use Mockery as m;

class ToXmlTest extends \Codeception\TestCase\Test {

    /**
     * @var UnitTester
     */
    protected $tester;

    public function testToXml() {
        $task = m::mock(Task::class . "[getConditions, getId]");
        $conditions = [
            new Condition([
                'id' => 12,
                'task_id' => 11,
                'type' => 1,
                'params' => '{location_params}'
            ])
        ];
        $task->shouldReceive('getId')->andReturn(11);
        $task->shouldReceive('getConditions')->andReturn($conditions);

        /* var $conditionXMLConverter ConditionXMLConverter */
        $conditionXMLConverter = new ConditionXMLConverter();
        // testing ...
        $resultXml = $conditionXMLConverter->toXML($task);

        // assertions:
        $expectedXml = "<condition globalId='12' type='LOCATION'><params>{location_params}</params></condition>";
        $this->assertEquals($expectedXml, $resultXml, "Wrong condition xml");
    }
}