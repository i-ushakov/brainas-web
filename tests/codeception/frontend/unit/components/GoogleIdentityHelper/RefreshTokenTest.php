<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 7/11/2017
 * Time: 10:44 AM
 */
use common\models\User;
use common\models\RefreshToken;
use common\components\BAException;
use frontend\components\GoogleIdentityHelper;
use frontend\components\Factory\GoogleClientFactory;

use \Google_Client;
use \Mockery as m;

class RefreshTokenTests extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testThrowNoRefreshTokenEx()
    {
        $accessToken = Yii::$app->params['testAccessTokenWithoutToken'];
        $user = m::mock(User::class . "[access_token]");
        $user->access_token = $accessToken;
        $this->tester->expectException(
            new BAException(
                GoogleIdentityHelper::NO_REFRESH_TOKEN_MSG,
                BAException::NOT_ENOUGH_DATA
            ),
            function() use ($user) {
                /* @var $client Google_Client */
                $client = m::mock(Google_Client::class . "[authenticate]");

                /* @var $app \yii\web\Application */
                $app = Yii::$app;

                $googleIdentityHelper = new GoogleIdentityHelper($client, $app);
                $googleIdentityHelper->getGoogleClientWithToken($user);
            }
        );
    }

    public function testThrowProblemWithRefreshTokenEx () {
        $wrongAccessToken = Yii::$app->params['testWrongAccessToken'];
        $refreshToken = m::mock(RefreshToken::class . "[refresh_token]");
        $refreshToken->refresh_token = $wrongAccessToken;
        $user = m::mock(User::class . "[access_token, getRefreshToken]");
        $user->access_token = $wrongAccessToken;
        $user->shouldReceive('getRefreshToken')->andReturn($refreshToken);
        $this->tester->expectException(
            new BAException(
                GoogleIdentityHelper::PROBLEM_WITH_REFRESH_TOKEN_MSG,
                BAException::INVALID_PARAM_EXCODE
            ),
            function() use ($user) {
                /* @var $client Google_Client */
                $client = m::mock(Google_Client::class . "[authenticate]");

                /* @var $app \yii\web\Application */
                $app = Yii::$app;

                $googleIdentityHelper = new GoogleIdentityHelper($client, $app);
                $googleIdentityHelper->getGoogleClientWithToken($user);
            }
        );
    }
}