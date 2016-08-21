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
                case  "CREATION":
                    break;
                default :
                    $tasks = $tasksGetQuery->orderBy('sync_changed_tasks.datetime')->all();
                    break;
            }
        } else {
            $tasks = $tasksGetQuery->orderBy('sync_changed_tasks.datetime')->all();
        }


        return $tasks;
    }
}