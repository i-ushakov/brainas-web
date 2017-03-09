<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/5/2017
 * Time: 3:48 PM
 */
use \backend\components\TasksSyncManager;
use \backend\helpers\ChangeOfTaskHandler;
use \backend\helpers\ChangeOfTaskParser;
use common\nmodels\TaskXMLConverter;
use common\nmodels\ConditionXMLConverter;
use \common\components\BAException;

use AspectMock\Test as test;

class TasksSyncManagerTest extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        test::clean(); // remove all registered test doubles
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
        $this->tester->expectException(new BAException(TasksSyncManager::WRONG_ROOT_ELEMNT, BAException::INVALID_PARAM_EXCODE), function() use ($tasksSyncManager,$changedTasksObj){
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
        $tasksSyncManager->getTasksFromDevice($changedTasks);
        $changeOfTaskHandlerProxy->verifyInvokedOnce('handle',3);
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
        $changeOfTaskHandlerProxy = $this->prepareTaskHandlerProxy();
        $tasksSyncManager = new TasksSyncManager($changeOfTaskHandlerProxy->getObject());
        $tasksSyncManager->getTasksFromDevice($changedTasks);
        $changeOfTaskHandlerProxy->verifyInvokedMultipleTimes('handle',3);
    }

    protected function prepareTaskHandlerProxy()
    {
        $changeOfTaskParserMock = test::double(ChangeOfTaskParser::class, [])->construct();
        $taskXMLConverterMock = test::double(TaskXMLConverter::class, [])->make();
        $userIdMock = 1;
        $changeOfTaskHandlerProxy = test::double(
            new ChangeOfTaskHandler($changeOfTaskParserMock, $taskXMLConverterMock, $userIdMock),
            ['handle' => null]
        );

        return $changeOfTaskHandlerProxy;
    }
}
