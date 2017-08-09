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


use \Mockery as m;

class SignInTest extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testThrowAuthCodeEmpty()
    {
        $this->tester->expectException(
            new BAException(
                GoogleIdentityHelper::AUTH_CODE_MUSTNT_BE_EMPTY,
                BAException::EMPTY_PARAM_EXCODE
            ),
            function() {
                $authCode = null;
                /* @var $googleIdentityHelper GoogleIdentityHelper */
                $googleIdentityHelper = Yii::$container->get(GoogleIdentityHelper::class);
                $googleIdentityHelper->signIn($authCode);
            }
        );
    }

    public function testSuccessfulAuthentication()
    {
        $testAuthCode = 'auth_code';
        $testAccessToken = 'access_token';
        $testUserEmail = "user_email";
        $testUser = new User();

        /* @var $app \yii\web\Application */
        $app = Yii::$app;

        /* @var $client Google_Client */
        $client = m::mock(Google_Client::class . "[authenticate]");
        $client->shouldReceive('authenticate')->once()->with($testAuthCode)->andReturn($testAccessToken);

        /* @var $googleIdentityHelper GoogleIdentityHelper*/
        $googleIdentityHelper = m::mock(
            GoogleIdentityHelper::class . "[retrieveUserEmail, loginUserInYii]",
            [$client, $app]
        );
        $googleIdentityHelper->shouldReceive('retrieveUserEmail')->once()->with($client)->andReturn($testUserEmail);
        $googleIdentityHelper->shouldReceive('loginUserInYii')->once()->with($testUserEmail, $testAccessToken)->andReturn($testUser);


        $result = $googleIdentityHelper->signIn($testAuthCode);
        $this->tester->assertEquals(true, $result);
    }

    public function testFailedAuthentication()
    {
        $authCode = 'auth_code';
        $testAccessToken = 'access_token';

        /* @var $app \yii\web\Application */
        $app = Yii::$app;

        /* @var $client Google_Client */
        $client = m::mock(Google_Client::class . "[authenticate]");
        $client->shouldReceive('authenticate')->once()->with($authCode)->andReturn($testAccessToken);

        /* @var $googleIdentityHelper GoogleIdentityHelper*/
        $googleIdentityHelper = m::mock(
            GoogleIdentityHelper::class . "[retrieveUserEmail, loginUserInYii]",
            [$client, $app]
        );
        $googleIdentityHelper->shouldReceive('retrieveUserEmail')->once()->with($client)->andReturn(null);
        $googleIdentityHelper->shouldReceive('loginUserInYii')->never();

        $result = $googleIdentityHelper->signIn($authCode);
        $this->tester->assertEquals(false, $result);
    }
}