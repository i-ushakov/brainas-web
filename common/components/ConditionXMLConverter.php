<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 11/28/2016
 * Time: 5:37 PM
 */

namespace common\components;

use common\models\EventType;
use common\nmodels\Condition;

class ConditionXMLConverter {

    public function fromXML(\SimpleXMLElement $xml) {
        if ($xml == null) {
            return null;
        }
        $condition = new Condition;
       if (isset($xml['globalId']) && $xml['globalId'] != 0) {
            $condition->id = intval($xml['globalId']);
        } else {
            $condition->id = 0;
        }

        if (isset($xml['type'])) {
           $type = (String)$xml['type'];
           try {
               $condition->type = EventType::getTypeIdByName($type);
           } catch (BAException $e) {
               throw $e;
           }
       }

       if (isset($xml->params)) {
           $params = (String)$xml->params;
           $condition->params = $params;
       }

        return $condition;
    }

    public function toXML($task) {
        $xml = "";
        $conditions = $task->conditions;
        foreach($conditions as $condition){
            if ($condition->validate()) {
                $xml .= "<condition globalId='" . $condition->id . "' task-id='" . $condition->task_id . "'>";
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