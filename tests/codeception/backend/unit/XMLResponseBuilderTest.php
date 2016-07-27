<?php

namespace tests\codeception\backend\unit;


use \backend\components\XMLResponseBuilder;


class XMLResponseBuilderTest extends \yii\codeception\TestCase
{
    public $appConfig = '@tests/codeception/config/backend/unit.php';

    public function testBuildXMLResponse() {
        $serverChanges = array();

        $serverChanges['tasks']['created'] = array();
        $serverChanges['tasks']['updated'] = array();
        $serverChanges['tasks']['deleted'] = array();

        /* Task 15 */
        $taskData = array(
            'id' => 15,
            'message' => "Message 15",
            'description' => "Description 15",
            'status' => "WAITING",
            'conditions' => array(
                array(
                'id' => 151,
                'task_id' => 15,
                'events' => array(
                    array(
                        'id' => 1511,
                        'type' => 1,
                        'typeName' => 'GPS',
                        'params' => '{"lat":55.613698562476, "lng":38.118674755096, "radius":100, "address":"??. ??????? ????????,  24,  ?????????"}'
                    )
                )
            ))
        );
        $task15 = $this->createMockTask($taskData);
        $task15ChangesDatetime = "2016-05-04 11:05:30";
        $task15ChangesAction = "CREATED";
        $serverChanges['tasks']['created'][$task15->id]['action'] = $task15ChangesAction;
        $serverChanges['tasks']['created'][$task15->id]['datetime'] = $task15ChangesDatetime;
        $serverChanges['tasks']['created'][$task15->id]['object'] = $task15;

        /* Task 14 */
        $taskData = array(
            'id' => 14,
            'message' => "Message 14",
            'description' => "Description 14",
            'status' => "WAITING",
            'conditions' => array(
                array(
                    'id' => 141,
                    'task_id' => 14,
                    'events' => array(
                        array(
                            'id' => 1411,
                            'type' => 1,
                            'typeName' => 'GPS',
                            'params' => '{"lat":55.599191,"lng":38.125281,"radius":100,"address":"??. ??????,  12,  ?????????","placeId":"ChIJ26i2RlC_SkEReagfvz6w5bs"}'
                        )
                    )
                ),
                array(
                    'id' => 142,
                    'task_id' => 14,
                    'events' => array(
                        array(
                            'id' => 1421,
                            'type' => 2,
                            'typeName' => 'TIME',
                            'params' => '{"datetime":"23-04-2016 19:00:30","offset":180}'
                        )
                    )
                )
            )
        );
        $task14 = $this->createMockTask($taskData);
        $task14ChangesDatetime = "2016-05-03 11:05:30";
        $task14ChangesAction = "UPDATED";
        $serverChanges['tasks']['updated'][$task14->id]['action'] = $task14ChangesAction;
        $serverChanges['tasks']['updated'][$task14->id]['datetime'] = $task14ChangesDatetime;
        $serverChanges['tasks']['updated'][$task14->id]['object'] = $task14;

        $task11Id = 11;
        $task11ChangesDatetime = "2016-05-07 11:15:20";
        $serverChanges['tasks']['deleted'][$task11Id]['action'] = "Deleted";
        $serverChanges['tasks']['deleted'][$task11Id]['datetime'] = $task11ChangesDatetime;

        $synchronizedTask10 = array('globalId' => 10, 'localId' => 1);
        $synchronizedObjects = array();
        $synchronizedObjects['tasks'] = array();
        $synchronizedObjects['tasks'][$synchronizedTask10['localId']] = $synchronizedTask10['globalId'];

        $lastSyncTime = "2016-04-05 11:37:10";

        $tokenJSON = "{acceessToken: Ac_c_ess_Token#, param1: val1}";

        $actualXMLStr = XMLResponseBuilder::buildXMLResponse($serverChanges, $synchronizedObjects, $lastSyncTime, $tokenJSON);

        $expectedXMLStr =
            '<?xml version="1.0" encoding="UTF-8"?>' .
            '<syncResponse>' .
                '<tasks>' .
                    '<created>' .
                        '<task global-id="' . $task15->id . '" time-changes="' . $task15ChangesDatetime . '">' .
                            '<message>' . $task15->message . '</message>' .
                            '<description>' . $task15->description . '</description>' .
                            '<conditions>';
                                $condition = $task15->conditions[0];
                                $expectedXMLStr .= "<condition id='" . $condition->id . "' task-id='" . $task15->id . "'>";
                                    $event = $condition->events[0];
                                    $params = json_decode($event->params);
                                    $expectedXMLStr .= "<event type='" . $event->eventType->name . "' id='" . $event->id . "'>" .
                                        "<params>" .
                                            "<lat>" . $params->lat . "</lat>" .
                                            "<lng>" . $params->lng . "</lng>" .
                                            "<radius>" . $params->radius . "</radius>" .
                                            "<address>" . $params->address . "</address>" .
                                        "</params>" .
                                    "</event>";
                                    $expectedXMLStr .= "</condition>";
                            $expectedXMLStr .= '</conditions>' .
                            '<status>' . $task15->status . '</status>' .
                            '</task>' .
                    '</created>' .
                    '<updated>' .
                        '<task global-id="' . $task14->id . '" time-changes="' . $task14ChangesDatetime . '">' .
                            '<message>' . $task14->message . '</message>' .
                            '<description>' . $task14->description . '</description>' .
                            '<conditions>';
                                $condition = $task14->conditions[0];
                                $expectedXMLStr .= "<condition id='" . $condition->id . "' task-id='" . $task14->id . "'>";
                                    $event = $condition->events[0];
                                    $params = json_decode($event->params);
                                    $expectedXMLStr .= "<event type='" . $event->eventType->name . "' id='" . $event->id . "'>" .
                                        "<params>" .
                                            "<lat>" . $params->lat . "</lat>" .
                                            "<lng>" . $params->lng . "</lng>" .
                                            "<radius>" . $params->radius . "</radius>" .
                                            "<address>" . $params->address . "</address>" .
                                            "<placeId>" . $params->placeId . "</placeId>" .
                                        "</params>" .
                                    "</event>";
                            $expectedXMLStr .= "</condition>";
                            $condition = $task14->conditions[1];
                            $expectedXMLStr .= "<condition id='" . $condition->id . "' task-id='" . $task14->id . "'>";
                                $event = $condition->events[0];
                                $params = json_decode($event->params);
                                $expectedXMLStr .= "<event type='" . $event->eventType->name . "' id='" . $event->id . "'>" .
                                    "<params>" .
                                        "<datetime>" . $params->datetime . "</datetime>" .
                                        "<offset>" . $params->offset . "</offset>" .
                                    "</params>" .
                                    "</event>";
                            $expectedXMLStr .= "</condition>";
                            $expectedXMLStr .= '</conditions>' .
                            '<status>' . $task15->status . '</status>' .
                        '</task>' .
                    '</updated>' .
                    '<deleted>' .
                        '<deletedTask global-id="' . $task11Id . '" time-changes="' . $task11ChangesDatetime . '"' . '></deletedTask>' .
                    '</deleted>' .
                '</tasks>' .
                '<synchronizedObjects>' .
                    '<synchronizedTasks>' .
                        '<synchronizedTask>' .
                            '<localId>' . $synchronizedTask10['localId'] . '</localId><globalId>' . $synchronizedTask10['globalId'] . '</globalId>' .
                        '</synchronizedTask>' .
                    '</synchronizedTasks>' .
                '</synchronizedObjects>' .
                '<lastSyncTime>' . $lastSyncTime . '</lastSyncTime>' .
                "<accessToken>\"" . $tokenJSON . "\"</accessToken>" .
            "</syncResponse>";
        //file_put_contents("expectedXMLStr.xml", $expectedXMLStr);
        //file_put_contents("actualXMLStr.xml", $actualXMLStr);


        $this->assertXmlStringEqualsXmlString($expectedXMLStr,$actualXMLStr);
    }

