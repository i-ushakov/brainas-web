<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 11/16/2015
 * Time: 1:01 PM
 */


namespace backend\controllers;


use backend\components\TaskHelper;
use backend\components\TaskXMLHelper;
use Google_Client;
use Yii;
use common\models\User;
use common\infrastructure\ChangeOfTask;
use common\models\Task;
use common\models\Condition;
use common\models\Event;
use common\models\EventType;
use backend\components\XMLResponseBuilder;


use yii\web\Controller;
use yii\web\HttpException;

class ConnectionController extends Controller {

    public static $jsonGoogleClientConfig = "/var/www/brainas.net/backend/config/client_secret_925705811320-cenbqg1fe5jb804116oefl78sbishnga.apps.googleusercontent.com.json";

    private $userId;
    private $token;
    private $initSyncTime = null;
    private $synchronizedObjects;
    private $existingTasksOnDevice;

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionGetTasks() {
        // get user id and access token
        $this->verifyUserAccess();

        // Get chnaged and deletet task
        // from time of last sync for this user
        $serverChanges = $this->getChanges();

        // The objects that was updated from device
        $this->synchronizedObjects = $this->processAllChangesFromDevice($serverChanges);

        // Build xml-document with server-changes
        // and data about changes from device that were accepted
        $xmlResponse = XMLResponseBuilder::buildXMLResponse($serverChanges, $this->synchronizedObjects, $this->initSyncTime, $this->token);
        echo $xmlResponse;
    }

    private function verifyUserAccess() {
        // Create Google client and get accessToken
        $client = $this->getGoogleClient();
        $token = $this->getAccessToken($client);
        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($token['refresh_token']);
            $token = $client->getAccessToken();
        }

        // Get user's date (email) and user id in our system
        $data = $client->verifyIdToken();
        $userEmail = $data['email'];
        $this->userId = $this->getUserIdByEmail($userEmail);

