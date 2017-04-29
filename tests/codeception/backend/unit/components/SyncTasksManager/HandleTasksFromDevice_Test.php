<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/5/2017
 * Time: 3:48 PM
 */
use backend\components\TasksSyncManager;
use backend\components\ChangeOfTaskHandler;
use backend\components\XMLResponseBuilder;
use common\components\TaskXMLConverter;
use common\components\BAException;

use AspectMock\Test as test;
use Mockery as m;

class HandleTasksFromDevice_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before()
    {
        $dbCleaner = new DBCleaner(Yii::$app->db);
        $dbCleaner->clean();
    }

    protected function tearDown()
    {
        test::clean(); // remove all registered test doubles
        m::close();
    }

    public function testThrowWrongParamException()
    {
        /* var $ChangeOfTaskHandler Mockery */
        $changeOfTaskHandler = \Mockery::mock(ChangeOfTaskHandler::class);

        /* var $xmlResponseBuilder XMLResponseBuilder */
        $xmlResponseBuilder = new XMLResponseBuilder(m::mock(TaskXMLConverter::class));

        /* var $tasksSyncManager TasksSyncManager */
        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandler, $xmlResponseBuilder);

        /* var $changedTasksObj SimpleXMLElement */
        $changedTasksObj = new SimpleXMLElement('<wrongElement><someXmlData/></wrongElement>');

        // testing ...
        $this->tester->expectException(new BAException(TasksSyncManager::WRONG_ROOT_ELEMNT, BAException::WRONG_ROOT_XML_ELEMENT_NAME), function() use ($tasksSyncManager,$changedTasksObj){
            $tasksSyncManager->handleTasksFromDevice($changedTasksObj);
        });
    }

    public function testZeroTasksFromDevice()
    {
        $changedTasksXML = ''.
            '<changedTasks></changedTasks>';
        $changedTasks = new SimpleXMLElement($changedTasksXML);

        /* var $ChangeOfTaskHandler Mockery */
        $changeOfTaskHandler = m::mock(ChangeOfTaskHandler::class);
        $changeOfTaskHandler->shouldReceive('setUserId')->once();
        $changeOfTaskHandler->shouldReceive('handle')->never();

        /* var $xmlResponseBuilder XMLResponseBuilder */
        $xmlResponseBuilder = new XMLResponseBuilder(m::mock(TaskXMLConverter::class));

        /* var $tasksSyncManager TasksSyncManager */
        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandler, $xmlResponseBuilder);
        $userId = 1;
        $tasksSyncManager->setUserId($userId);

        // testing...
        $tasksSyncManager->handleTasksFromDevice($changedTasks);
    }

    public function testOneTaskFromDevice()
    {
        $changedTasksXML = ''.
            '<changedTasks>' .
                '<changeOfTask localId="1" globalId="11"><someXmlData/></changeOfTask>' .
            '</changedTasks>';
        $changedTasks = new SimpleXMLElement($changedTasksXML);

        /* var $ChangeOfTaskHandler Mockery */
        $changeOfTaskHandler = m::mock(ChangeOfTaskHandler::class);
        $changeOfTaskHandler->shouldReceive('setUserId')->once();
        $changeOfTaskHandler->shouldReceive('handle')->once()->andReturn(11);

        /* var $xmlResponseBuilder Mockery */
        $xmlResponseBuilder =  m::mock(XMLResponseBuilder::class);
        $xmlResponseBuilder->shouldReceive('prepareSyncObjectsXml')->once()->with([1 => 11]);

        /* var $tasksSyncManager TasksSyncManager */
        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandler, $xmlResponseBuilder);
        $userId = 1;
        $tasksSyncManager->setUserId($userId);

        // testing
        $tasksSyncManager->handleTasksFromDevice($changedTasks);
    }

    public function testThreeTasksFromDevice()
    {
        $changedTasksXML = ''.
                '<changedTasks>' .
                    '<changeOfTask localId="1" globalId="11"><someXmlData/></changeOfTask>' .
                    '<changeOfTask localId="2" globalId="12"><someXmlData/></changeOfTask>' .
                    '<changeOfTask localId="3" globalId="0"><someXmlData/></changeOfTask>' .
                '</changedTasks>';
        $changedTasks = new SimpleXMLElement($changedTasksXML);

        /* var $ChangeOfTaskHandler Mockery */
        $changeOfTaskHandler = m::mock(ChangeOfTaskHandler::class);
        $changeOfTaskHandler->shouldReceive('setUserId')->once();
        $changeOfTaskHandler->shouldReceive('handle')->times(3)->andReturn(11,12,13);

        /* var $xmlResponseBuilder Mockery */
        $xmlResponseBuilder = m::mock(XMLResponseBuilder::class);
        $xmlResponseBuilder->shouldReceive('prepareSyncObjectsXml')->once()->with([1 => 11, 2 => 12, 3 => 13]);

        /* var $tasksSyncManager TasksSyncManager */
        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandler, $xmlResponseBuilder);
        $userId = 1;
        $tasksSyncManager->setUserId($userId);

        // testing ...
        $tasksSyncManager->handleTasksFromDevice($changedTasks);
    }
}
