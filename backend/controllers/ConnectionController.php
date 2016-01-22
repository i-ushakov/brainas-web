<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 11/16/2015
 * Time: 1:01 PM
 */

namespace backend\controllers;

use Yii;
use common\infrastructure\ChangedTask;
use common\models\Task;
use yii\helpers\Json;
use yii\web\Controller;

class ConnectionController extends Controller {


    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionGetTasks()
    {
        /*
         * $response = Yii::$app->getResponse();
$response->headers->set('Content-Type', 'image/jpeg');
$response->format = Response::FORMAT_RAW;
if ( !is_resource($response->stream = fopen($imgFullPath, 'r')) ) {
   throw new \yii\web\ServerErrorHttpException('file access failed: permission deny');
}
return $response->send();
         */

        //$response = Yii::$app->getResponse();
        //$response->headers->set('Content-Type', 'image/jpeg');

        $changedTasks = $this->getChangedTasks();

        $created = array();
        $updated = array();
        $deleted = array();
        foreach ($changedTasks as $changedTask) {
            if ($changedTask->action == "Created") {
                $created[$changedTask->task_id]['action'] = $changedTask->action;
                $created[$changedTask->task_id]['datetime'] = $changedTask->datetime;
            } else if ($changedTask->action == "Changed") {
                $updated[$changedTask->task_id]['action'] = $changedTask->action;
                $updated[$changedTask->task_id]['datetime'] = $changedTask->datetime;
            } else if ($changedTask->action == "Deleted") {
                $deleted[$changedTask->task_id]['action'] = $changedTask->action;
                $deleted[$changedTask->task_id]['datetime'] = $changedTask->datetime;
            }
        }

        $xmlWithTasks = "";
        $xmlWithTasks .= '<?xml version="1.0" encoding="UTF-8"?>';
        // Fox TEST
        $xmlWithTasks .= '<accessToken>' . $_POST['accessToken'] . '</accessToken>>';
        // For TEST (END)
        $xmlWithTasks .= '<tasks>';

        // New (created) Tasks
        $xmlWithTasks .= '<created>';
        $createdTasks = Task::find()
            ->where(array('in', 'id', array_keys($created)))
            ->orderBy('id')
            ->all();
        foreach ($createdTasks as $createdTask) {
            $xmlWithTasks .= $this->buildTaskXml($createdTask,  $created[$createdTask->id]['datetime']);
        }
        $xmlWithTasks .= '</created>';

        // Old (updated) Tasks
        $xmlWithTasks .= '<updated>';
        $updatedTasks = Task::find()
            ->where(array('in', 'id', array_keys($updated)))
            ->orderBy('id')
            ->all();
        foreach ($updatedTasks as $updatedTask) {
            $xmlWithTasks .= $this->buildTaskXml($updatedTask, $updated[$updatedTask->id]['datetime']);
        }
        $xmlWithTasks .= '</updated>';

        // Removed (deleted) Tasks
        $xmlWithTasks .= '<deleted>';
        foreach ($deleted as $id => $d) {
            $xmlWithTasks .= '<deletedTask ' .
                    'global-id="' . $id . '" ' .
                    'time-changes="' . $d['datetime'] . '"' .
                '></deletedTask>';
        }

        $xmlWithTasks .= '</deleted>';
        $xmlWithTasks .= '</tasks>';
        echo $xmlWithTasks;
    }

    public function actionAcceptedChanges() {
        $this->enableCsrfValidation = false;
        Yii::$app->controller->enableCsrfValidation = false;
        $post = Yii::$app->request->post();
        $acceptedChangesJSON = file_get_contents("php://input");
        $acceptedChanges = Json::decode($acceptedChangesJSON);
        //$acceptedChanges = Json::decode($acceptedChangesJSON);
        $records = ChangedTask::find()
            ->where(array('in', 'task_id', array_keys($acceptedChanges['tasks'])))
            ->andWhere(['user_id' => 1])
            ->orderBy('id')
            ->all();
        foreach($records as $record) {
            $record->delete();
        }
    }

    private function buildTaskXml($task, $datetime) {
        $xml = '' .
            '<task global-id="' . $task->id . '" time-changes="' . $datetime . '">' .
                '<message>' . $task->message . '</message>' .
                '<description>' . $task->description . '</description>' .
                '<conditions>' . $this->buildConditionsPart($task) . '</conditions>' .
                '<status>WAITING</status>' .
            '</task>';
        return $xml;
    }

    private function buildConditionsPart($task) {
        $xml = "";
        $conditions = $task->conditions;
        foreach($conditions as $condition){
            $xml .= "<condition id='" . $condition->id . "' task-id='" . $condition->task_id ."'>";
            $events = $condition->events;
            foreach($events as $event) {
                $xml .= "<event type='" . $event->eventType->name . "' id='" . $event->id . "'>";
                $xml .= "<params>";
                $params = json_decode($event->params);
                foreach ($params as $name => $value) {
                    $xml .= "<$name>$value</$name>";
                }
                $xml .= "</params>";
                $xml .= "</event>";
            }
            $xml .= "</condition>";
        }
        return $xml;
    }

    private function getChangedTasks() {
        $userId = $this->getUserId();
        $changedTasks = ChangedTask::find()
            ->where(['user_id' => $userId])
            ->orderBy('datetime')
            ->all();
        return $changedTasks;
    }

    private function getUserId() {
        $userId = 1;
        return $userId;
    }
}