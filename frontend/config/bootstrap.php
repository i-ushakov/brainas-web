<?php

use yii\di\Instance;
use common\components\MailSender;
use frontend\components\GoogleIdentityHelper;
use frontend\components\FeedbackManager;
use frontend\components\Factory\GoogleClientFactory;

$container = Yii::$container;

$container->setDefinitions([
    'application' => function() {
        return Yii::$app;
    },

    'GoogleClientWithoutToken' => function() {
        return GoogleClientFactory::create();
    },

    GoogleIdentityHelper::class => [
        ['class' => GoogleIdentityHelper::class],
        [
            Instance::of('GoogleClientWithoutToken'),
            Instance::of('application'),
        ]
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
