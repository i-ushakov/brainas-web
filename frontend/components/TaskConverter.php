<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 8/15/2016
 * Time: 12:17 AM
 */

namespace frontend\components;


class TaskConverter {
    public static function prepareTaskForResponse($task) {
        $item['id'] = $task->id;
        $item['message'] = $task->message;
        $item['description'] = nl2br($task->description);
        $item['status'] = nl2br($task->status);
        $item['created'] = $task->created;
        $item['changed'] = $task->changeOfTask->datetime;

        if ($task->picture != null) {
            $item['picture_name'] = $task->picture->name;
            $item['picture_file_id'] = $task->picture->file_id;
        }
        $conditions = $task->conditions;
        foreach ($conditions as $condition) {
            $c = array();
            $c['conditionId'] = $condition->id;
            $events = $condition->events;
            foreach ($events as $event) {
                $c[$event->eventType->name]['eventId'] = $event->id;
                $c[$event->eventType->name]['type'] = $event->eventType->name;
                $c[$event->eventType->name]['params'] = json_decode($event->params);
            }
            $item['conditions'][] = $c;
        }
        return $item;
    }
}