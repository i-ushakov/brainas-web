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
    const WRONG_ROOT_ELEMNT = 'Param ($tasksXML) with WRONG ROOT ELEMENT was sent into synchronization method';

    protected $changeOfTaskHandler;

    public function __construct(ChangeOfTaskHandler $changeOfTaskHandler)
    {
        $this->changeOfTaskHandler = $changeOfTaskHandler;
    }

    public function getTasksFromDevice(\SimpleXMLElement $taskChangesXML)
    {
        if ($taskChangesXML->getName() != "changedTasks") {
            throw new BAException(self::WRONG_ROOT_ELEMNT, BAException::INVALID_PARAM_EXCODE);
        }
        foreach($taskChangesXML->changeOfTask as $changeOfTaskXML) {
            $this->changeOfTaskHandler->handle($changeOfTaskXML);
        }

    }

    public function sendTasksToDevice()
    {
        // TODO
        return;
    }
}
