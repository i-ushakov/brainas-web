<?php
namespace backend\components;

use common\models\GoogleDriveFolder;
use common\models\PictureOfTask;
use common\models\Task;
use common\infrastructure\ChangeOfTask;
use frontend\components\GoogleDriveHelper;


/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 6/3/2016
 * Time: 8:43 PM
 */

class TaskSyncHelper {
    private $syncDataFromDevice;
    private $serverChanges;
    private $userId;
    private $token;
    private $client;

    function __construct(\SimpleXMLElement $syncDataFromDevice, $userId, $token) {
        $this->syncDataFromDevice = $syncDataFromDevice;
        $this->userId = $userId;
        $this->token = $token;
        $this->client = GoogleAuthHelper::getGoogleClient();
        $this->client->setAccessToken($this->token);
    }

    public function doSynchronization() {
        $initSyncTime = $this->getInitSyncTimeFromPost();

        //$this->processProjectFolders($this->syncDataFromDevice->);

        // Get chnaged and deletet task
        // from time of last sync for this user
        $serverChanges = $this->getServerChanges($initSyncTime);

        // We process changes from device and we'll return ids of synchronized objects (task, conditions, events)
        $synchronizedObjects = $this->processTaskChangesFromDevice($serverChanges);

        // Build xml-document with server-changes
        // and data about changes from device that were accepted
        $initSyncTime = $this->getCurrentTime();
        $xmlResponse = XMLResponseBuilder::buildXMLResponse($serverChanges, $synchronizedObjects, $initSyncTime, $this->token);
        return $xmlResponse;
    }

    public function getServerChanges($initSyncTime) {
        $serverChanges = array();

        // getting last changed tasks
        $changesOfTasks = $this->getChangedTasks($initSyncTime);

        $serverChanges['tasks']['created'] = array();
        $serverChanges['tasks']['updated'] = array();

        foreach ($changesOfTasks as $changeOfTask) {
            if ($changeOfTask->action == "Created" && $this->isChangedTaskExistInDb($changeOfTask)) {
                $serverChanges['tasks']['created'][$changeOfTask->task_id]['action'] = $changeOfTask->action;
                $serverChanges['tasks']['created'][$changeOfTask->task_id]['datetime'] = $changeOfTask->datetime;
                $serverChanges['tasks']['created'][$changeOfTask->task_id]['object'] = $changeOfTask->task;
            } else if ($changeOfTask->action == "Changed" && $this->isChangedTaskExistInDb($changeOfTask)) {
                $serverChanges['tasks']['updated'][$changeOfTask->task_id]['action'] = $changeOfTask->action;
                $serverChanges['tasks']['updated'][$changeOfTask->task_id]['datetime'] = $changeOfTask->datetime;
                $serverChanges['tasks']['updated'][$changeOfTask->task_id]['object'] = $changeOfTask->task;
            }
        }

        return $serverChanges;
    }

    public function processTaskChangesFromDevice(&$serverChanges) {
        $synchronizedObjects = array();
        $synchronizedTasks = array();

        // Find tasks that exists on device (client) but absent on server (use serverId)
        $existingTasksOnDevice = TaskXMLHelper::retrieveExistingTasksFromXML($this->syncDataFromDevice);
        $serverChanges['tasks']['deleted'] = TaskHelper::getTasksRemovedOnServer($existingTasksOnDevice, $this->userId);

        $changedTasks = $this->syncDataFromDevice->changedTasks;
        foreach($changedTasks->changedTask as $changedTask) {
            $statusOfChanges = (String)$changedTask->change[0]->status;
            $globalId = (string)$changedTask['globalId'];
            $localId = (string)$changedTask['id'];
            if ($globalId == 0 && $statusOfChanges != "DELETED") {
                $globalId = $this->addTaskFromDevice($changedTask, $synchronizedObjects);
                $synchronizedTasks[$localId] = $globalId;
            } else {
                if (Task::findOne($globalId) != null) {
                    $serverChangesTime = $this->getTimeOfTaskChanges($globalId);
                    $clientChangesTime = (String)$changedTask->change[0]->changeDatetime;
                    if (strtotime($serverChangesTime) < strtotime($clientChangesTime)) {
                        $status = (String)$changedTask->change[0]->status;
                        if ($status == "DELETED") {
                            $this->deleteTaskFromDevice($changedTask);
                            $localId = 0;
                        } elseif ($status == "UPDATED" || $status == "CREATED") {
                            $this->updateTaskFromDevice($changedTask, $synchronizedObjects);
                        }
                        unset($serverChanges['tasks']['updated'][$globalId]);
                    }
                    $synchronizedTasks[$localId] = $globalId;
                } else {
                    $serverChanges['tasks']['deleted'][$globalId] = $localId;
                    $synchronizedTasks[$localId] = $globalId;
                }
            }
        }
        $synchronizedObjects['tasks'] = $synchronizedTasks;
        return $synchronizedObjects;
    }

