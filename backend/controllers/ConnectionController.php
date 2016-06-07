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
use common\infrastructure\ChangeOfTask;
use common\models\Task;
use backend\components\TaskSyncHelper;


use yii\web\Controller;
use yii\web\HttpException;

class ConnectionController extends Controller {

    public static $jsonGoogleClientConfig = "/var/www/brainas.net/backend/config/client_secret_925705811320-cenbqg1fe5jb804116oefl78sbishnga.apps.googleusercontent.com.json";

    private $userId;
    private $token;
    private $synchronizedObjects;

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionGetTasks() {
        // get user id and access token
        $token = $this->verifyUserAccess();

        // retrive xml-document with device changes from file
        $deviceChanges = $this->loadDeviceChangesFromXML();
        $taskSyncHelper = new TaskSyncHelper($deviceChanges, $this->userId, $token);
        $xmlResponse = $taskSyncHelper->doSynchronizationOfTasks();

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
        return $token;
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

    private function loadDeviceChangesFromXML() {
        $deviceChangesInXML = simplexml_load_file($_FILES['all_changes_xml']['tmp_name']);
        return $deviceChangesInXML;
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