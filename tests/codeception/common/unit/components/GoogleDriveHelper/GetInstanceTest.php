<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 6/29/2017
 * Time: 9:26 PM
 */
use \backend\components\Factory\GoogleClientFactory;
use \common\components\BAException;
use \common\components\GoogleDriveHelper;

use Mockery as m;

class GetInstance_Test extends \Codeception\TestCase\Test
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testThrowNoTokenException()
    {

        $client = GoogleClientFactory::create();
        $this->tester->expectException(
            new BAException(GoogleDriveHelper::CLIENT_NOT_HAVE_TOKEN_MESSAGE, BAException::INVALID_PARAM_EXCODE),
            function() use ($client){
                GoogleDriveHelper::getInstance($client);
            }
        );

    }
}