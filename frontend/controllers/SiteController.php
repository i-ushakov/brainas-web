<?php
namespace frontend\controllers;

use common\components\BAException;
use Yii;
use common\models\LoginForm;
use common\components\MailSender;
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

        try {
            if (GoogleIdentityHelper::signIn($authCode)) {
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
     * SignOut with google.
     *
     */
    public function actionSignOut() {
        Yii::$app->user->logout();
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
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
        $request = Yii::$app->request;
        if (!Yii::$app->user->isGuest) {
            $userEmail = Yii::$app->user->identity['username'];
            if ($request->isPost) {
                $params = $request->post();
                if (!isset($params['subject'])) {
                    $result = array(
                        'status' => 'failed',
                        'type' => 'no_subject'
                    );
                } else if (!isset($params['message']) || empty($params['message'])){
                    $result = array(
                        'status' => 'failed',
                        'type' => 'no_message'
                    );
                } else if (MailSender::sendFeedbackEmail($userEmail, $params)) {
                    $result = array('status' => 'success');
                } else {
                    $result = array(
                        'status' => 'failed',
                        'type' => 'sending_is_failed'
                    );
                }
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
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    public function actionRefreshAuthorization() {
        require_once(Yii::$app->basePath . '/views/layouts/_google_identity_head.php');
        echo "You are logined under email: " . Yii::$app->user->identity['username'];
    }
}
