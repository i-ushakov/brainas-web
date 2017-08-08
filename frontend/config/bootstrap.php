<?php

use yii\di\Instance;
use common\components\MailSender;
use frontend\components\GoogleIdentityHelper;
use frontend\components\FeedbackManager;

$container = Yii::$container;

$container->setDefinitions([
    GoogleIdentityHelper::class => [
        ['class' => GoogleIdentityHelper::class]
    ],

    MailSender::class => [
        ['class' => MailSender::class]
    ],

    FeedbackManager::class => [
        ['class' => FeedbackManager::class],
        [
            Instance::of(MailSender::class)
        ]
    ]
]);
