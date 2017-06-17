<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/29/2017
 * Time: 2:58 PM
 */

use backend\components\ChangeOfTaskParser;
use backend\components\ChangeOfTaskHandler;
use common\components\TaskXMLConverter;
use common\models\Condition;

use Mockery as m;

class ChangeOfTaskHandler_CleanDeletedConditions_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testCleanDeletedConditions()
    {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);
        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $userId = 1;

        /* var $changeOfTaskHandler ChangeOfTaskHandler */
        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[]',
            [$changeOfTaskParser, $taskXMLConverter, $userId]
        );

        $taskId = 88;

        /* var $conditionFromDevice1 Condition */
        $conditionFromDevice1 = new Condition([
            'id'=> 101,
            'task_id' => $taskId,
            'type' => 2,
            'params' => 'time_params_updated'
        ]);

        /* var $conditionFromDevice2 Condition */
        $conditionFromDevice2 = new Condition([
            'id'=> 0,
            'task_id' => $taskId,
            'type' => 1,
            'params' => 'location_params_new'
        ]);

        $conditionsFromDevice = [$conditionFromDevice1, $conditionFromDevice2];

        // preparing DB
        $existCondition99 = new Condition([
            'id' => 99,
            'task_id' => $taskId,
            'type' => 1,
            'params' => 'time_params'
        ]);
        $existCondition99->save();

        $existCondition101 = new Condition([
            'id' => 101,
            'task_id' => $taskId,
            'type' => 2,
            'params' => 'time_params'
        ]);
        $existCondition101->save();

        // testing ...
        $changeOfTaskHandler->cleanDeletedConditions($conditionsFromDevice, $taskId);

        // assertions:
        $conditions99 = Condition::findOne(['id' => 99]);
        $this->tester->assertTrue(!isset($conditions99));
    }
}