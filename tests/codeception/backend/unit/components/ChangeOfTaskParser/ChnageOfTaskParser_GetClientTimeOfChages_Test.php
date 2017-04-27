<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/29/2017
 * Time: 2:58 PM
 */

use backend\components\ChangeOfTaskParser;

use Mockery as m;

class ChangeOfTaskParser_GetClientTimeOfChanges_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testGetTime()
    {
        $xml = new SimpleXMLElement('<changeOfTask localId="1" globalId="10">' .
                '<change>' .
                    '<status>CREATED</status><changeDatetime>2016-12-01 00:00:00</changeDatetime>' .
                '</change>' .
            '</changeOfTask>');
        $parser = new ChangeOfTaskParser();
        $clientTime = $parser->getClientTimeOfChanges($xml);
        $this->tester->assertEquals("2016-12-01 00:00:00", $clientTime);
    }
}