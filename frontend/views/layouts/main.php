<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use frontend\assets\SPAAsset;
use frontend\assets\GoogleAsset;
use common\widgets\Alert;

AppAsset::register($this);
GoogleAsset::register($this);
SPAAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-signin-client_id" content="925705811320-cenbqg1fe5jb804116oefl78sbishnga.apps.googleusercontent.com">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <?require_once(Yii::$app->basePath . '/views/layouts/_google_identity_head.php');?>
    <?require_once(Yii::$app->basePath . '/web/js/SPAParams.php');?>

</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'Brain Assistant Project',
        'brandUrl' => Yii::$app->homeUrl,
        'brandOptions' => ['class' => 'brainas-logo-icon-top-right'],
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $menuItems = [
        ['label' => 'Home', 'url' => ['/site/index']],
        ['label' => 'About', 'url' => ['/site/about']],
        ['label' => 'Contact', 'url' => ['/site/contact']],
        ['label' => 'Feedback', 'url' => ['/site/feedback']],
    ];
    $menuItems[] = ['label' => '', 'url' => ['/site/login'], 'options'=> ['id'=>'navbar', 'class' => 'google-sign-btn'],];
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'Sign in with Google', 'url' => ['/'], 'options' => ['id' => 'signinButton', 'class' => 'google-signin-btn',]];
    } else {
        $menuItems[] = ['label' => ' Sign out', 'url' => ['/'], 'options' => ['id' => 'signoutButton', 'class' => '',]];
    }
    https://brainas.com/main/panel

    //<div class="g-signin2" data-onsuccess="onSignIn"></div>
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    echo "<div id='navbar'></div>";

    NavBar::end();
    ?>
    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Brain Assistant <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?require_once(Yii::$app->basePath . '/views/layouts/_templates.php');?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
