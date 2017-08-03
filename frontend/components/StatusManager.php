<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 8/1/2017
 * Time: 5:06 PM
 */

namespace frontend\components;


class StatusManager
{
    const STATUS_ACTIVE = "ACTIVE";
    const STATUS_WAITING = "WAITING";
    const STATUS_DISABLED = "DISABLED";
    const STATUS_TODO = "TODO";

    public function updateStatus($task)
    {
        switch ($task->status) {
            case self::STATUS_ACTIVE :
                return $task;

            case self::STATUS_WAITING :
                $conditions = $task->getConditions();
                if (count($conditions) == 0) {
                    $task->status = self::STATUS_DISABLED;
                }
                return $task;

            case self::STATUS_TODO :
                if (count($task->conditions) > 0) {
                    $task->status = self::STATUS_WAITING;
                }
                return $task;
        }
        return $task;
    }
}