    private function createMockTask($taskData) {
        $mockTask = $this->getMockBuilder('Task')
            ->setConstructorArgs(array())
            ->getMock();
        $mockTask->id = $taskData['id'];
        $mockTask->message = $taskData['message'];
        $mockTask->description = $taskData['description'];
        $mockTask->status = $taskData['status'];
        $conditions = array();
        foreach ($taskData['conditions'] as $conditionData) {
            $condition = $this->createMockCondition($conditionData);
            $conditions[] = $condition;
        }
        $mockTask->conditions = $conditions;
        return $mockTask;
    }


    private function createMockCondition($conditionData) {
        $conditionMock = $this->getMockBuilder('Condition')
            ->setConstructorArgs(array())
            ->setMethods(array('validate'))
            ->getMock();
        $conditionMock->id = $conditionData['id'];
        $conditionMock->task_id = $conditionData['task_id'];
        $events = array();
        foreach ($conditionData['events'] as $eventData) {
            $event = $this->createMockEvent($eventData);
            $events[] = $event;
        }
        $conditionMock->events = $events;
        $conditionMock->method('validate')
            ->willReturn(true);

        return $conditionMock;
    }

    private function createMockEvent($eventData) {
        $eventMock = $this->getMockBuilder('Event')
            ->setConstructorArgs(array())
            ->getMock();

        $eventMock->id = $eventData['id'];
        $eventMock->type = $eventData['type'];
        $eventType = $this->getMockBuilder('EventType')
            ->setConstructorArgs(array())
            ->getMock();
        $eventType->name = $eventData['typeName'];
        $eventMock->eventType = $eventType;
        $eventMock->params = $eventData['params'];

        return $eventMock;
    }
}
