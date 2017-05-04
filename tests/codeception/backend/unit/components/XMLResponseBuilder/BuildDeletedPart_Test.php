<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/5/2017
 * Time: 3:48 PM
 */

use backend\components\XMLResponseBuilder;
use common\components\TaskXMLConverter;


use AspectMock\Test as test;
use Mockery as m;

class BuildDeletedPart_Test extends \Codeception\TestCase\Test
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

    public function testbuildDeletedPart()
    {
        /* var $xmlResponseBuilder XMLResponseBuilder */
        $xmlResponseBuilder = new XMLResponseBuilder(m::mock(TaskXMLConverter::class));

        $deletedTasks = [11 => 1, 12 => 2];
        // testing ...
        $result = $xmlResponseBuilder->buildDeletedPart($deletedTasks);

        // assetions :
        $deletedPartOfXml = '' .
            '<deleted>' .
                '<deletedTask globalId="11" localId="1"></deletedTask>' .
                '<deletedTask globalId="12" localId="2"></deletedTask>' .
            '</deleted>';
        $this->tester->assertEquals($deletedPartOfXml, $result, "Wrong xml with synchronized objects");
    }
}
