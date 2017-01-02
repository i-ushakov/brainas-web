<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 12/22/2016
 * Time: 10:59 AM
 */

namespace backend\helpers;

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
            $picture->task_id = $task->id;
            $picture->save();
        }
        return $task->id;
    }

    public function updateTask($taskWithConditions) {
        return false;
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


}