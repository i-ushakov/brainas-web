<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 1/24/2017
 * Time: 2:13 PM
 */

namespace backend\components;

use common\components\BAException;
use common\components\GoogleDriveHelper;
use common\models\ChangeOfTask;
use common\models\Task;

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

    /**
     *  @var ChangeOfTaskHandler
     */
    protected $changeHandler;

    /**
     * @var XMLResponseBuilder
     */
    protected $responseBuilder;

    /**
     * @var int
     */
    protected $userId = null;

    /**
     * @var GoogleDriveHelper
     */
    protected $googleDriveHelper = null;

    public function __construct(ChangeOfTaskHandler $changeOfTaskHandler, XMLResponseBuilder $responseBuilder)
    {
        $this->changeHandler = $changeOfTaskHandler;
        $this->responseBuilder = $responseBuilder;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
        $this->changeHandler->setUserId($userId);
        return $this;
    }

    /**
     * We get XML with task's change from device and handle it
     * to save new changes on server side
     *
     * @param \SimpleXMLElement $taskChangesXML
     * @return string
     * @throws BAException
     */
    public function handleTasksFromDevice(\SimpleXMLElement $taskChangesXML)
    {
        $synchronizedTasks = [];
        if ($taskChangesXML->getName() != "changedTasks") {
            throw new BAException(self::WRONG_ROOT_ELEMNT, BAException::WRONG_ROOT_XML_ELEMENT_NAME);
        }

        foreach($taskChangesXML->changeOfTask as $changeOfTaskXML) {
            $globalId = $this->changeHandler->handle($changeOfTaskXML);
            if ($globalId != null) {
                $synchronizedTasks[(int)$changeOfTaskXML['localId']] = $globalId;
            }
        }

        return $this->responseBuilder->prepareSyncObjectsXml($synchronizedTasks);
    }

    /**
     * Gets all tasks changes on server and prepare them for sending
     * in a form of xml-document form
     *
     * @param $existsTasksFromDevice
     * @param $lastSyncTime
     *
     * @return string
     */
    public function getXmlWithChanges($existsTasksFromDevice, $lastSyncTime)
    {
        $serverChanges = $this->getChangesOfTasks($lastSyncTime);
        $serverChanges['deleted'] = $this->getDeletedTasks($existsTasksFromDevice);

        return $this->responseBuilder->buildXmlWithTasksChanges($serverChanges, $this->getCurrentTime());
    }

    /**
     * Collects all tasks changes on server in array
     *
     * @param $lastSyncTime
     * @return array
     */
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

    /**
     * Retrieve all changes tasks that happened after $lastSyncTime
     *
     * @param $lastSyncTime
     * @return array|\yii\db\ActiveRecord[]
     * @throws BAException
     */
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

    /**
     * Here we handle situation when we have info about task changes
     * and the same time task is not exist in database
     *
     * @param $changeOfTask
     * @return bool
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

    /**
     * Getting current time (UTC timezone)
     *
     * @return string
     */
    public function getCurrentTime() {
        $currentDatetime = new \DateTime();
        $currentDatetime->setTimezone(new \DateTimeZone("UTC"));
        $lastSyncTime = $currentDatetime->format('Y-m-d H:i:s');
        return $lastSyncTime;
    }

    /**
     * We are getting tasks that we have on device but they are absent on server side
     *
     * @param $existingTasksOnDevice
     * @return array
     */

    public function getDeletedTasks($existingTasksOnDevice) {
        $removedTasks = array();
        $existingTasksOnServer = array();
        $tasks = Task::findAll(['user' => $this->userId]);
        foreach ($tasks as $task) {
            $existingTasksOnServer[] = intval($task->id);
        }
        foreach ($existingTasksOnDevice as $serverId => $localId) {
            if (!in_array(intval($serverId), $existingTasksOnServer)) {
                $removedTasks[$serverId] = intval($localId);
            }
        }
        return $removedTasks;
    }
}
