<?php

use tests\codeception\backend\FunctionalTester;
use \backend\components\TaskXMLHelper;

/* @var $scenario Codeception\Scenario */

return;
$task1 = array('model'=> array('id' => 1));
$condition1 = array(
    'model' => array('id' => 1001, 'task_id' => $task1['model']['id'])
);
$condition2 = array(
    'model' => array('id' => 1004, 'task_id' => $task1['model']['id']),
    'add' => array('localId' => 14)
);
$condition3 = array(
    'model' => array('id' => 0, 'task_id' => $task1['model']['id']),
    'add' => array('localId' => 15)
);
$event1 = array(
    'model' => array(
        'id' => 1001,
        'condition_id' => $condition1['model']['id'],
        'type' => 1,
        'params' => '{"address":"Unnamed Road, Test Test.,  Test","radius":100,"lng":60.1171875,"lat":56.944974180852}'
    )
);
$event2 = array(
    'model' => array(
        'id' => 1004,
        'condition_id' => $condition2['model']['id'],
        'type' => 2,
        'params' => '{"offset":0,"datetime":"10-05-2016 22:21:15"}'),
    'add' => array('localId' => 4, 'type' => 'GPS')
);
$event3 = array(
    'model' => array(
        'id' => 0,
        'condition_id' => $condition3['model']['id'],
        'type' => 1,
        'params' => '{"address":"ул. Фрунзе, 8889, Zhukovskiy","radius":100,"lng":38.12765311449766,"lat":55.59999073887121}'),
    'add' => array('localId' => 5, 'type' => 'TIME')
);
$conditionXML2 =
    '<condition localId="' . $condition2['add']['localId'] .'" globalId="' . $condition2['model']['id'] . '">
                <events>
                    <event localId="' . $event2['add']['localId'] . '" globalId="' . $event2['model']['id'] . '">
                        <type>' . $event2['add']['type'] .'</type>
                        <params>' . $event2['model']['params'] .'</params>
                    </event>
                </events>
            </condition>';
$conditionXML3 =
    '<condition localId="' . $condition3['add']['localId'] . '" globalId="' . $condition3['model']['id'] . '">
                <events>
                    <event localId="' . $event3['add']['localId'] . '" globalId="' . $event3['model']['id'] .'">
                        <type>' . $event3['add']['type'] .'</type>
                        <params>' . $event3['model']['params'] . '</params>
                    </event>
                </events>
    </condition>';

$conditionsXML = new SimpleXMLElement("<conditions>" . $conditionXML2 . $conditionXML3 . "</conditions>");


$I = new FunctionalTester($scenario);
$I->haveInDatabase('tasks', array('id' => 1));
$I->haveInDatabase('conditions', $condition1['model']);
$I->haveInDatabase('conditions', $condition2['model']);
$I->haveInDatabase('events', $event1['model']);
$I->haveInDatabase('events', $event2['model']);


$actualSynchronizedObjects = array();
$actualSynchronizedObjects['conditions'] = array();
$actualSynchronizedObjects['events'] = array();
$expectedSynchronizedObjects = array();
$expectedSynchronizedObjects['conditions'] = array();
$expectedSynchronizedObjects['events'] = array();
$expectedSynchronizedObjects['conditions'][$condition2['add']['localId']] = $condition2['model']['id'];
$expectedSynchronizedObjects['events'][$event2['add']['localId']] = $event2['model']['id'];
$expectedSynchronizedObjects['conditions'][$condition3['add']['localId']] = $condition3['model']['id'];
$expectedSynchronizedObjects['events'][$event3['add']['localId']] = $event3['model']['id'];

// TaskXMLHelper::cleanDeletedConditions
$I->seeInDatabase('events', array('params' => $event1['model']['params']));
TaskXMLHelper::cleanDeletedConditions($conditionsXML->condition, $task1['model']['id']);
$I->dontSeeInDatabase('events', array('params' => $event1['model']['params']));

// TaskXMLHelper::addConditionFromXML (work with DB)
$I->seeInDatabase('conditions', array('id' => $condition2['model']['id']));
TaskXMLHelper::addConditionFromXML(new \SimpleXMLElement($conditionXML2), $task1['model']['id'], $actualSynchronizedObjects);
TaskXMLHelper::addConditionFromXML(new \SimpleXMLElement($conditionXML3), $task1['model']['id'], $actualSynchronizedObjects);
$I->seeInDatabase('events', array('params' => $event2['model']['params']));
$I->seeInDatabase('events', array('params' => $event3['model']['params']));

// TaskXMLHelper::addConditionFromXML ($synchronizedObjects array)
$this->assertEquals(array_keys($expectedSynchronizedObjects['conditions']), array_keys($actualSynchronizedObjects['conditions']));
$this->assertEquals(array_keys($expectedSynchronizedObjects['events']), array_keys($actualSynchronizedObjects['events']));





