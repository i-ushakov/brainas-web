<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 11/28/2016
 * Time: 5:38 PM
 */

namespace tests\codeception\common\unit;


use common\nmodels\Condition;
use common\nmodels\ConditionXMLConverter;
class ConditionsConverterTest extends \Codeception\TestCase\Test {

    /**
     * @var UnitTester
     */
    protected $tester;

    private $conditionXMLString =  <<<EOT
        <condition id='714' task-id='1456' type='TIME'> 
            <params>
		        {"offset":-180,"datetime":"04-11-2016 15:01:47"}
	        </params>
        </condition>;
EOT;

    public function testConvertFromXML() {
       $converter = new ConditionXMLConverter();
       $conditionXMLElement = new \SimpleXMLElement("<condition id='714' task-id='1456' type='TIME'>" .
            "<params>" .
		        "{\"offset\":-180,\"datetime\":\"04-11-2016 15:01:47\"}" .
	        "</params>" .
        "</condition>");
        $condition = $converter->fromXML($conditionXMLElement);
        $this->assertNotNull($condition, "Condition must not be a null");
        $this->assertEquals(714, $condition->id, "Wrong id");
        $this->assertEquals(2, $condition->type, "Wrong type");
        $this->assertEquals(1456, $condition->task_id, "Wrong task id");
        $this->assertEquals('{"offset":-180,"datetime":"04-11-2016 15:01:47"}', $condition->params, "Wrong params");
    }

}