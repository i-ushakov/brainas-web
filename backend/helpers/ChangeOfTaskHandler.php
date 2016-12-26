<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 12/22/2016
 * Time: 10:59 AM
 */

namespace backend\helpers;

use common\nmodels\TaskXMLConverter;
use common\models\Task;

class ChangeOfTaskHandler {
    private $changeParser;
    private $converter;

    public function __construct(ChangeOfTaskParser $changeParser, TaskXMLConverter $taskConverter) {
        $this->changeParser = $changeParser;
        $this->converter = $taskConverter;
    }

    public function handle(\SimpleXMLElement $chnageOfTaskXML) {
        if($this->changeParser->isANewTask($chnageOfTaskXML)) {
            $chnageOfTaskXML = $this->converter->fromXML($chnageOfTaskXML);
            return $this->addTask($chnageOfTaskXML);
        } else {
            $taskId = $this->changeParser->getGlobalId($chnageOfTaskXML);
            $task = Task::findOne($taskId);
            if ($task != null) {
                if ($this->isChangeOfTaskActual($chnageOfTaskXML)) {
                    $status = $this->changeParser->getStatus($chnageOfTaskXML);
                    if ($status == "DELETED") {
                        //$this->deleteTaskFromDevice($changedTask);
                        //$localId = 0;
                    } elseif ($status == "UPDATED" || $status == "CREATED") {
                        return $this->updateTask($chnageOfTaskXML);
                    }
                }
                return $task->id;
            } else {
                return 0;
            }
        }
        return 0;
    }

    public function addTask($taskWithConditions) {
        $task = $taskWithConditions['task'];
        $conditions = $taskWithConditions['conditions'];
        $task->save();
        foreach ($conditions as $condition) {
            $condition->task_id = $task->id;
            $condition->save();
        }
        return $task->id;
    }

    public function updateTask($chnageOfTaskXML) {
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


}