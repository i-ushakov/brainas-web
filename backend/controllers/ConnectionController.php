<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 11/16/2015
 * Time: 1:01 PM
 */


namespace backend\controllers;


use Google_Client;
use Yii;
use common\models\User;
use common\infrastructure\ChangedTask;
use common\models\Task;
use common\models\Condition;
use common\models\Event;
use common\models\EventType;


use yii\web\Controller;
use yii\web\HttpException;

class ConnectionController extends Controller {

    public static $jsonGoogleClientConfig = "/var/www/brainas.net/backend/config/client_secret_925705811320-cenbqg1fe5jb804116oefl78sbishnga.apps.googleusercontent.com.json";

    private $userId;
    private $token;
    private $initSyncTime = null;
    private $synchronizedObjects;

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
        $changes = $this->getChanges();

        // The objects that was updated from device
        $this->synchronizedObjects = $this->processAllChangesFromDevice($changes);

        // Build xml-document with server-changes
        // and data about changes from device that were accepted
        $xmlResponse = $this->buildXMLResponse($changes, $this->token);

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
        $changes = array();
        if (!isset($_POST['initSyncTime'])) {
            // Getting changed tasks from website
            $changedTasks = $this->getChangedTasks(null);
            $currentDatetime = new \DateTime();
            $currentDatetime->setTimezone(new \DateTimeZone("Europe/London"));
            $this->initSyncTime = $currentDatetime->format('Y-m-d H:i:s');
        } else {
            $this->initSyncTime = $_POST['initSyncTime'];
            $changedTasks = $this->getChangedTasks($this->initSyncTime);
            $currentDatetime = new \DateTime();
            $currentDatetime->setTimezone(new \DateTimeZone("Europe/London"));
            $this->initSyncTime = $currentDatetime->format('Y-m-d H:i:s');
        }

