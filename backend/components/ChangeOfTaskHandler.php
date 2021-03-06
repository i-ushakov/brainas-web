<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 12/22/2016
 * Time: 10:59 AM
 */

namespace backend\components;

use common\components\BAException;
use common\components\TaskXMLConverter;
use common\models\Task;
use common\models\Condition;
use common\models\PictureOfTask;
use common\models\ChangeOfTask;
use common\components\GoogleDriveHelper;

/*
 * ChangeOfTaskHandler responsive for handling task that was got from device
 */
class ChangeOfTaskHandler {
    const USER_ID_MUST_TO_BE_SET_MSG = "User id must to be set";
    const TASK_ID_MUST_TO_BE_KNOWN_MSG = "Task id must to be known";
    const GOOGLE_DRIVE_HELPER_NOT_SET = "We don't have google drive helper";

    /** var ChangeOfTaskParser $changeParser
     *  helper class that used to parse XML-document of task
     */
    protected $changeParser;

    /** var TaskXMLConverter $converter
     *  helper class that convert task from/to XML-document
     */
    protected $converter;

    /** var Integer $userId
     * internal ID of the user (inside the system) from the device of which the request was received
     */
    protected $userId = null;

    /** var GoogleDriveHelper $googleDriveHelper
     *  helper class intends for working with Google Drive API
     */
    protected $googleDriveHelper;

    /*
     * Create new ChangeOfTaskHandler object
     * @param ChangeOfTaskParser $changeParser - helper class that used to parse XML-document of task
     * @param TaskXMLConverter $taskConverter - helper class that convert task from/to XML-document
     * @param Integer $userId - internal ID of the user (inside the system) from the device of which the request was received
     * @param GoogleDriveHelper $googleDriveHelper - helper class intends for working with Google Drive API
     */
    public function __construct(ChangeOfTaskParser $changeParser, TaskXMLConverter $taskConverter,
                                $userId = null, GoogleDriveHelper $googleDriveHelper = null) {
        $this->changeParser = $changeParser;
        $this->converter = $taskConverter;
        $this->userId = $userId;
        $this->googleDriveHelper = $googleDriveHelper;
    }

    /**
     * @param $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param $googleDriveHelper
     * @return $this
     */
    public function setGoogleDriveHelper($googleDriveHelper) {
        $this->googleDriveHelper = $googleDriveHelper;
        return $this;
    }


    /**
     * Handle a new or updated task (xml-document) gotten from user device
     *
     * @param \SimpleXMLElement $chnageOfTaskXML
     * @return int|mixed|null|string
     * @throws BAException
     */
    public function handle(\SimpleXMLElement $chnageOfTaskXML) {
        if (is_null($this->userId)) {
            throw new BAException(self::USER_ID_MUST_TO_BE_SET_MSG, BAException::PARAM_NOT_SET_EXCODE);
        }

        if($this->changeParser->isANewTask($chnageOfTaskXML)) {
            return $this->handleNewTask($chnageOfTaskXML);
        } else {
            return $this->handleExistTask($chnageOfTaskXML);
        }
    }

    /**
     * Handle a NEW task (xml-document) gotten from user device
     *
     * @param \SimpleXMLElement $chnageOfTaskXML
     * @return null
     */
    public function handleNewTask(\SimpleXMLElement $chnageOfTaskXML)
    {
        $taskWithConditions = $this->converter->fromXML($chnageOfTaskXML->task);
        $taskId = $this->addTask($taskWithConditions);
        $this->loggingChanges($chnageOfTaskXML, "Created", $taskId);
        return $taskId;
    }

