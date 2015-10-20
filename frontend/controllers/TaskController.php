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

class TaskController extends Controller {
    /**
     * Displays main panel.
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
            $items[] = $item;
        }

        /*$items = [
            ['id' => 1, 'message' => 'Test message'],
            ['id' => 2, 'message' => 'Test message2']
        ];*/
        \Yii::$app->response->format = 'json';
        return $items;
    }
}