<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\assets\common;

/**
 * Common asset bundle for the yiingine.
 */
class CommonAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@yiingine/assets/common';
    
    /** @inheritdoc */
    public $css = [
        //'css/layouts/screen.css'
    ];
    
    /** @inheritdoc */
    public $js = ['js/basic.js'];
    
    /** @inheritdoc */
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
        'rmrevin\yii\fontawesome\AssetBundle'
    ];
}
