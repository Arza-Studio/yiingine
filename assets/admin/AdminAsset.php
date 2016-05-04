<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\assets\admin;

/**
 * Admin asset bundle for the yiingine.
 */
class AdminAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@yiingine/assets/admin';
    
    /** @inheritdoc */
    public $css = [
        'css/main.css',
        'css/gridView.css'
    ];
    
    /** @inheritdoc */
    public $js = [
        'js/main.js',
    ];
    
    public $depends = [
        'yiingine\assets\common\CommonAsset',
        //'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        //'yii\bootstrap\BootstrapThemeAsset',
        'rmrevin\yii\fontawesome\AssetBundle',
    ];
}
