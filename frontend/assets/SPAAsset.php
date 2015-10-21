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
    ];
    public $js = [
        'js/libs/underscore.js',
        'js/libs/backbone.js',
        'js/models/MainPanel.js',
        'js/models/TaskPanel.js',
        'js/models/Task.js',
        'js/view/MainPanelView.js',
        'js/view/TaskPanelView.js',
        'js/view/TaskTileView.js',
        'js/collections/Tasks.js',
        'js/app.js'
    ];
    public $depends = [
    ];
}
