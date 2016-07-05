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
class GoogleAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        "css/bootstrap-datetimepicker.min.css"
    ];
    public $js = [
        'https://maps.googleapis.com/maps/api/js?key=AIzaSyALoOSAt19qKApEaQxnDEuHxsn9f7Kn46E&libraries=places',
        'https://apis.google.com/js/platform.js?onload=start',
        'js/google_signin.js',
    ];
    public $depends = [
    ];

    public $jsOptions = [
        'async' => 'async',
        'defer' => 'defer'
    ];
}
