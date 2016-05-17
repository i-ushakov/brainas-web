<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 5/12/2016
 * Time: 6:59 PM
 */
namespace tests\codeception\backend\unit;


use \backend\components\TaskXMLHelper;
use \common\models\Task as Task;
use \common\models\Condition;
use \common\models\Event;
use AspectMock\Test as test;
use yii\db\ActiveRecord;

class TaskXMLHelperTest extends \yii\codeception\TestCase {
    public $appConfig = '@tests/codeception/config/backend/unit.php';

    protected function tearDown()
    {
        test::clean();
        Condition::deleteAll();
        Event::deleteAll();

    }

    public function testAddConditionFromXML() {
        $condition = new Condition();
        $condition->id = 1375;
        $condition->task_id = 1;
        $condition->save();

        $oldEventParams = '{"offset":-60,"datetime":"10-05-2016 23:23:23"}';
        $newEventParams = '{"offset":-180,"datetime":"10-05-2016 23:21:15"}';
        $event = new Event();
        $event->id = 1445;
        $event->condition_id = 1375;
        $event->type = 2;
        $event->params = $oldEventParams;
        $event->save();

        $conditionXML1 =
            '<condition localId="4" globalId="1375">
                <events>
                    <event localId="4" globalId="1445">
                        <type>TIME</type>
                        <params>' . $newEventParams .'</params>
                    </event>
                </events>
            </condition>';

        $conditionXML2 =
            '<condition localId="5" globalId="0">
                <events>
                    <event localId="5" globalId="0">
                        <type>GPS</type>
                        <params>{"address":"ул. Фрунзе, 8, Zhukovskiy","radius":100,"lng":38.12765311449766,"lat":55.59999073887121}</params>
                    </event>
                </events>
            </condition>';

        $taskMock = test::double(new Task);
        $taskMock->id = 1;

        $actualSynchronizedObjects = array();
        $actualSynchronizedObjects['conditions'] = array();
        $actualSynchronizedObjects['events'] = array();
        $expectedSynchronizedObjects = array(
            'conditions' => array(4 => 375, 5 => 0),
            'events' => array(4 => 455, 5 => 0)
        );

        TaskXMLHelper::addConditionFromXML(new \SimpleXMLElement(($conditionXML1)), $taskMock->id, $actualSynchronizedObjects);
        TaskXMLHelper::addConditionFromXML(new \SimpleXMLElement(($conditionXML2)), $taskMock->id, $actualSynchronizedObjects);

        $this->assertEquals(array_keys($expectedSynchronizedObjects['conditions']), array_keys($actualSynchronizedObjects['conditions']));
        $this->assertEquals(array_keys($expectedSynchronizedObjects['events']), array_keys($actualSynchronizedObjects['events']));
        $this->assertEquals(0, Event::find()->where(['params' => $oldEventParams])->count());
        $this->assertEquals(1, Event::find()->where(['params' => $newEventParams])->count());
}
}