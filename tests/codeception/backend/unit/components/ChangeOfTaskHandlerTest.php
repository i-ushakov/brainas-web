<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 12/22/2016
 * Time: 11:00 AM
 */

use Codeception\Util\Stub;
use backend\components\ChangeOfTaskParser;
use backend\components\ChangeOfTaskHandler;

use Mockery as m;

class ChangeOfTaskHandlerTest extends \Codeception\TestCase\Test {
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testSavePistureOfTask() {
        // TODO
        $picture = Stub::construct(
            '\common\models\PictureOfTask',
            array(),
            array(
                'task_id' => 779,
                'name' => 'task_img_1234567890.jpg',
                'drive_id' => 'DriveId:CAESABi0DSD-iK_fmFIoAA==',
                'file_id' => '0B-nWSp4lPq2nb3NjbnIyaTZYSWc'
            ),$this
        );
        //$this->tester->seeRecord('common\models\PictureOfTask', array('task_id' => 777, 'name' => 'task_img_1234567890.jpg'));
    }

    public function testCleanDeletedCondition() {
        // TODO
    }

}