        // save refresh token if exist or retrieve from database and send to client
        if (isset($token['refresh_token'])) {
            $this->saveRefreshToken($token['refresh_token']);
        } else {
            $refreshToken = $this->getRefreshToken();
            if ($refreshToken != null) {
                $token['refresh_token'] = $refreshToken;
            }
        }
        $this->token = $token;
    }

    private static function getGoogleClient() {
        $client = new Google_Client();
        $client->setAuthConfigFile(self::$jsonGoogleClientConfig);
        //$client->setRedirectUri("http://brainas.net/backend/web/connection/");
        $client->setRedirectUri("http://brainas.net/site/login");
        $client->setScopes("https://www.googleapis.com/auth/plus.login");
        $client->setAccessType('online'); //offline
        $client->setApprovalPrompt('force');

        return $client;
    }

    private function getAccessToken($client) {
        $code = $this->getCodeFromPost();
        if ($code != null) {
            $client->authenticate($code);
            $token = $client->getAccessToken();
        } else {
            $token = json_decode($this->getTokenFromPost(), true);
            $client->setAccessToken($token);
        }
        return $token;
    }

    private function getChanges() {
        $serverChanges = array();
        if (!isset($_POST['initSyncTime'])) {
            // Getting changed tasks from website
            $changesOfTasks = $this->getChangedTasks(null);
            $currentDatetime = new \DateTime();
            $currentDatetime->setTimezone(new \DateTimeZone("UTC"));
            $this->initSyncTime = $currentDatetime->format('Y-m-d H:i:s');
        } else {
            $this->initSyncTime = $_POST['initSyncTime'];
            $changesOfTasks = $this->getChangedTasks($this->initSyncTime);
            $currentDatetime = new \DateTime();
            $currentDatetime->setTimezone(new \DateTimeZone("UTC"));
            $this->initSyncTime = $currentDatetime->format('Y-m-d H:i:s');
        }

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
            $changeOfTask->action = "Deleted";
            $changeOfTask->save();
            return false;
        }

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

    private function getTokenFromPost() {
        $post = Yii::$app->request->post();
        //$post['accessToken'] = "";
        if(isset($post['accessToken'])) {
            $accessToken = $post['accessToken'];
        } else {
            throw new HttpException(470 ,'Token wasn\'t sent');
        }
        return $accessToken;
    }

    private function getCodeFromPost() {
        $post = Yii::$app->request->post();
        //$post['accessToken'] = "";
        if(isset($post['accessCode'])) {
            $accessCode = $post['accessCode'];
            return $accessCode;
        }
        return null;
    }

    private function getUserIdByEmail($userEmail) {
        $user = User::findOne(['username' => $userEmail]);
        if (!empty($user)) {
            return $user->id;
        } else {
            $user = new User();
            $user->username = $userEmail;
            $user->email = $userEmail;
            $user->save();
            return $user->id;
        }
    }

    private function processAllChangesFromDevice(&$serverChanges) {
        $synchronizedTasks = array();
        $allDeviceChangesInXML = simplexml_load_file($_FILES['all_changes_xml']['tmp_name']);

        // Find tasks that exists on device (client) but absent on server (use serverId)
        $this->existingTasksOnDevice = TaskXMLHelper::retrieveExistingTasksFromXML($allDeviceChangesInXML);
        $serverChanges['tasks']['deleted'] = TaskHelper::getTasksRemovedOnServer($this->existingTasksOnDevice, $this->userId);
        $changedTasks = $allDeviceChangesInXML->changedTasks;
        foreach($changedTasks->changedTask as $changedTask) {
            $statusOfChanges = (String)$changedTask->change[0]->status;
            if ((string)$changedTask['globalId'] == 0 && $statusOfChanges != "DELETED") {
                $globalId = $this->addTaskFromDevice($changedTask);
                $localId = (string)$changedTask['id'];
                $synchronizedTasks[$localId] = $globalId;
            } else {
                $globalId = (string)$changedTask['globalId'];
                $localId = (string)$changedTask['id'];
                if (Task::findOne($globalId) != null) {
                    $serverChangesTime = $this->getTimeOfTaskChanges($globalId);
                    $clientChangesTime = (String)$changedTask->change[0]->changeDatetime;
                    if (strtotime($serverChangesTime) < strtotime($clientChangesTime)) {
                        $status = (String)$changedTask->change[0]->status;
                        if ($status == "DELETED") {
                            $this->deleteTaskFromDevice($changedTask);
                            $localId = 0;
                        } elseif ($status == "UPDATED" || $status == "CREATED") {
                            $this->updateTaskFromDevice($changedTask);
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

        $this->synchronizedObjects['tasks'] = $synchronizedTasks;
        return $this->synchronizedObjects;
    }

    private function addTaskFromDevice ($newTaskFromDevice) {
        $task = new Task();
        $task->message = (String)$newTaskFromDevice->message;
        $task->user = $this->userId;
        if ($task->save()) {
            foreach ($newTaskFromDevice->conditions->condition as $c) {
                TaskXMLHelper::addConditionFromXML($c, $task->id, $this->synchronizedObjects);
            }
            $changeDatetime = (String)$newTaskFromDevice->change[0]->changeDatetime;
            ChangeOfTask::loggingChangesForSync("Created", $changeDatetime, $task);
            return $task->id;
        }
        return 0;
    }

    private function updateTaskFromDevice ($changedTask) {
        $id = (string)$changedTask['globalId'];
        $message = (string)$changedTask->message;
        $description = (string)$changedTask->description;
        $task = Task::findOne($id);
        $task->message = $message;
        $task->description = $description;
        $task->save();
        TaskXMLHelper::cleanDeletedConditions($changedTask->conditions->condition, $task->id);
        foreach ($changedTask->conditions->condition as $c) {
            TaskXMLHelper::addConditionFromXML($c, $task->id, $this->synchronizedObjects);
        }
        $changeDatetime = (String)$changedTask->change[0]->changeDatetime;
        ChangeOfTask::loggingChangesForSync("Changed", $changeDatetime, $task);
        return $task->id;
    }

    private function deleteTaskFromDevice ($changedTask) {
        $id = (string)$changedTask['globalId'];
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

    private function getRefreshToken() {
        $refreshToken = null;
        $params = [':user_id' => $this->userId];
        $r = Yii::$app->db->createCommand('SELECT * FROM refresh_tokens WHERE user_id=:user_id')
            ->bindValues($params)
            ->queryOne();

        if (isset($r['refresh_token'])) {
            $refreshToken = $r['refresh_token'];
        }

        return $refreshToken;
    }

    private function saveRefreshToken($refreshToken) {
        $params = [
            ':user_id' => $this->userId,
            ':refresh_token' => $refreshToken,
        ];

        Yii::$app->db->createCommand('
                INSERT INTO refresh_tokens (user_id, refresh_token)
                VALUES(:user_id, :refresh_token) ON DUPLICATE KEY UPDATE
                user_id=:user_id, refresh_token=:refresh_token
            ')
            ->bindValues($params)
            ->execute();
        return;
    }
}