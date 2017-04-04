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
use common\components\BAException as BAException;

class ConditionXMLConverterTest extends \Codeception\TestCase\Test {

    /**
     * @var UnitTester
     */
    protected $tester;

    public function testConvertTimeConditionFromXML() {
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

    public function testConvertLocationConditionFromXML() {
        $converter = new ConditionXMLConverter();
        $conditionXMLElement = new \SimpleXMLElement("<condition id='715' task-id='1457' type='LOCATION'>" .
            "<params>" .
                "{\"address\":\"ул. Фрунзе, 12, Zhukovskiy\",\"radius\":100,\"lng\":38.125353455544,\"lat\":55.59917167827}" .
            "</params>" .
            "</condition>");
        $condition = $converter->fromXML($conditionXMLElement);
        $this->assertNotNull($condition, "Condition must not be a null");
        $this->assertEquals(715, $condition->id, "Wrong id");
        $this->assertEquals(1, $condition->type, "Wrong type");
        $this->assertEquals(1457, $condition->task_id, "Wrong task id");
        $this->assertEquals('{"address":"ул. Фрунзе, 12, Zhukovskiy","radius":100,"lng":38.125353455544,"lat":55.59917167827}', $condition->params, "Wrong params");
    }


    /**
     * @expectedException common\components\BAException
     * @expectedExceptionCode 1101
     */
    public function testConvertWrontTypeConditionFromXMLWith() {
        $converter = new ConditionXMLConverter();
        $conditionXMLElement = new \SimpleXMLElement("<condition id='715' task-id='1457' type='GPS'>" .
            "<params>" .
                "{\"address\":\"ул. Фрунзе, 12, Zhukovskiy\",\"radius\":100,\"lng\":38.125353455544,\"lat\":55.59917167827}" .
            "</params>" .
            "</condition>");
        //$this->setExpectedException('common\components\BAException');
        $condition = $converter->fromXML($conditionXMLElement);
    }
}