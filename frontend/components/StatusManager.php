<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 8/1/2017
 * Time: 5:06 PM
 */

namespace frontend\components;

/**
 * Class StatusManager
 * Checking and Updating statuses of tasks
 *
 * @package frontend\components
 */
class StatusManager
{
    const STATUS_ACTIVE = "ACTIVE";
    const STATUS_WAITING = "WAITING";
    const STATUS_DISABLED = "DISABLED";
    const STATUS_TODO = "TODO";

    /**
     * Check and Update (if needed) status of task
     * @param $task
     * @return mixed
     */
    public function updateStatus($task)
    {
        switch ($task->status) {
            case self::STATUS_ACTIVE :
                break;

            case self::STATUS_WAITING :
                $conditions = $task->getConditions();
                if (count($conditions) == 0) {
                    $task->status = self::STATUS_DISABLED;
                }
                break;

            case self::STATUS_TODO :
                if (count($task->conditions) > 0) {
                    $task->status = self::STATUS_WAITING;
                }
                break;
        }
        return $task;
    }
}