<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 10/18/2015
 * Time: 2:11 PM
 */

namespace frontend\controllers;


use Yii;
use yii\web\Controller;
use common\models\Task;
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
            $item['id'] = $task->id;
            $item['message'] = $task->message;
            $item['description'] = $task->description;
            $conditions = $task->conditions;
            foreach($conditions as $condition){
                $c = array();
                $events = $condition->events;
                foreach($events as $event) {
                    $c[$event->eventType->name]['type'] = $event->eventType->name;
                    $c[$event->eventType->name]['params'] = json_decode($event->params);
                }
                $item['conditions'][] = $c;
            }
            $items[] = $item;
        }

        \Yii::$app->response->format = 'json';
        return $items;
    }
}