    /**
     * Handle a UPDATED task (xml-document) gotten from user device
     *
     * @param \SimpleXMLElement $chnageOfTaskXML
     * @return int|mixed|null|string
     */
    public function handleExistTask(\SimpleXMLElement $chnageOfTaskXML)
    {
        $taskId = $this->changeParser->getGlobalId($chnageOfTaskXML);;
        $task = Task::findOne(['id' => $taskId, 'user' => $this->userId]);

        if (!is_null($task)) {
            if ($this->isActualChange($chnageOfTaskXML)) {
                $status = $this->changeParser->getStatus($chnageOfTaskXML);
                if ($status == ChangeOfTask::STATUS_DELETED) {
                    return $this->deleteTask($taskId);
                } elseif ($status == ChangeOfTask::STATUS_UPDATED || $status == ChangeOfTask::STATUS_CREATED) {
                    $taskWithConditions = $this->converter->fromXML($chnageOfTaskXML->task);
                    if($taskId = $this->updateTask($taskWithConditions)) {
                        $this->loggingChanges($chnageOfTaskXML, ChangeOfTask::STATUS_UPDATED);
                        return $taskId;
                    } else {
                        return null;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Add task with conditions and picture to DB
     *
     * @param array $taskWithConditions
     * @return null
     */
    public function addTask(array $taskWithConditions) {
        $task = $taskWithConditions['task'];
        $conditions = $taskWithConditions['conditions'];
        $picture = $taskWithConditions['picture'];
        $task->id = null;
        $task->user = $this->userId;
        $task->last_modify = date('Y-m-d H:i:s', time());
        $task->created = date('Y-m-d H:i:s', time());
        $task->save();
        foreach ($conditions as $condition) {
            $condition->task_id = $task->id;
            $condition->save();
        }

        if ($picture != null) {
            $this->savePistureOfTask($picture, $task->id);
        }
        return $task->id;
    }

    /**
     * Update task with conditions and picture in DB
     *
     * @param $taskWithConditions
     * @return mixed|null
     */
    public function updateTask($taskWithConditions) {
        $updatedTask = $taskWithConditions['task'];
        $updatedPicture = $taskWithConditions['picture'];
        $updatedConditions = $taskWithConditions['conditions'];
        $task = Task::findOne(['id' => $updatedTask->id, 'user' => $this->userId]);
        if (!isset($task)) {
            return null;
        }
        $task->message = $updatedTask->message;
        $task->description = $updatedTask->description;
        $task->last_modify = date('Y-m-d H:i:s', time());
        $task->status = $updatedTask->status;
        $task->save();

        if (!is_null($updatedPicture)) {
            $this->savePistureOfTask($updatedPicture, $updatedTask->id);
        }

        $this->cleanDeletedConditions($updatedConditions, $updatedTask->id);
        $this->updateConditions($updatedConditions, $updatedTask->id);

        return $task->id;
    }

    /**
     * Update conditions of task in DB
     *
     * @param $updatedConditions
     */
    public function updateConditions($updatedConditions) {
        foreach ($updatedConditions as $updatedCondition) {
            $condition = \common\models\Condition::findOne($updatedCondition->id);
            if (!isset($condition)) {
                $condition = new \common\models\Condition();
            }
            $condition->task_id = $updatedCondition->task_id;
            $condition->type = $updatedCondition->type;
            $condition->params = $updatedCondition->params;
            $condition->save();
        }
    }

    /**
     * Delete task from DB
     *
     * @param $taskId
     * @return mixed
     */
    public function deleteTask($taskId) {
        $task = Task::findOne($taskId);
        if (isset($task)) {
            $task->delete();
        }
        return $taskId;
    }

    /**
     * Check if changes that came from device are actual or server's data is newest
     *
     * @param \SimpleXMLElement $chnageOfTaskXML
     * @return bool
     */
    public function isActualChange(\SimpleXMLElement $chnageOfTaskXML) {
        $taskId = $this->changeParser->getGlobalId($chnageOfTaskXML);
        $serverTime = $this->getServerTimeOfChanges($taskId);
        $clientTime = $this->changeParser->getClientTimeOfChanges($chnageOfTaskXML);
        if (strtotime($serverTime) < strtotime($clientTime)) {
            return true;
        }
    }

    /**
     * Logging tasks changes in DB for control actualization of data in future
     *
     * @param $changeOfTaskXML
     * @param $action
     * @param null $taskId
     * @return bool
     * @throws BAException
     */
    public function loggingChanges($changeOfTaskXML, $action, $taskId = null) {
        $changeDatetime = $this->changeParser->getClientTimeOfChanges($changeOfTaskXML);
        if (is_null($taskId)) {
            $taskId = $this->changeParser->getGlobalId($changeOfTaskXML);
        }

        if ($taskId == 0) {
            throw new BAException(self::TASK_ID_MUST_TO_BE_KNOWN_MSG, BAException::PARAM_NOT_SET_EXCODE);
        }

        $changeOfTask = ChangeOfTask::find()
            ->where(['user_id' => $this->userId, 'task_id' => $taskId])
            ->orderBy('id')
            ->one();
        if (empty($changeOfTask)) {
            $changeOfTask = new ChangeOfTask();
            $changeOfTask->task_id = $taskId;
            $changeOfTask->user_id = $this->userId;
        }

        if ($changeDatetime == null) {
            $currentDatetime = new \DateTime();
            $currentDatetime->setTimezone(new \DateTimeZone("UTC"));
            $changeOfTask->datetime = $currentDatetime->format('Y-m-d H:i:s');
        } else {
            $changeOfTask->datetime = $changeDatetime;
        }
        $changeOfTask->server_update_time = date('Y-m-d H:i:s');
        $changeOfTask->action = $action;
        $changeOfTask->save();

        return true;
    }

    /**
     * Save picture's name and fileId (for Google Drive API)
     *
     * @param PictureOfTask $pictureForSave
     * @param $taskId
     */
    public function savePistureOfTask(PictureOfTask $pictureForSave, $taskId) {
        $picture = PictureOfTask::find()->where(['task_id' => $taskId])->one();
        if (!isset($picture)) {
            $picture = new PictureOfTask();
        }

        $picture->task_id = $taskId;
        $picture->name = $pictureForSave->name;

        if (isset($pictureForSave->file_id)) {
            $picture->file_id = $pictureForSave->file_id;
        } else {
            if ($this->googleDriveHelper != null) {
                $picture->file_id = $this->googleDriveHelper->getFileIdByName($pictureForSave->name);
            } else {
                throw new BAException(self::GOOGLE_DRIVE_HELPER_NOT_SET, BAException::PARAM_NOT_SET_EXCODE, null);
            }
        }
        $picture->save();
    }

    /**
     * Retrieve server time of the last update of task from logging table in DB,
     * we need this to know is server data actual or need to be updated
     *
     * @param $taskid
     * @return mixed|null
     */
    public function getServerTimeOfChanges($taskid) {
        $changedTask = ChangeOfTask::find()
            ->where(['user_id' => $this->userId, 'task_id' => $taskid])
            ->orderBy('id')
            ->one();
        if (!is_null($changedTask)) {
            return $changedTask->datetime;
        } else {
            return null;
        }
    }

    /**
     * Remove conditions from DB
     *
     * @param $updatedConditions
     * @param $taskId
     */
    public function cleanDeletedConditions($updatedConditions, $taskId) {
        $conditionsIds = array();
        foreach ($updatedConditions as $updatedCondition) {
            if (isset($updatedCondition->id) && $updatedCondition->id != 0) {
                $conditionsIds[] = $updatedCondition->id;
            }
        }
        $conditionsFromDB = Condition::find()
            ->where(['task_id' => $taskId])
            ->all();
        foreach($conditionsFromDB as $conditionFromDB) {
            if(!in_array ($conditionFromDB->id, $conditionsIds)) {
                $conditionFromDB->delete();
            }
        }
    }

}