<?php
namespace frontend\controllers;

use Yii;
use common\models\LoginForm;
use common\models\User;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use frontend\components\GoogleIdentityHelper;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * Site controller
 */
class SiteController extends Controller
{

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->redirect(\Yii::$app->urlManager->createUrl("main/panel"));
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * SignIn with google.
     *
     */
    public function actionSignIn() {
        $result = array();
        $authCode = file_get_contents('php://input');

        if (isset($authCode)) {
            $client = GoogleIdentityHelper::getGoogleClient();
            $accessToken = $client->authenticate($authCode);
            $userEmail = GoogleIdentityHelper::authenticationOfUser($accessToken);
            if ($userEmail != null) {
                $user = GoogleIdentityHelper::loginUserInYii($userEmail, $accessToken);
                if ($user != null){
                    $result['status'] = "SUCCESS";
                    $result['message'] = "User was logged as " . Yii::$app->user->identity['username'];
                } else {
                    $result['status'] = "FAILED";
                    $result['message'] = "Cannot authenticate user";
                }
            } else {
                $result['status'] = "FAILED";
                $result['message'] = "Cannot authenticate user";
            }
        } else {
            $result['status'] = "FAILED";
            $result['message'] = "Code is null";
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
