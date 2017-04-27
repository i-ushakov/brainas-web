<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/29/2017
 * Time: 2:58 PM
 */

use backend\components\ChangeOfTaskParser;

use Mockery as m;

class ChangeOfTaskParser_GetGlobalId_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testRetrive100()
    {
        $xml = new SimpleXMLElement('<changeOfTask localId="1" globalId="10"></changeOfTask>');
        $parser = new ChangeOfTaskParser();
        $globalId = $parser->getGlobalId($xml);
        $this->assertEquals(10, $globalId, "Wrong global Id");
    }

    public function testRetrive0()
    {
        $xml = new SimpleXMLElement('<changeOfTask localId="1" globalId="0"></changeOfTask>');
        $parser = new ChangeOfTaskParser();
        $globalId = $parser->getGlobalId($xml);
        $this->assertEquals(0, $globalId, "Wrong global Id");
    }

    public function testRetriveUndefined()
    {
        $xml = new SimpleXMLElement('<changeOfTask localId="1"></changeOfTask>');
        $parser = new ChangeOfTaskParser();
        $globalId = $parser->getGlobalId($xml);
        $this->assertEquals(0, $globalId, "Wrong global Id");
    }
}