<?php

/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 5/2/2016
 * Time: 11:26 AM
 */
namespace backend\components;

class XMLResponseBuilder {

    static function buildXMLResponse($serverChanges, $synchronizedObjects, $lastSyncTime, $token) {
        $xmlResponse = "";
        $xmlResponse .= '<?xml version="1.0" encoding="UTF-8"?>';
        $xmlResponse .= '<syncResponse>';

        /* Tasks */
        $xmlResponse .= '<tasks>';
        // Created tasks
        $xmlResponse .= '<created>';
        foreach ($serverChanges['tasks']['created'] as $id => $serverChange) {
            $xmlResponse .= self::buildXmlOfTask($serverChange['object'],  $serverChange['datetime']);
        }
        $xmlResponse .= '</created>';

        // Updated tasks
        $xmlResponse .= '<updated>';
        foreach ($serverChanges['tasks']['updated'] as $id => $serverChange) {
            if (isset($serverChange['object']) && !empty($serverChange['object'])) {
                $xmlResponse .= self::buildXmlOfTask($serverChange['object'], $serverChange['datetime']);
            } else {
                \Yii::info(
                    "We have a server change without object with datetime = " . $serverChange['datetime'] .
                    " for task with id = " . $id, "MyLog"
                );
            }
        }
        $xmlResponse .= '</updated>';

        // Deleted Tasks
        $xmlResponse .= '<deleted>';
        foreach ($serverChanges['tasks']['deleted'] as $globalId => $localId) {
            $xmlResponse .= '<deletedTask ' .
                'global-id="' . $globalId . '" ' .
                'local-id="' . $localId . '"' .
                '></deletedTask>';
        }
        $xmlResponse .= '</deleted>';
        $xmlResponse .= '</tasks>';

        /* Synchronized Objects */
        if (!empty($synchronizedObjects)) {
            $xmlResponse .= '<synchronizedObjects>';
            // Tasks
            $synchronizedTasks = $synchronizedObjects['tasks'];
            if (!empty($synchronizedTasks)) {
                $xmlResponse .= '<synchronizedTasks>';
                foreach($synchronizedTasks as $localId => $globalId) {
                    $xmlResponse .= "<synchronizedTask>" .
                        "<localId>" . $localId . "</localId>" .
                        "<globalId>" . $globalId . "</globalId>" .
                        "</synchronizedTask>";
                }
                $xmlResponse .= '</synchronizedTasks>';
            }
            // Conditions
            if (!empty($synchronizedObjects['conditions'])) {
                $synchronizedConditions = $synchronizedObjects['conditions'];
                $xmlResponse .= '<synchronizedConditions>';
                foreach($synchronizedConditions as $localId => $globalId) {
                    $xmlResponse .= "<synchronizedCondition>" .
                        "<localId>" . $localId . "</localId>" .
                        "<globalId>" . $globalId . "</globalId>" .
                        "</synchronizedCondition>";
                }
                $xmlResponse .= '</synchronizedConditions>';
            }

            // Events
            if (!empty($synchronizedObjects['events'])) {
                $synchronizedEvents = $synchronizedObjects['events'];
                $xmlResponse .= '<synchronizedEvents>';
                foreach($synchronizedEvents as $localId => $globalId) {
                    $xmlResponse .= "<synchronizedEvent>" .
                        "<localId>" . $localId . "</localId>" .
                        "<globalId>" . $globalId . "</globalId>" .
                        "</synchronizedEvent>";
                }
                $xmlResponse .= '</synchronizedEvents>';
            }
            $xmlResponse .= '</synchronizedObjects>';
        }

        $xmlResponse .= '<lastSyncTime>' . $lastSyncTime . '</lastSyncTime>';
        $xmlResponse .=  "<accessToken>" . json_encode($token) . "</accessToken>";

        $xmlResponse .= '</syncResponse>';

        return $xmlResponse;
    }

    public function buildXmlOfTask($task, $datetime) {
        $xml = '' .
            '<task globalId="' . $task->id . '" timeOfChange="' . $datetime . '">' .
            '<message>' . $task->message . '</message>' .
            '<description>' . $task->description . '</description>' .
            self::addPictureEntity($task->picture) .
            '<conditions>' . self::buildXmlOfConditions($task) . '</conditions>' .
            '<status>' . $task->status . '</status>' .
            '</task>';
        return $xml;
    }

    private static function buildXmlOfConditions($task) {
        $xml = "";
        $conditions = $task->conditions;
        foreach($conditions as $condition){
            if ($condition->validate()) {
                $xml .= "<condition id='" . $condition->id . "' task-id='" . $condition->task_id . "'>";
                $events = $condition->events;
                foreach ($events as $event) {
                    $xml .= "<event type='" . $event->eventType->name . "' id='" . $event->id . "'>";
                    $xml .= "<params>";
                    $params = json_decode($event->params);
                    foreach ($params as $name => $value) {
                        $xml .= "<$name>$value</$name>";
                    }
                    $xml .= "</params>";
                    $xml .= "</event>";
                }
                $xml .= "</condition>";
            }
        }
        return $xml;
    }

    private function addPictureEntity($picture) {
        $xmlPart = "";
        if (isset($picture) && isset($picture->name)) {
            $xmlPart =
                '<picture><name>' . $picture->name . '</name>';
            if (isset($picture->file_id)) {
                $xmlPart .= '<resourceId>' . $picture->file_id . '</resourceId>';
            }
            $xmlPart .= '</picture>';
        }
        return $xmlPart;
    }

    public function prepareXmlWithTasksChanges($changedTasks, $currentTime) {
        $xmlResponse = "";
        $xmlResponse .= '<?xml version="1.0" encoding="UTF-8"?>';

        $xmlResponse .= '<changes>';
        $xmlResponse .= '<tasks>';

        // Created tasks
        $xmlResponse .= '<created>';
        foreach ($changedTasks['created'] as $id => $changedTask) {
            $xmlResponse .= XMLResponseBuilder::buildXmlOfTask($changedTask['object'],  $changedTask['datetime']);
        }
        $xmlResponse .= '</created>';

        // Updated tasks
        $xmlResponse .= '<updated>';
        foreach ($changedTasks['updated'] as $id => $changedTask) {
            if (isset($changedTask['object']) && !empty($changedTask['object'])) {
                $xmlResponse .= self::buildXmlOfTask($changedTask['object'], $changedTask['datetime']);
            } else {
                \Yii::info(
                    "We have a server change without object with datetime = " . $changedTask['datetime'] .
                    " for task with id = " . $id, "MyLog"
                );
            }
        }
        $xmlResponse .= '</updated>';

        $xmlResponse .= '<deleted>';
        foreach ($changedTasks['deleted'] as $globalId => $localId) {
            $xmlResponse .= '<deletedTask ' .
                'globalId="' . $globalId . '" ' .
                'localId="' . $localId . '"' .
                '></deletedTask>';
        }
        $xmlResponse .= '</deleted>';

        $xmlResponse .= '</tasks>';

        $xmlResponse .= '<serverTime>' . $currentTime . '</serverTime>';

        $xmlResponse .= '</changes>';

        return $xmlResponse;
    }

    public function prepareSyncObjectsXml($synchronizedTasks)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<synchronizedTasks>';

        if (count($synchronizedTasks) > 0) {
            foreach ($synchronizedTasks as $localId => $globalId) {
                $xml .= "<synchronizedTask>" .
                    "<localId>$localId</localId>" .
                    "<globalId>$globalId</globalId>" .
                    "</synchronizedTask>";
            }
        }

        $xml .= '</synchronizedTasks>';
        return $xml;
    }
}