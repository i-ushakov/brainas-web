<?php
/**
 * Created by Kit on 10/19/2015
 */

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SPAAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        "css/bootstrap-datetimepicker.min.css",
        "css/bootstrap-dialog.css"
    ];
    public $js = [
        "js/libs/moment.min.js",
        "js/libs/bootstrap-datetimepicker.min.js",
        "js/libs/bootstrap-dialog.js",
        'js/libs/underscore.js',
        'js/libs/backbone.js',
        'js/settings.js',
        'js/Utils.js',
        'js/models/panels/MainPanel.js',
        'js/models/panels/TaskPanel.js',
        'js/models/panels/TasksControlBoard.js',
        'js/models/task/Condition.js',
        'js/collections/Conditions.js',
        'js/models/task/Task.js',
        'js/helpers/PictureHelper.js',
        'js/view/task/ConditionTypeSelectorView.js',
        'js/view/panels/MainPanelView.js',
        'js/view/panels/TaskControlBoardView.js',
        'js/view/panels/TaskPanelView.js',

        // Task card
        'js/view/task/TaskStatusView.js',
        'js/view/task/TaskTileView.js',
        'js/view/task/TaskLocationConditionView.js',
        'js/view/task/TaskTimeConditionView.js',
        'js/view/task/TaskMessageView.js',
        'js/view/task/PictureView.js',
        'js/view/task/PictureUploaderView.js',
        'js/view/task/TaskDescriptionView.js',
        'js/view/task/ConditionsPanelView.js',
        'js/view/task/TaskCardView.js',
        'js/collections/Tasks.js',
        'js/app.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}
