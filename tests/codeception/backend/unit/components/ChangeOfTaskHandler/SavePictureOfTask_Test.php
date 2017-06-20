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

use Mockery as m;

class SavePictureOfTask_Test extends \Codeception\TestCase\Test
{
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
        $userId = 1;

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
        $taskId = 777;


        $changeOfTaskHandler->savePistureOfTask($pictureForSave, $taskId);

        $this->tester->seeRecord(PictureOfTask::class, [
            'task_id' => 777,
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
        $userId = 1;

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
        $taskId = 777;


        $changeOfTaskHandler->savePistureOfTask($pictureForSave, $taskId);

        $this->tester->seeRecord(PictureOfTask::class, [
            'task_id' => 777,
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
        $userId = 1;

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
        $existsPicture->task_id = 777;
        $existsPicture->name = 'picture_name_old.png';
        $existsPicture->file_id = 'picture_fileId_old';

        $pictureForSave = new PictureOfTask();
        $pictureForSave->name = "picture_name_new.png";
        $pictureForSave->file_id = "picture_fileId_new";
        $taskId = 777;

        $changeOfTaskHandler->savePistureOfTask($pictureForSave, $taskId);

        $this->tester->seeRecord(PictureOfTask::class, [
            'task_id' => 777,
            'name' => 'picture_name_new.png',
            'file_id' => 'picture_fileId_new'
        ]);
    }
}