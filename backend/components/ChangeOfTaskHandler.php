<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 12/22/2016
 * Time: 10:59 AM
 */

namespace backend\components;

use common\components\BAException;
use common\nmodels\TaskXMLConverter;
use common\nmodels\Task;
use common\models\PictureOfTask;
use common\infrastructure\ChangeOfTask;
use frontend\components\GoogleDriveHelper;

class ChangeOfTaskHandler {
    const USER_ID_MUST_TO_BE_SET_MSG = "User id must to be set";
    const TASK_ID_MUST_TO_BE_KNOWN_MSG = "Task id must to be known";
    private $changeParser;
    private $converter;
    private $userId = null;
    private $client;

    public function __construct(ChangeOfTaskParser $changeParser, TaskXMLConverter $taskConverter, $userId = null) {
        $this->changeParser = $changeParser;
        $this->converter = $taskConverter;
        $this->userId = $userId;
    }

    public function handle(\SimpleXMLElement $chnageOfTaskXML) {
        if (is_null($this->userId)) {
            throw new BAException(self::USER_ID_MUST_TO_BE_SET_MSG, BAException::PARAM_NOT_SET_EXCODE);
        }
        if($this->changeParser->isANewTask($chnageOfTaskXML)) {
            return $this->handleNewTask($chnageOfTaskXML);
        } else {
            $taskId = $this->changeParser->getGlobalId($chnageOfTaskXML);
            $task = Task::findOne($taskId);
            if ($task != null) {
                if ($this->isChangeOfTaskActual($chnageOfTaskXML)) {
                    $status = $this->changeParser->getStatus($chnageOfTaskXML);
                    if ($status == "DELETED") {
                        $this->deleteTask($taskId);
                    } elseif ($status == "UPDATED" || $status == "CREATED") {
                        $taskWithConditions = $this->converter->fromXML($chnageOfTaskXML->task);
                        if($taskId = $this->updateTask($taskWithConditions)) {
                            $this->loggingChanges($chnageOfTaskXML, "Changed");
                        } else {
                            return false;
                        }
                    }
                }
            }
            return $taskId;
        }
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function handleNewTask(\SimpleXMLElement $chnageOfTaskXML)
    {
        $taskWithConditions = $this->converter->fromXML($chnageOfTaskXML->task);
        $taskId = $this->addTask($taskWithConditions);
        $this->loggingChanges($chnageOfTaskXML, "Created", $taskId);
        return $taskId;
    }

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

    public function updateTask($taskWithConditions) {
        $updatedTask = $taskWithConditions['task'];
        $updatedPicture = $taskWithConditions['picture'];
        $updatedConditions = $taskWithConditions['conditions'];

        $task = Task::findOne($updatedTask->id);

        if (!isset($task)) {
            return null;
        }
        $task->message = $updatedTask->message;
        $task->user = $updatedTask->user;
        $task->description = $updatedTask->description;
        $task->last_modify = $updatedTask->last_modify;
        $task->status = $updatedTask->status;
        $task->save();

        if ($updatedPicture != null) {
            $this->savePistureOfTask($updatedPicture, $updatedTask->id);
        }

        $this->cleanDeletedConditions($updatedConditions, $updatedTask->id);
        foreach ($updatedConditions as $updatedCondition) {
            $condition = \common\nmodels\Condition::findOne($updatedCondition->id);
            if (!isset($condition)) {
                $condition = new \common\nmodels\Condition();
            }
            $condition->task_id = $updatedCondition->task_id;
            $condition->type = $updatedCondition->type;
            $condition->params = $updatedCondition->params;
            $condition->save();
        }

        return $task->id;
    }

    public function deleteTask($taskId) {
        return false;
    }

    public function isChangeOfTaskActual(\SimpleXMLElement $chnageOfTaskXML) {
        $taskId = $this->changeParser->getGlobalId($chnageOfTaskXML);
        $serverChangeTime = $this->getTimeOfTaskChanges($taskId);
        $clientChangeTime = $this->changeParser->getTimeOfChange($chnageOfTaskXML);
        if (strtotime($serverChangeTime) < strtotime($clientChangeTime)) {
            return true;
        }
    }

    public function getTimeOfLastLoggedChanges($taskId) {
        $changeOfTask = ChangeOfTask::find()
            ->where(['user_id' => $this->userId, 'task_id' => $taskId])
            ->orderBy('id')
            ->one();
        if (!is_null($changeOfTask)) {
            return $changeOfTask->datetime;
        } else {
            return null;
        }
    }

    public function loggingChanges($changeOfTaskXML, $action, $taskId = null) {
        $changeDatetime = $this->changeParser->getTimeOfChange($changeOfTaskXML);
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

    public function savePistureOfTask($pictureForSave, $taskId) {
        $picture = PictureOfTask::find()->where(['task_id' => $taskId])->one();
        if (!isset($picture)) {
            $picture = new PictureOfTask();
        }

        $picture->task_id = $taskId;
        $picture->name = $pictureForSave->name;

        if (isset($pictureForSave->file_id)) {
            $picture->file_id = $pictureForSave->resourceId;
        } else {
            $picture->file_id = GoogleDriveHelper::getInstance($this->client)->getFileIdByName($pictureForSave->name);
        }
        $picture->save();
    }

    public function cleanDeletedConditions() {
        return true;
    }

}