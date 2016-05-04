<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\assets\common;

/**
 * Hammer.js jquery plugin asset bundle for the yiingine.
 */
class JQueryHammerJsAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@bower/jquery-hammerjs';
    
    /** @inheritdoc */
    public $js = ['jquery.hammer.js'];
    
    /** @inheritdoc */
    public $depends =  [
        '\yiingine\assets\common\HammerJsAsset',
        '\yii\web\JqueryAsset'
    ];
}

/**
 * Hammer.js asset bundle for the yiingine.
 */
class HammerJsAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@bower/hammerjs';
    
    /** @inheritdoc */
    public $js = ['hammer.min.js'];
}

