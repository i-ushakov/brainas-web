<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 1/24/2017
 * Time: 2:13 PM
 */

namespace backend\components;
use common\components\BAException;

/**
 * Class SyncManager
 * @package backend\components
 *
 * This is sync manager that is responsible for handling of data (tasks)
 * from device and preparing response
 *
 * @throws BAException
 */
class TasksSyncManager
{
    const EMPTY_TASKS_XML = 'EMPTY PARAM ($tasksXML) was sent into synchronization method';
    const WRONG_ROOT_ELEMNT = 'Param ($tasksXML) with WRONG ROOT ELEMENT was sent into synchronization method';

    public function getTasksFromDevice(\SimpleXMLElement $tasksXML)
    {
        if (!isset($tasksXML)) {
            throw new BAException(BAException::EMPTY_PARAM_EXCODE, self::EMPTY_TASKS_XML);
        }
        if ($tasksXML->getName() != "tasks") {
            throw new BAException(BAException::INVALID_PARAM_EXCODE, self::WRONG_ROOT_ELEMNT);
        }

    }

    public function sendTasksToDevice()
    {
        // TODO
        return;
    }
}
