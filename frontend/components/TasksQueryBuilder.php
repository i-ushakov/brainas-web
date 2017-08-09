<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 8/14/2016
 * Time: 1:17 PM
 */

namespace frontend\components;


use common\components\BAException;
use common\models\Task;

/**
 * Class TasksQueryBuilde
 * Responsible for retrieving tasks from Databases
 * depending on different filter's statuses
 *
 * @package frontend\components
 */
class TasksQueryBuilder
{

    const SORTTYPE_NEWEST= "TIME_ADDED_NEWEST";
    const SORTTYPE_OLDEST= "TIME_ADDED_OLDEST";
    const SORTTYPE_LATEST_CHANGES= "LATEST_CHANGES";
    const SORTTYPE_TITLE= "TASK_TITLE";
    const USER_ID_HAVE_TO_BE_SET_MSG = "User Id have to be set";

    private $userId;

    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }
    /**
     * Retrieve task from DB
     *
     * @param null $statusesFilter
     * @param null $typeOfSort
     * @return mixed
     */
    public function get($statusesFilter = null, $typeOfSort = null)
    {
        if (empty($this->userId)) {
            throw new BAException(self::USER_ID_HAVE_TO_BE_SET_MSG, BAException::PARAM_NOT_SET_EXCODE, null);
        }

        $tasksGetQuery = Task::find()
            ->where(['user' => $this->userId])
            ->leftJoin('sync_changed_tasks', '`sync_changed_tasks`.`task_id` = `tasks`.`id`')
            ->with('picture', 'changeOfTask');

        if ($statusesFilter != null) {
            $tasksGetQuery->andWhere(['status' => $statusesFilter]);
        }

        if ($typeOfSort != null) {
            switch ($typeOfSort){
                case self::SORTTYPE_NEWEST :
                    $tasks = $tasksGetQuery->orderBy('created')->all();
                    break;
                case self::SORTTYPE_OLDEST :
                    $tasks = $tasksGetQuery->orderBy('created desc')->all();
                    break;
                case  self::SORTTYPE_LATEST_CHANGES :
                    $tasks = $tasksGetQuery->orderBy('sync_changed_tasks.datetime')->all();
                    break;
                case self::SORTTYPE_TITLE :
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