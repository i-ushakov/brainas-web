<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/5/2017
 * Time: 3:48 PM
 */

use backend\components\XMLResponseBuilder;


use AspectMock\Test as test;
use Mockery as m;
use common\components\TaskXMLConverter;

class XMLResponseBuilder_PrepareSyncObjectsXml_Test extends \Codeception\TestCase\Test
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

    public function testPrepareSyncObjectsXml()
    {
        /* var $taskXMLConverter TaskXMLConverter */
        $taskXMLConverter = m::mock(TaskXMLConverter::class);

        /* var $xmlResponseBuilder XMLResponseBuilder */
        $xmlResponseBuilder = new XMLResponseBuilder($taskXMLConverter);

        $synchronizedTasks = [
            1 => 11,
            2 => 12
        ];

        // testing ...
        $result = $xmlResponseBuilder->prepareSyncObjectsXml($synchronizedTasks);

        // assetions :
        $synchronizedObjsXml = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<synchronizedTasks>' .
                '<synchronizedTask>' .
                    '<localId>1</localId>' .
                    '<globalId>11</globalId>' .
                '</synchronizedTask>' .
                '<synchronizedTask>' .
                    '<localId>2</localId>' .
                    '<globalId>12</globalId>' .
                '</synchronizedTask>' .
            '</synchronizedTasks>';
        $this->tester->assertEquals($synchronizedObjsXml, $result, "Wrong xml with synchronized objects");
    }
}
