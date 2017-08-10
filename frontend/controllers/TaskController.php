<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 10/18/2015
 * Time: 2:11 PM
 */

namespace frontend\controllers;

use frontend\components\TaskConverter;
use frontend\components\TasksManager;
use frontend\components\TasksQueryBuilder;
use common\models\Task;

use Yii;
use yii\web\Controller;
use yii\helpers\Json;

class TaskController extends Controller {

    private $userId;
    private $result = array();

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Return tasks in JSON format
     *
     * @return mixed
     */
    public function actionGet()
    {
        if ($this->checkThatUserIsNotAGuest()) {
            $userId = Yii::$app->user->id;

            $statusesFilter = (isset($_GET['statusesFilter']) ? $_GET['statusesFilter'] : null);
            $typeOfSort = (isset($_GET['typeOfSort']) ? $_GET['typeOfSort'] : null);

            /* @var $tasksQueryBuilder TasksQueryBuilder */
            $tasksQueryBuilder = Yii::$container->get(TasksQueryBuilder::class);
            $tasksQueryBuilder->setUserId($userId);

            $tasks = $tasksQueryBuilder->get($statusesFilter, $typeOfSort);

            $tasksAr = [];
            foreach ($tasks as $task) {
                /** @var $taskConverter TaskConverter */
                $taskConverter = Yii::$container->get(TaskConverter::class);
                $tasksAr[] = $taskConverter->prepareTaskForResponse($task);
                $this->result = $tasksAr;
            }
        }

        Yii::$app->response->format = 'json';
        return $this->result;
    }


    /**
     * Saving task gotten from client side of web-app
     *
     * @return array
     */
    public function actionSave() {
        if ($this->checkThatUserIsNotAGuest()) {
            $post = Yii::$app->request->post();
            $taskForSave = Json::decode($post['task']);

            /** @var $tasksManager TasksManager */
            $tasksManager = Yii::$container->get(TasksManager::class);
            $tasksManager->setUser(Yii::$app->user->identity);
            $task = $tasksManager->handleTask($taskForSave);

            if (!empty($task)) {
                $this->result['status'] = "OK";

                /** @var $taskConverter TaskConverter */
                $taskConverter = Yii::$container->get(TaskConverter::class);
                $this->result['task'] = $taskConverter->prepareTaskForResponse($task);
            } else {
                $this->result['status'] = "FAILED";
                $this->result['errors'][] = "No task with id = " . $taskForSave['id'] . "that is owned of user  whit id=" . Yii::$app->user->id;
            }
        }

        \Yii::$app->response->format = 'json';
        return $this->result;
    }

    public function actionRemove() {
        if ($this->checkThatUserIsNotAGuest()) {

            $post = Yii::$app->request->post();
            $taskForRemove = Json::decode($post['task']);
            $taskId = $taskForRemove['id'];


            /** @var $tasksManager TasksManager */
            $tasksManager = Yii::$container->get(TasksManager::class);
            $tasksManager->setUser(Yii::$app->user->identity);
            if ($tasksManager->removeTask($taskId)) {
                $this->result['status'] = "OK";
            } else {
                $this->result['status'] = "FAILED";
                $this->result['type'] = "remove_error";
                $this->result['message'] = "No task with id = " . $taskId . "that is owned of user  " . $task->user->name;;
            }

        }
        \Yii::$app->response->format = 'json';
        return $this->result;

    }

    /**
     * Checking that user is logged in Yii
     *
     * @return bool
     */
    private function checkThatUserIsNotAGuest() {
        if (Yii::$app->user->isGuest) {
            $this->result['status'] = "FAILED";
            $this->result['type'] = "must_be_signed_in";
            return false;
        } else {
            $this->userId = Yii::$app->user->id;
            return true;
        }
    }
}