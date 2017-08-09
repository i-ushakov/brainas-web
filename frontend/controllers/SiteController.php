<?php
namespace frontend\controllers;

use common\components\BAException;
use frontend\components\FeedbackManager;
use Yii;
use common\models\LoginForm;
use frontend\components\Factory\GoogleClientFactory;
use frontend\components\GoogleIdentityHelper;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * Site controller
 * Supplies access to usual site functional like: About Page, Login, Logout, Home and etc.
 */
class SiteController extends Controller
{

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Redirect to Single Page Application
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->redirect(\Yii::$app->urlManager->createUrl("main/panel"));
    }

    /**
     * Privacy Policy
     *
     * @return string
     */
    public function actionPolicy()
    {
        return $this->render('policy', []);
    }


    /**
     * SignIn with google.
     *
     */
    public function actionSignIn() {
        $result = array();
        $authCode = file_get_contents('php://input');

        /* @var $googleIdentityHelper GoogleIdentityHelper */
        $googleIdentityHelper = Yii::$container->get(GoogleIdentityHelper::class);

        try {
            if ($googleIdentityHelper->signIn($authCode)) {
                $result['status'] = "SUCCESS";
                $result['message'] = "User was logged as " . Yii::$app->user->identity['username'];
            }
        } catch (BAException $ex) {
            $result['status'] = "FAILED";
            $result['message'] = $ex->getMessage();;
        }

        \Yii::$app->response->format = 'json';
        echo json_encode($result);
        return;
    }

    /**
     * SignOut
     *
     */
    public function actionSignOut() {
        Yii::$app->user->logout();
    }


    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        return $this->render('contact');
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout() {
        $aboutFilePdf = \Yii::getAlias('@webroot') . "/docs/about.pdf";
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename=" ' . $aboutFilePdf . '"');
        @readfile($aboutFilePdf);
    }

    /**
     * Displays feedback page.
     *
     * @return mixed
     */
    public function actionFeedback()
    {
        if (!Yii::$app->user->isGuest) {
            $request = Yii::$app->request;
            $userEmail = Yii::$app->user->identity['username'];
            if ($request->isPost) {
                $params = $request->post();

                /* @var $feedbackManager FeedbackManager */
                $feedbackManager = Yii::$container->get(FeedbackManager::class);
                $result = $feedbackManager->sendFeedback($params, $userEmail);

                Yii::$app->response->format = 'json';
                echo json_encode($result);
            } else {
                $this->view->params['userEmail'] = $userEmail;
                return $this->render('feedback');
            }
        } else {
            return $this->goHome();
        }
    }

    /**
     * This method allow to refresh authentication for client side if user is not active
     */
    public function actionRefreshAuthorization() {
        require_once(Yii::$app->basePath . '/views/layouts/_google_identity_head.php');
        echo "You are logged under email: " . Yii::$app->user->identity['username'];
    }
}
