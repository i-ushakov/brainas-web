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

    public function testGetTasksFromDeviceWrongParamEx()
    {
        $changedTasksXML = ''.
            '<wrongElement>' .
                '<changeOfTask localId="1" globalId="11"><someXmlData/></changeOfTask>' .
                '<changeOfTask localId="2" globalId="12"><someXmlData/></changeOfTask>' .
                '<changeOfTask localId="3" globalId="0"><someXmlData/></changeOfTask>' .
            '</wrongElement>';
        $changedTasksObj = new SimpleXMLElement($changedTasksXML);
        $changeOfTaskHandlerProxy = $this->prepareTaskHandlerProxy();
        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandlerProxy->getObject());
        $this->tester->expectException(new BAException(TasksSyncManager::WRONG_ROOT_ELEMNT, BAException::WRONG_ROOT_XML_ELEMENT_NAME), function() use ($tasksSyncManager,$changedTasksObj){
            $tasksSyncManager->getTasksFromDevice($changedTasksObj);
        });
    }

    public function testGetTasksFromDevice0()
    {
        $changedTasksXML = ''.
            '<changedTasks></changedTasks>';
        $changedTasks = new SimpleXMLElement($changedTasksXML);
        $changeOfTaskHandlerProxy = $this->prepareTaskHandlerProxy();
        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandlerProxy->getObject());
        $tasksSyncManager->getTasksFromDevice($changedTasks);
        $changeOfTaskHandlerProxy->verifyNeverInvoked('handle');
    }

    public function testGetTasksFromDevice1()
    {
        $changedTasksXML = ''.
            '<changedTasks>' .
                '<changeOfTask localId="1" globalId="11"><someXmlData/></changeOfTask>' .
            '</changedTasks>';
        $changedTasks = new SimpleXMLElement($changedTasksXML);
        $changeOfTaskHandlerProxy = $this->prepareTaskHandlerProxy();
        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandlerProxy->getObject());
        $synchronizedTasks =  $tasksSyncManager->getTasksFromDevice($changedTasks);
        $changeOfTaskHandlerProxy->verifyInvokedOnce('handle');
        $this->tester->assertEquals([1 => 11], $synchronizedTasks, "Wrong synchronized tasks array");
    }

    public function testGetTasksFromDevice3()
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
        $synchronizedTasks = $tasksSyncManager->getTasksFromDevice($changedTasks);

        $this->tester->assertEquals(
            [1 => 11, 2 => 12, 3 => 13], $synchronizedTasks, "Wrong synchronized tasks array");
    }

    protected function prepareTaskHandlerProxy()
    {
        $changeOfTaskParserMock = test::double(ChangeOfTaskParser::class, [])->construct();
        $taskXMLConverterMock = test::double(TaskXMLConverter::class, [])->make();
        $userIdMock = 1;
        $changeOfTaskHandlerProxy = test::double(
            new ChangeOfTaskHandler($changeOfTaskParserMock, $taskXMLConverterMock, $userIdMock),
            ['handle' => 11]
        );
        return $changeOfTaskHandlerProxy;
    }
}
