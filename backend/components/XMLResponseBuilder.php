<?php

/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 5/2/2016
 * Time: 11:26 AM
 */
namespace backend\components;

use \common\models\Task;

class XMLResponseBuilder {

    static function buildXMLResponse($serverChanges, $synchronizedObjects, $initSyncTime, $token) {
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
            $xmlResponse .= self::buildXmlOfTask($serverChange['object'],  $serverChange['datetime']);
        }
        $xmlResponse .= '</updated>';

        // Deleted Tasks
        $xmlResponse .= '<deleted>';
        foreach ($serverChanges['tasks']['deleted'] as $id => $d) {
            $xmlResponse .= '<deletedTask ' .
                'global-id="' . $id . '" ' .
                'time-changes="' . $d['datetime'] . '"' .
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

        $xmlResponse .= '<initSyncTime>' . $initSyncTime . '</initSyncTime>';
        $xmlResponse .=  "<accessToken>" . json_encode($token) . "</accessToken>";

        $xmlResponse .= '</syncResponse>';

        return $xmlResponse;
    }

    private static function buildXmlOfTask($task, $datetime) {
        $xml = '' .
            '<task global-id="' . $task->id . '" time-changes="' . $datetime . '">' .
            '<message>' . $task->message . '</message>' .
            '<description>' . $task->description . '</description>' .
            '<conditions>' . self::buildXmlOfConditions($task) . '</conditions>' .
            '<status>WAITING</status>' .
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
}