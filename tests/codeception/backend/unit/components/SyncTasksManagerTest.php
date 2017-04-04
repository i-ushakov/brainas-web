<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/5/2017
 * Time: 3:48 PM
 */
use backend\components\TasksSyncManager;
use backend\components\ChangeOfTaskHandler;
use backend\components\ChangeOfTaskParser;
use common\nmodels\TaskXMLConverter;
use common\components\BAException;

use AspectMock\Test as test;
use Mockery as m;

class TasksSyncManagerTest extends \Codeception\TestCase\Test
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

    public function testHandleTasksFromDevice_ThrowWrongParamEx()
    {
        $changedTasksXML = ''.
            '<wrongElement><someXmlData/></wrongElement>';
        $changedTasksObj = new SimpleXMLElement($changedTasksXML);
        $changeOfTaskHandlerProxy = $this->prepareTaskHandlerMock();
        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandlerProxy->getObject());
        $this->tester->expectException(new BAException(TasksSyncManager::WRONG_ROOT_ELEMNT, BAException::WRONG_ROOT_XML_ELEMENT_NAME), function() use ($tasksSyncManager,$changedTasksObj){
            $tasksSyncManager->handleTasksFromDevice($changedTasksObj);
        });
    }

    public function testHandleTasksFromDevice0()
    {
        $changedTasksXML = ''.
            '<changedTasks></changedTasks>';
        $changedTasks = new SimpleXMLElement($changedTasksXML);

        $changeOfTaskHandler = m::mock(ChangeOfTaskHandler::class);
        $changeOfTaskHandler->shouldReceive('setUserId')->once();
        $changeOfTaskHandler->shouldReceive('handle')->never();

        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandler);
        $userId = 1;
        $tasksSyncManager->setUserId($userId);
        $tasksSyncManager->handleTasksFromDevice($changedTasks);
    }

    public function testHandleTasksFromDevice1()
    {
        $changedTasksXML = ''.
            '<changedTasks>' .
                '<changeOfTask localId="1" globalId="11"><someXmlData/></changeOfTask>' .
            '</changedTasks>';
        $changedTasks = new SimpleXMLElement($changedTasksXML);
        $changeOfTaskHandlerProxy = $this->prepareTaskHandlerMock();
        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandlerProxy->getObject());
        $synchronizedTasks =  $tasksSyncManager->handleTasksFromDevice($changedTasks);
        $changeOfTaskHandlerProxy->verifyInvokedOnce('handle');
        $this->tester->assertEquals([1 => 11], $synchronizedTasks, "Wrong synchronized tasks array");
    }

    public function testHandleTasksFromDevice3()
    {
        $changedTasksXML = ''.
                '<changedTasks>' .
                    '<changeOfTask localId="1" globalId="11"><someXmlData/></changeOfTask>' .
                    '<changeOfTask localId="2" globalId="12"><someXmlData/></changeOfTask>' .
                    '<changeOfTask localId="3" globalId="0"><someXmlData/></changeOfTask>' .
                '</changedTasks>';
        $changedTasks = new SimpleXMLElement($changedTasksXML);

        $changeOfTaskHandler = m::mock(ChangeOfTaskHandler::class);
        $changeOfTaskHandler->shouldReceive('handle')->times(3)->andReturn(11, 12, 13);

        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandler);
        $synchronizedTasks = $tasksSyncManager->handleTasksFromDevice($changedTasks);

        $this->tester->assertEquals(
            [1 => 11, 2 => 12, 3 => 13], $synchronizedTasks, "Wrong synchronized tasks array");
    }
    public function testPrepareSyncObjectsXml()
    {
        $changeOfTaskHandler = m::mock(ChangeOfTaskHandler::class);
        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandler);
        $synchronizedTasks = [
            1 => 11,
            2 => 12
        ];
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
        $result = $tasksSyncManager->prepareSyncObjectsXml($synchronizedTasks);
        $this->tester->assertEquals($synchronizedObjsXml, $result, "Wrong xml with synchronized objects");
    }

    protected function prepareTaskHandlerMock()
    {
        $changeOfTaskParserMock = test::double(ChangeOfTaskParser::class, [])->construct();
        $taskXMLConverterMock = test::double(TaskXMLConverter::class, [])->make();
        $userIdMock = 1;
        $changeOfTaskHandlerMock = test::double(
            new ChangeOfTaskHandler($changeOfTaskParserMock, $taskXMLConverterMock, $userIdMock),
            ['handle' => 11]
        );
        return $changeOfTaskHandlerMock;
    }


}
