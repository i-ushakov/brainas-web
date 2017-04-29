<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 11/28/2016
 * Time: 5:38 PM
 */

namespace tests\codeception\common\unit;

use common\components\ConditionXMLConverter;
use common\components\BAException;
use common\models\EventType;

class FromXmlTest extends \Codeception\TestCase\Test {

    /**
     * @var UnitTester
     */
    protected $tester;

    public function testConvertTimeConditionFromXML() {
       $converter = new ConditionXMLConverter();
       $conditionXMLElement = new \SimpleXMLElement("<condition localId='14' globalId='714' type='TIME'>" .
            "<params>" .
		        "{\"offset\":-180,\"datetime\":\"04-11-2016 15:01:47\"}" .
	        "</params>" .
        "</condition>");
        $condition = $converter->fromXML($conditionXMLElement);
        $this->assertNotNull($condition, "Condition must not be a null");
        $this->assertEquals(714, $condition->id, "Wrong id");
        $this->assertEquals(2, $condition->type, "Wrong type");
        $this->assertEquals('{"offset":-180,"datetime":"04-11-2016 15:01:47"}', $condition->params, "Wrong params");
    }

    public function testConvertLocationConditionFromXML() {
        $converter = new ConditionXMLConverter();
        $conditionXMLElement = new \SimpleXMLElement("<condition localId='15' globalId='715' type='LOCATION'>" .
            "<params>" .
                "{\"address\":\"ул. Фрунзе, 12, Zhukovskiy\",\"radius\":100,\"lng\":38.125353455544,\"lat\":55.59917167827}" .
            "</params>" .
            "</condition>");
        $condition = $converter->fromXML($conditionXMLElement);
        $this->assertNotNull($condition, "Condition must not be a null");
        $this->assertEquals(715, $condition->id, "Wrong id");
        $this->assertEquals(1, $condition->type, "Wrong type");
        $this->assertEquals('{"address":"ул. Фрунзе, 12, Zhukovskiy","radius":100,"lng":38.125353455544,"lat":55.59917167827}', $condition->params, "Wrong params");
    }

    public function testThrowExeptionWrongNameOfEventType() {
        $converter = new ConditionXMLConverter();
        $conditionXMLElement = new \SimpleXMLElement("<condition localId='15' globalId='715' task-id='1457' type='GPS'>" .
            "<params>" .
                "{\"address\":\"ул. Фрунзе, 12, Zhukovskiy\",\"radius\":100,\"lng\":38.125353455544,\"lat\":55.59917167827}" .
            "</params>" .
            "</condition>");
        $this->tester->expectException(
            new BAException(
                EventType::WRONG_NAME_OF_TYPE__MSG,
                BAException::WRONG_NAME_OF_EVENT_TYPE_ERRORCODE
            ),
            function() use ($converter, $conditionXMLElement){
                $converter->fromXML($conditionXMLElement);
            }
        );
    }
}