<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/29/2017
 * Time: 2:58 PM
 */

use backend\components\ChangeOfTaskParser;
use backend\components\ChangeOfTaskHandler;
use common\components\TaskXMLConverter;
use \common\models\PictureOfTask;
use \common\components\GoogleDriveHelper;
use \common\components\BAException;

use Mockery as m;

class SavePictureOfTask_Test extends \Codeception\TestCase\Test
{
    const TEST_VALUE_OF_USER_ID = 1;
    const TEST_VALUE_OF_TASK_ID = 777;

    /**
     * @var UnitTester
     */
    protected $tester;

    protected function tearDown()
    {
        m::close();
    }

    public function testSaveNewPictureWithoutFileId()
    {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);
        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $googleDriveHelper = m::mock(GoogleDriveHelper::class);
        $googleDriveHelper->shouldReceive('getFileIdByName')
            ->once()
            ->with('picture_name.png')
            ->andReturn('picture_fileId');
        $userId = self::TEST_VALUE_OF_USER_ID;

        /** @var ChangeOfTaskHandler $changeOfTaskHandler  */
        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[]',
            [
                $changeOfTaskParser,
                $taskXMLConverter,
                $userId,
                $googleDriveHelper
            ]
        );

        $pictureForSave = new PictureOfTask();
        $pictureForSave->name = "picture_name.png";
        //$pictureForSave->file_id = "picture_fileId";
        $taskId = self::TEST_VALUE_OF_TASK_ID;


        $changeOfTaskHandler->savePistureOfTask($pictureForSave, $taskId);

        $this->tester->seeRecord(PictureOfTask::class, [
            'task_id' => self::TEST_VALUE_OF_TASK_ID,
            'name' => 'picture_name.png',
            'file_id' => 'picture_fileId'
        ]);
    }

    public function testSaveNewPictureWithFileId()
    {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);
        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $googleDriveHelper = m::mock(GoogleDriveHelper::class);
        $googleDriveHelper->shouldReceive('getFileIdByName')
            ->never();
        $userId = self::TEST_VALUE_OF_USER_ID;

        /** @var ChangeOfTaskHandler $changeOfTaskHandler  */
        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[]',
            [
                $changeOfTaskParser,
                $taskXMLConverter,
                $userId,
                $googleDriveHelper
            ]
        );

        $pictureForSave = new PictureOfTask();
        $pictureForSave->name = "picture_name.png";
        $pictureForSave->file_id = "picture_fileId";
        $taskId = self::TEST_VALUE_OF_TASK_ID;


        $changeOfTaskHandler->savePistureOfTask($pictureForSave, $taskId);

        $this->tester->seeRecord(PictureOfTask::class, [
            'task_id' => self::TEST_VALUE_OF_TASK_ID,
            'name' => 'picture_name.png',
            'file_id' => 'picture_fileId'
        ]);
    }

    public function testSaveUpdatedPicture()
    {
        $taskXMLConverter = m::mock(TaskXMLConverter::class);
        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $googleDriveHelper = m::mock(GoogleDriveHelper::class);
        $googleDriveHelper->shouldReceive('getFileIdByName')
            ->never();
        $userId = self::TEST_VALUE_OF_USER_ID;

        /** @var ChangeOfTaskHandler $changeOfTaskHandler  */
        $changeOfTaskHandler = \Mockery::mock(
            ChangeOfTaskHandler::class . '[]',
            [
                $changeOfTaskParser,
                $taskXMLConverter,
                $userId,
                $googleDriveHelper
            ]
        );
        $existsPicture = new PictureOfTask();
        $existsPicture->task_id = self::TEST_VALUE_OF_TASK_ID;
        $existsPicture->name = 'picture_name_old.png';
        $existsPicture->file_id = 'picture_fileId_old';

        $pictureForSave = new PictureOfTask();
        $pictureForSave->name = "picture_name_new.png";
        $pictureForSave->file_id = "picture_fileId_new";
        $taskId = self::TEST_VALUE_OF_TASK_ID;

        $changeOfTaskHandler->savePistureOfTask($pictureForSave, $taskId);

        $this->tester->seeRecord(PictureOfTask::class, [
            'task_id' => self::TEST_VALUE_OF_TASK_ID,
            'name' => 'picture_name_new.png',
            'file_id' => 'picture_fileId_new'
        ]);
    }

    public function testThrowNoGoogleDriveHelperException()
    {
        $changeOfTaskParser = m::mock(ChangeOfTaskParser::class);
        $taskXMLConverter = m::mock(TaskXMLConverter::class);
        $userId = self::TEST_VALUE_OF_USER_ID;
        $handler = new ChangeOfTaskHandler($changeOfTaskParser, $taskXMLConverter, $userId, null);

        $picture = new PictureOfTask();
        $picture->file_id = null;
        $picture->name = 'picture_name';
        $taskId = self::TEST_VALUE_OF_TASK_ID;

        $this->tester->expectException(
            new BAException(ChangeOfTaskHandler::GOOGLE_DRIVE_HELPER_NOT_SET, BAException::PARAM_NOT_SET_EXCODE),
            function() use ($handler, $picture, $taskId) {
                $handler->savePistureOfTask($picture, $taskId);
            }
        );
    }
}