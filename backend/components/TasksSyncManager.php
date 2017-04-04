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
    protected $userId = null;

    public function __construct(ChangeOfTaskHandler $changeOfTaskHandler)
    {
        $this->changeOfTaskHandler = $changeOfTaskHandler;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
        $this->changeOfTaskHandler->setUserId($userId);
    }

    public function handleTasksFromDevice(\SimpleXMLElement $taskChangesXML)
    {
        $synchronizedTasks = [];
        if ($taskChangesXML->getName() != "changedTasks") {
            throw new BAException(self::WRONG_ROOT_ELEMNT, BAException::WRONG_ROOT_XML_ELEMENT_NAME);
        }

        foreach($taskChangesXML->changeOfTask as $changeOfTaskXML) {
            $globalId = $this->changeOfTaskHandler->handle($changeOfTaskXML);
            if ($globalId != null) {
                $synchronizedTasks[(int)$changeOfTaskXML['localId']] = $globalId;
            }
        }

        return $synchronizedTasks;
    }

    public function sendTasksToDevice()
    {
        // TODO
        return;
    }

    public function prepareSyncObjectsXml($synchronizedTasks)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<synchronizedTasks>';

        if (count($synchronizedTasks) > 0) {
            foreach ($synchronizedTasks as $localId => $globalId) {
                $xml .= "<synchronizedTask>" .
                        "<localId>$localId</localId>" .
                        "<globalId>$globalId</globalId>" .
                    "</synchronizedTask>";
            }
        }

        $xml .= '</synchronizedTasks>';
        return $xml;
    }
}
