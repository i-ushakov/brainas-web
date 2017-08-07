<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 8/15/2016
 * Time: 12:17 AM
 */

namespace frontend\components;

use \common\models\Task;

/**
 * Class TaskConverter
 * Prepare task (\common\models\Task) object with conditions for sending to client side
 * @package frontend\components
 */
class TaskConverter
{
    /**
     * Prepare task (\common\models\Task) object with conditions for sending to client side
     * @param Task $task
     * @return mixed
     */
    public function prepareTaskForResponse(Task $task)
    {
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
            $eventName = $condition->eventType->name;
            $c[$eventName]['eventId'] = $condition->id;
            $c[$eventName]['type'] = $eventName;
            $c[$eventName]['params'] = json_decode($condition->params);
            $item['conditions'][] = $c;
        }
        return $item;
    }
}