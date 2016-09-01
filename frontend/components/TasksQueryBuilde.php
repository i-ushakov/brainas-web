<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 8/14/2016
 * Time: 1:17 PM
 */

namespace frontend\components;


use common\models\Task;

class TasksQueryBuilde
{

    private $userId;

    public function __construct($userId) {
        $this->userId = $userId;
        return $this;
    }

    public function get($statusesFilter = null, $typeOfSort = null) {
        $tasksGetQuery = Task::find()
            ->where(['user' => $this->userId])
            ->leftJoin('sync_changed_tasks', '`sync_changed_tasks`.`task_id` = `tasks`.`id`')
            ->with('picture', 'changeOfTask');

        if ($statusesFilter != null) {
            $tasksGetQuery->andWhere(['status' => $statusesFilter]);
        }

        if ($typeOfSort != null) {
            switch ($typeOfSort){
                case "TIME_ADDED_NEWEST" :
                    $tasks = $tasksGetQuery->orderBy('created')->all();
                    break;
                case "TIME_ADDED_OLDEST" :
                    $tasks = $tasksGetQuery->orderBy('created desc')->all();
                    break;
                case  "LATEST_CHANGES":
                    $tasks = $tasksGetQuery->orderBy('sync_changed_tasks.datetime')->all();
                    break;
                case "TASK_TITLE" :
                    $tasks = $tasksGetQuery->orderBy('message')->all();
                    break;
                default :
                    $tasks = $tasksGetQuery->orderBy('created')->all();
                    break;
            }
        } else {
            $tasks = $tasks = $tasksGetQuery->orderBy('created')->all();
        }

        return $tasks;
    }
}