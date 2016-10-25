<?php

/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 5/2/2016
 * Time: 11:26 AM
 */
namespace backend\components;

use Yii;
use \common\models\Task;
use \common\models\Condition;
use \common\models\Event;
use \common\models\EventType;


class TaskXMLHelper {

    static public function addConditionFromXML(\SimpleXMLElement $conditionXML, $taskId, &$synchronizedObjects) {
        if (isset($conditionXML['globalId']) && $conditionXML['globalId'] != 0) {
            $conditionId = intval($conditionXML['globalId']);
            $condition = Condition::find()->where(['id' => $conditionId])->one();
        } else {
            $condition = new Condition();
            $condition->task_id = $taskId;
            $condition->save();
        }

        if ($condition != null) {
            $task = Task::findOne($taskId);
            \Yii::warning("===task->status31" . $task->status);
            self::cleanDeletedEvents($conditionXML->events->event, $conditionXML->id);
            $task = Task::findOne($taskId);
            \Yii::warning("===task->status32" . $task->status);
            foreach ($conditionXML->events->event as $e) {
                self::addEventFromXML($e, $condition->id, $synchronizedObjects);
            }
            $task = Task::findOne($taskId);
            \Yii::warning("===task->status33" . $task->status);
            Task::findOne(['id' => $taskId])->save();
            $synchronizedObjects['conditions'][(string)$conditionXML['localId']] = $condition->id;
        }
    }

    static public function addEventFromXML(\SimpleXMLElement $eventXML, $conditionId, &$synchronizedObjects) {
        if (isset($eventXML['globalId']) && $eventXML['globalId'] != 0) {
            $eventId = intval($eventXML['globalId']);
            $event = Event::find()->where(['id' => $eventId])->one();
        } else {
            $event = new Event();
            $event->condition_id = $conditionId;
            $event->save();
        }
        if ($event != null) {
            $event->type = EventType::getTypeIdByName((string)$eventXML->type);
            $event->params = (string)$eventXML->params;
            $event->save();
            $synchronizedObjects['events'][(string)$eventXML['localId']] = (string)$eventXML['globalId'];
        }
    }

    static public function cleanDeletedConditions(\SimpleXMLElement $conditionsXML, $taskId) {
        $conditionsIds = array();
        foreach ($conditionsXML as $conditionXML) {
            if (isset($conditionXML['globalId']) && $conditionXML['globalId'] != 0) {
                $conditionsIds[] = $conditionXML['globalId'];
            }
        }
        $conditionsFromDB = Condition::find()
            ->where(['task_id' => $taskId])
            ->all();
        foreach($conditionsFromDB as $conditionFromDB) {
            if(!in_array ($conditionFromDB->id, $conditionsIds)) {
                $conditionFromDB->delete();
            }
        }
    }

    static public function cleanDeletedEvents(\SimpleXMLElement $eventsXML, $conditionId) {
        $eventsIds = array();
        foreach ($eventsXML as $eventXML) {
            if (isset($eventXML['globalId']) && $eventXML['globalId'] != 0) {
                $eventsIds[] = $eventXML['globalId'];
            }
        }
        $eventsFromDB = Event::find()
            ->where(['condition_id' => $conditionId])
            ->all();
        foreach($eventsFromDB as $eventFromDB) {
            if(!in_array ($eventFromDB->id, $eventsIds)) {
                $eventFromDB->delete();
            }
        }
    }

    static public function retrieveExistingTasksFromXML(\SimpleXMLElement $allDeviceChangesInXML) {
        $json = $allDeviceChangesInXML->existingTasks;
        $existingTasks = json_decode($json, true);
        return $existingTasks;
    }
}