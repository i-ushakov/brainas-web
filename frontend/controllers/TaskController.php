<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 10/18/2015
 * Time: 2:11 PM
 */

namespace frontend\controllers;


use common\models\Condition;
use common\models\Event;
use Yii;
use yii\web\Controller;
use common\models\Task;
use common\models\EventType;
use yii\helpers\Json;

class TaskController extends Controller {
    /**
     * Return tasks
     *
     * @return mixed
     */
    public function actionGet() {

        $tasks = Task::find()
            //->where(['status' => Customer::STATUS_ACTIVE])
            ->orderBy('id')
            ->all();

        $items = array();
        foreach ($tasks as $task) {
            $items[] = $this->prepareTsakForSending($task);
        }

        \Yii::$app->response->format = 'json';
        return $items;
    }


    public function actionSave() {
        $result = array();

        $post = Yii::$app->request->post();
        $taskForSave = Json::decode($post['task']);
        $taskId = $taskForSave['id'];

        if (is_null($taskId)) {
            $task = new Task();
        } else {
            $task = Task::find($taskId)
                ->where(['id' => $taskId])
                ->one();
        }

        if (isset($taskForSave['message'])) {
            $task->message = $taskForSave['message'];
        }

        if (isset($taskForSave['description'])) {
            $task->description = $taskForSave['description'];
        }

        if (isset($taskForSave['conditions']) && count($taskForSave['conditions']) > 0) {
            $conditionsAr = Json::decode($post['conditions']);
            foreach($conditionsAr as $conditionAr) {
                $condition = null;
                if (isset($conditionAr['conditionId'])) {
                    $conditionId = $conditionAr['conditionId'];
                    $condition = Condition::find($conditionId)
                        ->where(['id' => $conditionId])
                        ->one();
                } else {
                    $condition = new Condition();
                    $condition->task_id = $taskId;
                    $condition->save();
                }
                foreach($conditionAr['events'] as $eventType => $eventAr) {
                    if (isset($eventAr['eventId'])) {
                        $eventId = $eventAr['eventId'];
                        $event = Event::find($eventId)
                            ->where(['id' => $eventId])
                            ->one();

                        if (isset($eventAr['deleted'])) {
                            $event->delete();
                        }
                        $event->params = Json::encode($eventAr['params']);
                        $event->save();
                    } else {
                        $event = new Event();
                        $event->condition_id = $condition->id;
                        $event->type = EventType::getTypeIdByName($eventAr['type']);
                        $event->params = Json::encode($eventAr['params']);
                        $event->save();
                    }


                ///if(isset($conditionAr['GPS'])) {
                    //$event = new Event();
                //}
                }
            }
        }

        if ($task->validate()) {
            $task->save();
            $result['status'] = "OK";
            $result['task'] = $this->prepareTsakForSending($task);
        } else {
            $errors = $task->errors;
            $result['status'] = "FAILED";
            $result['errors'] = $errors;
        }

        \Yii::$app->response->format = 'json';
        return $result;
    }

    public function actionRemove() {
        $result = array();
        $result['status'] = "FAILED";

        $post = Yii::$app->request->post();
        $taskForRemove = Json::decode($post['task']);
        $taskId = $taskForRemove['id'];

        $task = Task::find($taskId)
            ->where(['id' => $taskId])
            ->one();

        if (!empty($task)) {
            if($task->delete()) {
                $result['status'] = "OK";
            }
        } else {
            $result['message'] = "No task with id = " . $taskId . " not exists";
        }


        \Yii::$app->response->format = 'json';
        return $result;
    }

    private function prepareTsakForSending($task){
        $item['id'] = $task->id;
        $item['message'] = $task->message;
        $item['description'] = $task->description;
        $conditions = $task->conditions;
        foreach($conditions as $condition){
            $c = array();
            $c['conditionId'] = $condition->id;
            $events = $condition->events;
            foreach($events as $event) {
                $c[$event->eventType->name]['eventId'] = $event->id;
                $c[$event->eventType->name]['type'] = $event->eventType->name;
                $c[$event->eventType->name]['params'] = json_decode($event->params);
            }
            $item['conditions'][] = $c;
        }

        return $item;
    }
}