<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 1/24/2017
 * Time: 2:13 PM
 */

namespace backend\components;

use common\components\BAException;
use common\infrastructure\ChangeOfTask;
use common\nmodels\Task;

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
    const USER_ID_MUST_TO_BE_SET_MSG = "User id must to be set";

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

    public function getXmlWithChanges($existsTasksFromDevice, $lastSyncTime)
    {
        $serverChanges = $this->getChangesOfTasks($lastSyncTime);
        //$existingTasksOnDevice = TaskXMLHelper::retrieveExistingTasksFromXML($this->syncDataFromDevice);
        //$serverChanges['deleted'] = TaskHelper::getTasksRemovedOnServer($existingTasksOnDevice, $this->userId);
        return $this->prepareXmlWithTasksChanges($serverChanges);
    }

    public function getChangesOfTasks($lastSyncTime) {
        $changedTasks = ['created' => [], 'updated' => []];

        // getting last changed tasks
        $changes = $this->retrieveChangesOfTasksFromDB($lastSyncTime);

        foreach ($changes as $changeOfTask) {
            if ($changeOfTask->action == ChangeOfTask::STATUS_CREATED && $this->isChangedTaskExistInDb($changeOfTask)) {
                $changedTasks['created'][$changeOfTask->task_id]['action'] = $changeOfTask->action;
                $changedTasks['created'][$changeOfTask->task_id]['datetime'] = $changeOfTask->datetime;
                $changedTasks['created'][$changeOfTask->task_id]['object'] = $changeOfTask->task;
            } else if ($changeOfTask->action ==  ChangeOfTask::STATUS_UPDATED  && $this->isChangedTaskExistInDb($changeOfTask)) {
                $changedTasks['updated'][$changeOfTask->task_id]['action'] = $changeOfTask->action;
                $changedTasks['updated'][$changeOfTask->task_id]['datetime'] = $changeOfTask->datetime;
                $changedTasks['updated'][$changeOfTask->task_id]['object'] = $changeOfTask->task;
            }
        }

        return $changedTasks;
    }

    public function retrieveChangesOfTasksFromDB($lastSyncTime) {
        if (is_null($this->userId)) {
            throw new BAException(self::USER_ID_MUST_TO_BE_SET_MSG, BAException::PARAM_NOT_SET_EXCODE);
        }
        if ($lastSyncTime != null) {
            $changesOfTasks = ChangeOfTask::find()
                ->where([
                    'and',
                    ['=', 'user_id', $this->userId],
                    ['>', 'server_update_time', $lastSyncTime]
                ])
                ->orderBy('datetime')
                ->all();
        } else {
            $changesOfTasks = ChangeOfTask::find()
                ->where(['user_id' => $this->userId])
                ->orderBy('datetime')
                ->all();
        }
        return $changesOfTasks;
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


    public function prepareXmlWithTasksChanges($tasks) {
        file_put_contents("test006.txt", count($tasks));
        $xmlResponse = "";
        $xmlResponse .= '<?xml version="1.0" encoding="UTF-8"?>';

        $xmlResponse .= '<tasks>';

        // Created tasks
        $xmlResponse .= '<created>';
        foreach ($tasks['created'] as $id => $serverChange) {
            $xmlResponse .= XMLResponseBuilder::buildXmlOfTask($serverChange['object'],  $serverChange['datetime']);
        }
        $xmlResponse .= '</created>';
        $xmlResponse .= '</tasks>';

        return $xmlResponse;
    }

    /*
     * Here we handle situation when we have info about task changes
     * and the same time task is not exist in database
     */
    public function isChangedTaskExistInDb($changeOfTask) {
        if(isset($changeOfTask->task) && !empty($changeOfTask->task) && $changeOfTask->task instanceof Task) {
            return true;
        } else {
            \Yii::info(
                "We have info about task changes without object in DB with datetime = " .$changeOfTask->datetime .
                " for task with id = " . $changeOfTask->task_id .
                ". So we change action from '" . $changeOfTask->action . "' to 'Deleted'", "MyLog"
            );
            return false;
        }
    }
}