        $changes['tasks']['created'] = array();
        $changes['tasks']['updated'] = array();
        $changes['tasks']['deleted'] = array();
        foreach ($changedTasks as $changedTask) {
            if ($changedTask->action == "Created") {
                $changes['tasks']['created'][$changedTask->task_id]['action'] = $changedTask->action;
                $changes['tasks']['created'][$changedTask->task_id]['datetime'] = $changedTask->datetime;
            } else if ($changedTask->action == "Changed") {
                $changes['tasks']['updated'][$changedTask->task_id]['action'] = $changedTask->action;
                $changes['tasks']['updated'][$changedTask->task_id]['datetime'] = $changedTask->datetime;
            } else if ($changedTask->action == "Deleted") {
                $changes['tasks']['deleted'][$changedTask->task_id]['action'] = $changedTask->action;
                $changes['tasks']['deleted'][$changedTask->task_id]['datetime'] = $changedTask->datetime;
            }
        }
        return $changes;
    }

    private function buildXMLResponse($changes, $token) {
        $xmlResponse = "";
        $xmlResponse .= '<?xml version="1.0" encoding="UTF-8"?>';
        $xmlResponse .= '<syncResponse>';
        $xmlResponse .= '<tasks>';

        // Created tasks
        $xmlResponse .= '<created>';
        $createdTasks = Task::find()
            ->where(array('in', 'id', array_keys($changes['tasks']['created'])))
            ->orderBy('id')
            ->all();
        foreach ($createdTasks as $createdTask) {
            $xmlResponse .= $this->buildTaskXml($createdTask,  $changes['tasks']['created'][$createdTask->id]['datetime']);
        }
        $xmlResponse .= '</created>';

        // Updated tasks
        $xmlResponse .= '<updated>';
        $updatedTasks = Task::find()
            ->where(array('in', 'id', array_keys($changes['tasks']['updated'])))
            ->orderBy('id')
            ->all();
        foreach ($updatedTasks as $updatedTask) {
            $xmlResponse .= $this->buildTaskXml($updatedTask, $changes['tasks']['updated'][$updatedTask->id]['datetime']);
        }
        $xmlResponse .= '</updated>';

        // Deleted Tasks
        $xmlResponse .= '<deleted>';
        foreach ($changes['tasks']['deleted'] as $id => $d) {
            $xmlResponse .= '<deletedTask ' .
                'global-id="' . $id . '" ' .
                'time-changes="' . $d['datetime'] . '"' .
                '></deletedTask>';
        }

        $xmlResponse .= '</deleted>';
        $xmlResponse .= '</tasks>';

        if (!empty($this->synchronizedObjects)) {
            $xmlResponse .= '<synchronizedObjects>';
            // Tasks
            $synchronizedTasks = $this->synchronizedObjects['tasks'];
            if (!empty($synchronizedTasks)) {
                $xmlResponse .= '<synchronizedTasks>';
                foreach($synchronizedTasks as $localId => $globalId) {
                    $xmlResponse .= "<synchronizedTask>" .
                        "<localId>" . $localId . "</localId>" .
                        "<globalId>" . $globalId . "</globalId>" .
                        "</synchronizedTask>";
                }
                $xmlResponse .= '</synchronizedTasks>';
            }
            // Conditions
            if (!empty($this->synchronizedObjects['conditions'])) {
                $synchronizedConditions = $this->synchronizedObjects['conditions'];
                $xmlResponse .= '<synchronizedConditions>';
                foreach($synchronizedConditions as $localId => $globalId) {
                    $xmlResponse .= "<synchronizedCondition>" .
                        "<localId>" . $localId . "</localId>" .
                        "<globalId>" . $globalId . "</globalId>" .
                        "</synchronizedCondition>";
                }
                $xmlResponse .= '</synchronizedConditions>';
            }

            // Events
            if (!empty($this->synchronizedObjects['events'])) {
                $synchronizedEvents = $this->synchronizedObjects['events'];
                $xmlResponse .= '<synchronizedEvents>';
                foreach($synchronizedEvents as $localId => $globalId) {
                    $xmlResponse .= "<synchronizedEvent>" .
                        "<localId>" . $localId . "</localId>" .
                        "<globalId>" . $globalId . "</globalId>" .
                        "</synchronizedEvent>";
                }
                $xmlResponse .= '</synchronizedEvents>';
            }
            $xmlResponse .= '</synchronizedObjects>';
        }


        $xmlResponse .= '<initSyncTime>' . $this->initSyncTime . '</initSyncTime>';
        $xmlResponse .=  "<accessToken>" . json_encode($token) . "</accessToken>";;

        $xmlResponse .= '</syncResponse>';

        return $xmlResponse;
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

    private function getChangedTasks($initSyncTime) {
        if ($initSyncTime != null) {
            $changedTasks = ChangedTask::find()
                ->where([
                    'and',
                    ['=', 'user_id', $this->userId],
                    ['>', 'datetime', $initSyncTime]
                ])
                ->orderBy('datetime')
                ->all();
        } else {
            $changedTasks = ChangedTask::find()
                ->where(['user_id' => $this->userId])
                ->orderBy('datetime')
                ->all();
        }
        return $changedTasks;
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

    private function processAllChangesFromDevice($changes) {
        $synchronizedTasks = array();
        $allChangesInXML = simplexml_load_file($_FILES['all_changes_xml']['tmp_name']);

        $changedTasks = $allChangesInXML->changedTasks;
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
                        unset($changes['tasks']['updated'][$globalId]);
                    }
                    $synchronizedTasks[$localId] = $globalId;
                } else {
                    $changes['tasks']['deleted'][$globalId]['action'] = "Deleted";
                    $currentDatetime = new \DateTime();
                    $currentDatetime->setTimezone(new \DateTimeZone("Europe/London"));
                    $changes['tasks']['deleted'][$globalId]['datetime'] = $currentDatetime->format('Y-m-d H:i:s');
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
            $changeDatetime = (String)$newTaskFromDevice->change[0]->changeDatetime;
            $task->loggingChangesForSync("Created", $changeDatetime);
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
        Yii::warning("=====conditions======");
        Yii::warning($changedTask->conditions);
        $task->save();
        $this->cleanDeletedConditions($changedTask->conditions->condition, $task->id);
        foreach ($changedTask->conditions->condition as $c) {
            $this->addConditionFromXML($c, $task->id);
        }
        $changeDatetime = (String)$changedTask->change[0]->changeDatetime;
        $task->loggingChangesForSync("Changed", $changeDatetime);
        return $task->id;
    }

    private function addConditionFromXML(\SimpleXMLElement $conditionXML, $taskId) {
        if (isset($conditionXML['globalId']) && $conditionXML['globalId'] != 0) {
            $conditionId = $conditionXML['globalId'];
            $condition = Condition::find($conditionId)
                ->where(['id' => $conditionId])
                ->one();
        } else {
            $condition = new Condition();
            $condition->task_id = $taskId;
            $condition->save();
        }
        Yii::warning("!!!!@@@");
        Yii::warning($conditionXML);
        Yii::warning($conditionXML->events);
        Yii::warning($conditionXML->events->event);
        $this->cleanDeletedEvents($conditionXML->events->event, $conditionXML->id);
        foreach($conditionXML->events->event as $e) {
            $this->addEventFromXML($e, $condition->id);
        }
        $this->synchronizedObjects['conditions'][(string)$conditionXML['localId']] = $condition->id;
    }

    private function addEventFromXML(\SimpleXMLElement $eventXML, $conditionId) {
        if (isset($eventXML['globalId']) && $eventXML['globalId'] != 0) {
            $eventId = $eventXML['globalId'];
            $event = Event::find($eventId)
                ->where(['id' => $eventId])
                ->one();
        } else {
            $event = new Event();
            $event->condition_id = $conditionId;
        }
        Yii::warning("Fucking value");
        Yii::warning($eventXML);
        Yii::warning($eventXML->type);
        Yii::warning((string)$eventXML->type);
        $event->type = EventType::getTypeIdByName((string)$eventXML->type);
        $event->params = (string)$eventXML->params;
        $event->save();
        $this->synchronizedObjects['events'][(string)$eventXML['localId']] = (string)$eventXML['globalId'];
    }

    private function cleanDeletedConditions(\SimpleXMLElement $conditionsXML, $taskId) {
        $conditionsIds = array();
        foreach ($conditionsXML as $conditionXML) {
            if (isset($conditionXML['globalId']) && $conditionXML['globalId'] != 0) {
                $conditionsIds[] = $conditionXML['globalId'];
            }
        }
        $conditionsFromDB = Condition::find()
            ->where(['task_id' => $taskId])
            ->all();
        foreach($conditionsFromDB as $conditionFromDB) {
            if(!in_array ($conditionFromDB->id, $conditionsIds)) {
                $conditionFromDB->delete();
            }
        }
    }


    private function cleanDeletedEvents(\SimpleXMLElement $eventsXML, $conditionId) {
        $eventsIds = array();
        foreach ($eventsXML as $eventXML) {
            if (isset($eventXML['globalId']) && $eventXML['globalId'] != 0) {
                $eventsIds[] = $eventXML['globalId'];
            }
        }
        $eventsFromDB = Event::find()
            ->where(['condition_id' => $conditionId])
            ->all();
        foreach($eventsFromDB as $eventFromDB) {
            if(!in_array ($eventFromDB->id, $eventsIds)) {
                $eventFromDB->delete();
            }
        }
    }

    private function deleteTaskFromDevice ($changedTask) {
        $id = (string)$changedTask['globalId'];
        $task = Task::findOne($id);
        if (isset($task)) {
            $task->delete();
        }
    }

    private function getTimeOfTaskChanges($taskid) {
        $changedTask = ChangedTask::find()
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
Yii::warning("=====getRefreshToken======");
        Yii::warning($this->userId);
        $r = Yii::$app->db->createCommand('SELECT * FROM refresh_tokens WHERE user_id=:user_id')
            ->bindValues($params)
            ->queryOne();
        Yii::warning('SELECT * FROM refresh_tokens WHERE user_id=:user_id');
        if (isset($r['refresh_token'])) {
            $refreshToken = $r['refresh_token'];
        }
        Yii::warning("===========");
        Yii::warning($refreshToken);
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