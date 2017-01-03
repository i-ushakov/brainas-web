<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 12/22/2016
 * Time: 10:59 AM
 */

namespace backend\helpers;

use common\models\Condition;
use common\nmodels\TaskXMLConverter;
use common\nmodels\Task;

class ChangeOfTaskHandler {
    private $changeParser;
    private $converter;
    private $userId;

    public function __construct(ChangeOfTaskParser $changeParser, TaskXMLConverter $taskConverter, $userId) {
        $this->changeParser = $changeParser;
        $this->converter = $taskConverter;
        $this->userId = $userId;
    }

    public function handle(\SimpleXMLElement $chnageOfTaskXML) {
        if($this->changeParser->isANewTask($chnageOfTaskXML)) {
            $taskWithConditions = $this->converter->fromXML($chnageOfTaskXML);
            $taskId = $this->addTask($taskWithConditions);
            $this->loggingChanges($chnageOfTaskXML, "CREATE");
            return $taskId;
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

    public function addTask($taskWithConditions) {
        $task = $taskWithConditions['task'];
        $conditions = $taskWithConditions['conditions'];
        $picture = $taskWithConditions['picture'];
        $task->user = $this->userId;
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

    public function getTimeOfTaskChange($taskId) {
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

    public function loggingChanges($changeOfTask, $type) {
        //$changeDatetime = $this->changeParser->getTimeOfChange();
        //ChangeOfTask::loggingChangesForSync("Created", $changeDatetime, $task);
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