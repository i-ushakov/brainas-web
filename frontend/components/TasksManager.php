<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 8/9/2017
 * Time: 11:11 AM
 */

namespace frontend\components;

use common\models\ChangeOfTask;
use common\models\Task;
use common\models\Condition;
use common\models\EventType;
use common\models\User;
use common\utils\DatetimeUtils;
use common\models\PictureOfTask;
use common\components\GoogleDriveHelper;

use yii\helpers\Json;

class TasksManager
{
    /**
     * @var GoogleIdentityHelper;
     */
    protected $googleIdentityHelper;

    /**
     * @var StatusManager
     */
    protected $statusManager;

    /**
     * @var User
     */
    protected $user;

    public function __construct(GoogleIdentityHelper $googleIdentityHelper, StatusManager $statusManager)
    {
        $this->googleIdentityHelper = $googleIdentityHelper;
        $this->statusManager = $statusManager;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Update exists task or create new one, save picture (if given) and conditions
     *
     * @param $taskDataForSave
     * @return array
     */
    public function handleTask($taskDataForSave)
    {
        $taskId = $taskDataForSave['id'];

        if (is_null($taskId)) {
            $task = $this->createTask($taskDataForSave);
            $status = ChangeOfTask::STATUS_CREATED;
        } else {
            $task = $this->updateTask($taskDataForSave);
            $status = ChangeOfTask::STATUS_UPDATED;
        }

        if (isset($taskDataForSave['picture_name']) && isset($taskDataForSave['picture_file_id']))
        {
            $this->savePicture($task, $taskDataForSave['picture_file_id'], $taskDataForSave['picture_name']);
        }

        $this->cleanDeletedConditions($taskDataForSave['conditions'], $task->id);


        if (isset($taskDataForSave['conditions']) && count($taskDataForSave['conditions']) > 0) {
            $this->saveConditions($taskDataForSave['conditions'], $task->id);
        }

        $this->statusManager->updateStatus($task);
        $this->saveTask($task, $status);

        return $task;
    }

    /**
     * Create new task with given data
     *
     * @param $taskDataForSave
     * @return Task|null
     */
    public function createTask($taskDataForSave)
    {
        /* @var Task */
        $task = new Task();
        $task->user = $this->user->id;
        $task->message = "New task";
        $task->last_modify = DatetimeUtils::getCurrentUTCTime();
        $this->setTaskData($task, $taskDataForSave);
        $task = $this->saveTask($task, ChangeOfTask::STATUS_CREATED);
        return $task;
    }

    /**
     * Update exists task with given data
     *
     * @param $taskDataForSave
     * @return array|Task|null|void|\yii\db\ActiveRecord
     */
    public function updateTask($taskDataForSave) {
        $taskId = $taskDataForSave['id'];

        $task = Task::find()
            ->where(['id' => $taskId, 'user' => $this->user->id])
            ->with('picture')
            ->one();

        $task = $this->setTaskData($task, $taskDataForSave);
        $task = $this->saveTask($task, ChangeOfTask::STATUS_UPDATED);
        return $task;
    }

    /**
     * Set fields of Task object
     *
     * @param $task
     * @param $taskDataForSave
     *
     * @return Task
     */
    public function setTaskData($task, $taskDataForSave)
    {
        if (isset($taskDataForSave['message'])) {
            $task->message = $taskDataForSave['message'];
        }

        if (isset($taskDataForSave['description'])) {
            $task->description = $taskDataForSave['description'];
        }

        if (isset($taskDataForSave['status'])) {
            $task->status = $taskDataForSave['status'];
        }

        return $task;
    }

    /**
     * Save task in database and log changes for sync with devices
     *
     * @param Task $task
     * @param $changeStatus
     * @return Task|null
     */
    public function saveTask(Task $task, $changeStatus)
    {
        if (!empty($task)) {
            $task->save();
            ChangeOfTask::loggingChangesForSync($changeStatus, null, $task);
            return $task;
        } else {
            return null;
        }
    }

    public function savePicture($task, $pictureFileId, $pictureName)
    {
        if ((!isset($task->picture->file_id) || $pictureFileId != $task->picture->file_id)) {
            $currentPicture = $task->picture;
            if (isset($currentPicture)) {
                $user = $this->user;
                $googleDriveHelper = GoogleDriveHelper::getInstance(
                    new \Google_Service_Drive($this->googleIdentityHelper->getGoogleClientWithToken($user))
                );
                $googleDriveHelper->removeFile($currentPicture->file_id);
                $currentPicture->delete();
            }

            $newPictureOfTask = new PictureOfTask();
            $newPictureOfTask->task_id = $task->id;
            $newPictureOfTask->name = $pictureName;
            $newPictureOfTask->file_id = $pictureFileId;
            $newPictureOfTask->save();
        }
    }

    public function cleanDeletedConditions($conditionsForSave, $taskId)
    {
        $conditionsIds = array();
        if (!empty($conditionsForSave)) {
            foreach ($conditionsForSave as $conditionForSave) {
                if (isset($conditionForSave['id']) && $conditionForSave['id'] != 0) {
                    $conditionsIds[] = $conditionForSave['id'];
                }
            }
        }
        $conditionsFromDB = Condition::find()
            ->where(['task_id' => $taskId])
            ->all();
        foreach($conditionsFromDB as $conditionFromDB) {
            if(!in_array ($conditionFromDB->id, $conditionsIds)) {
                $conditionFromDB->delete();
                $this->cleanDeletedEvents($conditionFromDB->id);
            }
        }
    }

    public function saveConditions($conditionsData, $taskId)
    {
        foreach ($conditionsData as $conditionData) {
            if (empty($conditionData)) {
                continue;
            }
            $condition = null;
            if (isset($conditionData['conditionId'])) {
                $conditionId = $conditionData['conditionId'];
                $condition = Condition::find($conditionId)
                    ->where(['id' => $conditionId])
                    ->one();
            } else {
                $condition = new Condition();
                $condition->task_id = $taskId;
            }
            foreach ($conditionData['events'] as $eventType => $eventAr) {
                if (empty($eventAr)) {
                    continue;
                }
                $condition->type = EventType::getTypeIdByName($eventAr['type']);
                $condition->params = Json::encode($eventAr['params']);
                $condition->save();
            }
        }
    }
}