    private function getChangedTasks($initSyncTime) {
        if ($initSyncTime != null) {
            $changesOfTasks = ChangeOfTask::find()
                ->where([
                    'and',
                    ['=', 'user_id', $this->userId],
                    ['>', 'datetime', $initSyncTime]
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

    private function addTaskFromDevice ($newTaskFromDevice, &$synchronizedObjects) {
        $task = new Task();
        $task->message = (String)$newTaskFromDevice->message;
        $task->description = (String)$newTaskFromDevice->description;
        $task->user = $this->userId;
        if ($task->save()) {
            foreach ($newTaskFromDevice->conditions->condition as $c) {
                TaskXMLHelper::addConditionFromXML($c, $task->id, $synchronizedObjects);
            }
            if (isset($newTaskFromDevice->picture)) {
                $this->savePistureOfTask($newTaskFromDevice->picture, $task->id);
            }
            $changeDatetime = (String)$newTaskFromDevice->change[0]->changeDatetime;
            ChangeOfTask::loggingChangesForSync("Created", $changeDatetime, $task);
            return $task->id;
        }
        return 0;
    }

    private function updateTaskFromDevice ($changedTask, &$synchronizedObjects) {
        $taskId = (string)$changedTask['globalId'];
        $message = (string)$changedTask->message;
        $description = (string)$changedTask->description;
        $task = Task::findOne($taskId);
        $task->message = $message;
        $task->description = $description;
        $task->save();
        if (isset($changedTask->picture)) {
            $this->savePistureOfTask($changedTask->picture, $taskId);
        }
        TaskXMLHelper::cleanDeletedConditions($changedTask->conditions->condition, $task->id);
        foreach ($changedTask->conditions->condition as $c) {
            TaskXMLHelper::addConditionFromXML($c, $task->id, $synchronizedObjects);
        }
        $changeDatetime = (String)$changedTask->change[0]->changeDatetime;
        ChangeOfTask::loggingChangesForSync("Changed", $changeDatetime, $task);
        return $task->id;
    }

    private function savePistureOfTask($pictureXML, $taskId)
    {
        $picture = new PictureOfTask();
        $picture->task_id = $taskId;
        $picture->name = $pictureXML->fileName;
        if (isset($pictureXML->driveId)) {
            $picture->drive_id = $pictureXML->driveId;
        }
        $picture->file_id = GoogleDriveHelper::getInstance($this->client)->getFileIdByName($pictureXML->fileName);
        if ($picture->file_id != null) {
            $picture->save();
        } else {
            return false;
        }
    }

    private function deleteTaskFromDevice ($deletedTask) {
        $id = (string)$deletedTask['globalId'];
        $task = Task::findOne($id);
        if (isset($task)) {
            $task->delete();
        }
    }

    private function getTimeOfTaskChanges($taskid) {
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

    private function getInitSyncTimeFromPost() {
        $initSyncTime = null;
        if (isset($_POST['initSyncTime'])) {
            $initSyncTime = $_POST['initSyncTime'];
        }
        return $initSyncTime;
    }

    private function getCurrentTime() {
        $currentDatetime = new \DateTime();
        $currentDatetime->setTimezone(new \DateTimeZone("UTC"));
        $initSyncTime = $currentDatetime->format('Y-m-d H:i:s');
        return $initSyncTime;
    }

    /*
     * Here we handle situation when we have info about task changes
     * and the same time task is not exist in database
     */
    private function isChangedTaskExistInDb($changeOfTask) {
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